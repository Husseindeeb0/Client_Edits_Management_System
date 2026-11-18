<?php

namespace Tests\Feature;

use App\Features\Attachments\Models\Attachment;
use App\Features\Attachments\Requests\StoreAttachmentRequest;
use App\Features\Authentication\Models\User;
use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Edits\Models\Edit;
use App\Features\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttachmentsTest extends TestCase
{
    use RefreshDatabase;

    protected Edit $edit;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Sanctum::actingAs(User::factory()->create());

        $this->edit = Edit::factory()->done()->create();
    }

    /**
     * Upload the given files to the test edit and return the response.
     *
     * @param array<int, UploadedFile> $files
     */
    private function upload(array $files, array $extra = []): \Illuminate\Testing\TestResponse
    {
        return $this->post(
            "/api/edits/{$this->edit->id}/attachments",
            array_merge(['files' => $files], $extra),
            ['Accept' => 'application/json'],
        );
    }

    private function pdf(string $name = 'report.pdf', int $kilobytes = 100): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, 'application/pdf');
    }

    // -------------------------------------------------------------------------
    // Upload
    // -------------------------------------------------------------------------

    public function test_can_upload_single_attachment(): void
    {
        $response = $this->upload([$this->pdf()], ['description' => 'Signed brief']);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Attachments uploaded successfully.',
                'data' => [[
                    'edit_id' => $this->edit->id,
                    'original_file_name' => 'report.pdf',
                    'description' => 'Signed brief',
                ]],
            ])
            ->assertJsonMissingPath('data.0.file_path');

        $attachment = Attachment::sole();
        Storage::disk('local')->assertExists($attachment->file_path);
        $this->assertStringStartsWith('attachments/', $attachment->file_path);
    }

    public function test_can_upload_multiple_attachments(): void
    {
        $response = $this->upload([
            $this->pdf('a.pdf'),
            UploadedFile::fake()->create('photo.jpg', 50, 'image/jpeg'),
            UploadedFile::fake()->create('sheet.csv', 5, 'text/csv'),
        ]);

        $response->assertCreated()->assertJsonCount(3, 'data');
        $this->assertSame(3, $this->edit->attachments()->count());
    }

    public function test_upload_requires_at_least_one_file(): void
    {
        $this->upload([])->assertUnprocessable()->assertJsonValidationErrors(['files']);
    }

    public function test_upload_rejects_too_many_files(): void
    {
        $files = [];
        for ($i = 0; $i <= StoreAttachmentRequest::MAX_FILES; $i++) {
            $files[] = $this->pdf("file{$i}.pdf", 1);
        }

        $this->upload($files)->assertUnprocessable()->assertJsonValidationErrors(['files']);
        $this->assertSame(0, Attachment::count());
    }

    public function test_upload_rejects_files_over_size_limit(): void
    {
        $tooBig = $this->pdf('huge.pdf', StoreAttachmentRequest::MAX_FILE_SIZE_KB + 1);

        $this->upload([$tooBig])->assertUnprocessable()->assertJsonValidationErrors(['files.0']);
    }

    public function test_upload_rejects_disallowed_file_types(): void
    {
        $exe = UploadedFile::fake()->create('setup.exe', 10, 'application/x-msdownload');

        $this->upload([$exe])->assertUnprocessable()->assertJsonValidationErrors(['files.0']);
    }

    public function test_upload_rejects_svg_files(): void
    {
        $svg = UploadedFile::fake()->create('logo.svg', 1, 'image/svg+xml');

        $this->upload([$svg])->assertUnprocessable()->assertJsonValidationErrors(['files.0']);
    }

    public function test_upload_checks_file_content_not_only_extension(): void
    {
        // Named .pdf, but its detected content type is an executable
        $disguised = UploadedFile::fake()->create('invoice.pdf', 10, 'application/x-msdownload');

        $this->upload([$disguised])->assertUnprocessable()->assertJsonValidationErrors(['files.0']);
        $this->assertSame(0, Attachment::count());
    }

    public function test_upload_returns_404_for_missing_edit(): void
    {
        $this->post('/api/edits/999999/attachments', ['files' => [$this->pdf()]], ['Accept' => 'application/json'])
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function test_can_list_attachments_of_an_edit_newest_first(): void
    {
        $older = Attachment::factory()->for($this->edit)->create();
        $newer = Attachment::factory()->for($this->edit)->create();
        Attachment::factory()->create(); // belongs to another edit

        $this->getJson("/api/edits/{$this->edit->id}/attachments")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id);
    }

    public function test_can_list_all_attachments_paginated_with_edit_title_newest_first(): void
    {
        $older = Attachment::factory()->for($this->edit)->create();
        $newer = Attachment::factory()->create(); // another edit

        $this->getJson('/api/attachments')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'edit_id', 'edit_title', 'original_file_name', 'description', 'created_at']],
                'links',
                'meta',
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.0.edit_title', $newer->edit->title)
            ->assertJsonPath('data.1.id', $older->id)
            ->assertJsonPath('data.1.edit_title', $this->edit->title);
    }

    public function test_can_search_all_attachments_by_file_name(): void
    {
        $match = Attachment::factory()->for($this->edit)->create(['original_file_name' => 'signed-contract.pdf']);
        Attachment::factory()->for($this->edit)->create(['original_file_name' => 'logo.png']);

        $this->getJson('/api/attachments?search=contract')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    }

    public function test_listing_all_attachments_runs_a_constant_number_of_queries(): void
    {
        Attachment::factory()->create();
        $queriesForOne = $this->countQueries('/api/attachments');

        Attachment::factory()->count(9)->create(); // each on its own edit
        $queriesForTen = $this->countQueries('/api/attachments');

        $this->assertSame($queriesForOne, $queriesForTen, 'Listing attachments must not run one query per attachment (N+1).');
    }

    private function countQueries(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson($url)->assertOk();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    // -------------------------------------------------------------------------
    // Download
    // -------------------------------------------------------------------------

    public function test_can_download_attachment_with_original_file_name(): void
    {
        $this->upload([$this->pdf('Final Design.pdf')])->assertCreated();
        $attachment = Attachment::sole();

        $this->get("/api/attachments/{$attachment->id}/download")
            ->assertOk()
            ->assertDownload('Final Design.pdf');
    }

    public function test_download_returns_404_when_file_is_missing_from_storage(): void
    {
        $attachment = Attachment::factory()->for($this->edit)->create();

        $this->getJson("/api/attachments/{$attachment->id}/download")->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_can_delete_attachment_and_its_file(): void
    {
        $this->upload([$this->pdf()])->assertCreated();
        $attachment = Attachment::sole();

        $this->deleteJson("/api/attachments/{$attachment->id}")
            ->assertOk()
            ->assertJson(['message' => 'Attachment deleted successfully.']);

        $this->assertModelMissing($attachment);
        Storage::disk('local')->assertMissing($attachment->file_path);
    }

    public function test_deleting_attachment_succeeds_when_file_already_missing(): void
    {
        $attachment = Attachment::factory()->for($this->edit)->create();

        $this->deleteJson("/api/attachments/{$attachment->id}")->assertOk();
        $this->assertModelMissing($attachment);
    }

    public function test_deleting_edit_removes_attachment_files(): void
    {
        $this->upload([$this->pdf('a.pdf'), $this->pdf('b.pdf')])->assertCreated();
        $paths = Attachment::pluck('file_path')->all();

        $this->deleteJson("/api/edits/{$this->edit->id}")->assertOk();

        $this->assertSame(0, Attachment::count());
        foreach ($paths as $path) {
            Storage::disk('local')->assertMissing($path);
        }
    }

    public function test_deleting_client_removes_all_descendant_attachment_files(): void
    {
        $client = Client::factory()->create();
        $company = Company::factory()->for($client)->create();
        $project = Project::factory()->for($company)->create();
        $this->edit = Edit::factory()->for($project)->done()->create();

        $this->upload([$this->pdf()])->assertCreated();
        $path = Attachment::sole()->file_path;

        $this->deleteJson("/api/clients/{$client->id}")->assertOk();

        Storage::disk('local')->assertMissing($path);
    }

    public function test_deleting_company_removes_all_descendant_attachment_files(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->for($company)->create();
        $this->edit = Edit::factory()->for($project)->done()->create();

        $this->upload([$this->pdf()])->assertCreated();
        $path = Attachment::sole()->file_path;

        $this->deleteJson("/api/companies/{$company->id}")->assertOk();

        Storage::disk('local')->assertMissing($path);
    }

    public function test_blocked_client_delete_keeps_attachment_files(): void
    {
        $client = Client::factory()->create();
        $company = Company::factory()->for($client)->create();
        $project = Project::factory()->for($company)->create();
        $this->edit = Edit::factory()->for($project)->active()->create();

        $this->upload([$this->pdf()])->assertCreated();
        $path = Attachment::sole()->file_path;

        $this->deleteJson("/api/clients/{$client->id}")->assertUnprocessable();

        Storage::disk('local')->assertExists($path);
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $attachment = Attachment::factory()->for($this->edit)->create();
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/attachments')->assertUnauthorized();
        $this->getJson("/api/edits/{$this->edit->id}/attachments")->assertUnauthorized();
        $this->postJson("/api/edits/{$this->edit->id}/attachments")->assertUnauthorized();
        $this->getJson("/api/attachments/{$attachment->id}/download")->assertUnauthorized();
        $this->deleteJson("/api/attachments/{$attachment->id}")->assertUnauthorized();
    }
}
