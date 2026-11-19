<?php

namespace Tests\Feature;

use App\Features\Attachments\Models\Attachment;
use App\Features\Authentication\Models\User;
use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Edits\Models\Edit;
use App\Features\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());

        $client = Client::factory()->create(['name' => 'Acme Owner']);
        $this->company = Company::factory()->for($client)->create(['name' => 'Acme Corp']);
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function test_can_list_projects_with_company_and_client_names(): void
    {
        $project = Project::factory()->for($this->company)->create(['name' => 'Website']);

        $this->getJson('/api/projects')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertExactJson([
                'data' => [[
                    'id' => $project->project_id,
                    'name' => 'Website',
                    'company_id' => $this->company->company_id,
                    'company_name' => 'Acme Corp',
                    'client_id' => $this->company->client_id,
                    'client_name' => 'Acme Owner',
                ]],
                'links' => $this->getJson('/api/projects')->json('links'),
                'meta' => $this->getJson('/api/projects')->json('meta'),
            ]);
    }

    public function test_can_search_projects_by_project_company_or_client_name(): void
    {
        Project::factory()->for($this->company)->create(['name' => 'Mobile App']);
        $other = Company::factory()->create(['name' => 'Globex']);
        Project::factory()->for($other)->create(['name' => 'Landing Page']);

        $this->getJson('/api/projects?search=Mobile')->assertJsonCount(1, 'data');
        $this->getJson('/api/projects?search=Acme Corp')->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mobile App');
        $this->getJson('/api/projects?search=Acme Owner')->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mobile App');
        $this->getJson('/api/projects?search=Globex')->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Landing Page');
    }

    public function test_listing_projects_runs_a_constant_number_of_queries(): void
    {
        Project::factory()->create();
        $queriesForOne = $this->countQueries('/api/projects');

        Project::factory()->count(9)->create();
        $queriesForTen = $this->countQueries('/api/projects');

        $this->assertSame($queriesForOne, $queriesForTen, 'Listing projects must not run one query per project (N+1).');
    }

    public function test_client_and_company_project_lists_can_be_searched_by_project_name(): void
    {
        Project::factory()->for($this->company)->create(['name' => 'Mobile App']);
        Project::factory()->for($this->company)->create(['name' => 'Website']);

        $this->getJson("/api/projects/company/{$this->company->company_id}?search=Mobile")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mobile App');

        $this->getJson("/api/projects/client/{$this->company->client_id}?search=Web")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Website');
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_can_create_project(): void
    {
        $this->postJson('/api/projects', [
            'name' => 'New Website',
            'company_id' => $this->company->company_id,
        ])
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => 'New Website',
                    'company_id' => $this->company->company_id,
                ],
            ]);

        $this->assertDatabaseHas('projects', ['name' => 'New Website', 'company_id' => $this->company->company_id]);
    }

    public function test_create_project_validates_input(): void
    {
        $this->postJson('/api/projects', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'company_id']);

        $this->postJson('/api/projects', ['name' => 'X', 'company_id' => 999999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['company_id']);

        $this->postJson('/api/projects', ['name' => str_repeat('a', 256), 'company_id' => $this->company->company_id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // -------------------------------------------------------------------------
    // Details
    // -------------------------------------------------------------------------

    public function test_can_get_project_details_with_company_and_client(): void
    {
        $project = Project::factory()->for($this->company)->create(['name' => 'Website']);

        $this->getJson("/api/projects/{$project->project_id}")
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $project->project_id,
                    'name' => 'Website',
                    'company_id' => $this->company->company_id,
                    'company' => ['id' => $this->company->company_id, 'name' => 'Acme Corp'],
                    'client' => ['id' => $this->company->client_id, 'name' => 'Acme Owner'],
                ],
            ]);
    }

    public function test_project_details_returns_404_for_missing_project(): void
    {
        $this->getJson('/api/projects/999999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_can_rename_project(): void
    {
        $project = Project::factory()->for($this->company)->create(['name' => 'Old']);

        $this->patchJson("/api/projects/{$project->project_id}", ['name' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New')
            ->assertJsonPath('data.company_id', $this->company->company_id);
    }

    public function test_can_move_project_to_another_company(): void
    {
        $project = Project::factory()->for($this->company)->create();
        $otherCompany = Company::factory()->create();

        $this->patchJson("/api/projects/{$project->project_id}", ['company_id' => $otherCompany->company_id])
            ->assertOk()
            ->assertJsonPath('data.company_id', $otherCompany->company_id);
    }

    public function test_update_project_validates_input(): void
    {
        $project = Project::factory()->for($this->company)->create();

        $this->patchJson("/api/projects/{$project->project_id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['payload']);

        $this->patchJson("/api/projects/{$project->project_id}", ['company_id' => 999999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['company_id']);

        $this->patchJson("/api/projects/{$project->project_id}", ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_cannot_delete_project_with_active_edits(): void
    {
        $project = Project::factory()->for($this->company)->create();
        Edit::factory()->for($project)->active()->create();

        $this->deleteJson("/api/projects/{$project->project_id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['project']);

        $this->assertModelExists($project);
    }

    public function test_can_delete_project_with_only_inactive_edits_and_files_are_removed(): void
    {
        Storage::fake('local');

        $project = Project::factory()->for($this->company)->create();
        $edit = Edit::factory()->for($project)->done()->create();
        $attachment = Attachment::factory()->for($edit)->create();
        Storage::disk('local')->put($attachment->file_path, 'content');

        $this->deleteJson("/api/projects/{$project->project_id}")
            ->assertOk()
            ->assertJson(['message' => 'Project deleted successfully.']);

        $this->assertModelMissing($project);
        $this->assertModelMissing($edit);
        $this->assertModelMissing($attachment);
        Storage::disk('local')->assertMissing($attachment->file_path);
    }

    public function test_can_delete_project_without_edits(): void
    {
        $project = Project::factory()->for($this->company)->create();

        $this->deleteJson("/api/projects/{$project->project_id}")->assertOk();

        $this->assertModelMissing($project);
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $project = Project::factory()->for($this->company)->create();
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/projects')->assertUnauthorized();
        $this->postJson('/api/projects', [])->assertUnauthorized();
        $this->getJson("/api/projects/{$project->project_id}")->assertUnauthorized();
        $this->patchJson("/api/projects/{$project->project_id}", [])->assertUnauthorized();
        $this->deleteJson("/api/projects/{$project->project_id}")->assertUnauthorized();
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
}
