<?php

namespace Tests\Feature;

use App\Features\Authentication\Models\User;
use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Edits\Models\Edit;
use App\Features\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EditsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        $client  = Client::create(['name' => 'Test Client', 'phone_number' => '+1234567890']);
        $company = Company::create(['name' => 'Test Company', 'client_id' => $client->id]);
        $this->project = Project::create(['name' => 'Test Project', 'company_id' => $company->id]);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_can_create_edit_with_required_fields_only(): void
    {
        $response = $this->postJson('/api/edits', [
            'project_id' => $this->project->id,
            'title'      => 'Fix homepage banner',
            'priority'   => 'Medium',
            'status'     => 'Pending',
        ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'project_id' => $this->project->id,
                    'title'      => 'Fix homepage banner',
                    'priority'   => 'Medium',
                    'status'     => 'Pending',
                    'is_paid'    => false,
                ],
            ]);

        $this->assertDatabaseHas('edits', [
            'title'      => 'Fix homepage banner',
            'project_id' => $this->project->id,
        ]);
    }

    public function test_can_create_edit_with_all_fields(): void
    {
        $response = $this->postJson('/api/edits', [
            'project_id'  => $this->project->id,
            'title'       => 'Full edit',
            'description' => 'Detailed description here',
            'priority'    => 'High',
            'status'      => 'In Progress',
            'price'       => 150.50,
            'is_paid'     => true,
            'due_date'    => '2025-12-31',
        ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'title'    => 'Full edit',
                    'priority' => 'High',
                    'status'   => 'In Progress',
                    'price'    => 150.50,
                    'is_paid'  => true,
                    'due_date' => '2025-12-31',
                ],
            ]);
    }

    public function test_create_edit_fails_with_invalid_project_id(): void
    {
        $response = $this->postJson('/api/edits', [
            'project_id' => 999999,
            'title'      => 'Some Edit',
            'priority'   => 'Low',
            'status'     => 'Pending',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_create_edit_fails_with_invalid_priority(): void
    {
        $response = $this->postJson('/api/edits', [
            'project_id' => $this->project->id,
            'title'      => 'Edit with bad priority',
            'priority'   => 'Urgent',  // not in allowed list
            'status'     => 'Pending',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_create_edit_fails_with_invalid_status(): void
    {
        $response = $this->postJson('/api/edits', [
            'project_id' => $this->project->id,
            'title'      => 'Edit with bad status',
            'priority'   => 'Low',
            'status'     => 'Completed',  // not in allowed list
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_create_edit_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/edits', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id', 'title', 'priority', 'status']);
    }

    // -------------------------------------------------------------------------
    // List (paginated)
    // -------------------------------------------------------------------------

    public function test_can_retrieve_paginated_edits(): void
    {
        Edit::create(['project_id' => $this->project->id, 'title' => 'Edit A', 'priority' => 'High', 'status' => 'Pending']);
        Edit::create(['project_id' => $this->project->id, 'title' => 'Edit B', 'priority' => 'Low', 'status' => 'Done']);

        $response = $this->getJson('/api/edits');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'project_id', 'project_name', 'title', 'priority', 'status', 'price', 'is_paid', 'due_date'],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_edit_list_includes_project_name(): void
    {
        Edit::create(['project_id' => $this->project->id, 'title' => 'Named Edit', 'priority' => 'Medium', 'status' => 'Pending']);

        $response = $this->getJson('/api/edits');

        $response->assertOk()
            ->assertJsonFragment(['project_name' => 'Test Project']);
    }

    public function test_can_filter_edits_by_status(): void
    {
        Edit::create(['project_id' => $this->project->id, 'title' => 'Pending Edit', 'priority' => 'Low', 'status' => 'Pending']);
        Edit::create(['project_id' => $this->project->id, 'title' => 'Done Edit',    'priority' => 'Low', 'status' => 'Done']);

        $response = $this->getJson('/api/edits?status=Pending');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['status' => 'Pending']);
    }

    public function test_can_filter_edits_by_priority(): void
    {
        Edit::create(['project_id' => $this->project->id, 'title' => 'High Priority', 'priority' => 'High', 'status' => 'Pending']);
        Edit::create(['project_id' => $this->project->id, 'title' => 'Low Priority',  'priority' => 'Low',  'status' => 'Pending']);

        $response = $this->getJson('/api/edits?priority=High');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['priority' => 'High']);
    }

    public function test_can_filter_edits_by_is_paid(): void
    {
        Edit::create(['project_id' => $this->project->id, 'title' => 'Paid Edit',   'priority' => 'Low', 'status' => 'Done', 'is_paid' => true]);
        Edit::create(['project_id' => $this->project->id, 'title' => 'Unpaid Edit', 'priority' => 'Low', 'status' => 'Done', 'is_paid' => false]);

        $response = $this->getJson('/api/edits?is_paid=true');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['is_paid' => true]);
    }

    public function test_can_filter_edits_by_project_id(): void
    {
        $client2  = Client::create(['name' => 'Other Client', 'phone_number' => '999']);
        $company2 = Company::create(['name' => 'Other Company', 'client_id' => $client2->id]);
        $project2 = Project::create(['name' => 'Other Project', 'company_id' => $company2->id]);

        Edit::create(['project_id' => $this->project->id, 'title' => 'Edit for P1', 'priority' => 'Low', 'status' => 'Pending']);
        Edit::create(['project_id' => $project2->id,     'title' => 'Edit for P2', 'priority' => 'Low', 'status' => 'Pending']);

        $response = $this->getJson("/api/edits?project_id={$this->project->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['project_id' => $this->project->id]);
    }

    public function test_can_search_edits_by_title(): void
    {
        Edit::create(['project_id' => $this->project->id, 'title' => 'Banner redesign', 'priority' => 'Low', 'status' => 'Pending']);
        Edit::create(['project_id' => $this->project->id, 'title' => 'Footer fix',      'priority' => 'Low', 'status' => 'Pending']);

        $response = $this->getJson('/api/edits?search=Banner');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Banner redesign']);
    }

    // -------------------------------------------------------------------------
    // Details
    // -------------------------------------------------------------------------

    public function test_can_get_edit_details_with_project_and_attachments(): void
    {
        $edit = Edit::create([
            'project_id'  => $this->project->id,
            'title'       => 'Detail Edit',
            'description' => 'Some description',
            'priority'    => 'High',
            'status'      => 'Pending',
        ]);

        $response = $this->getJson("/api/edits/{$edit->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'project_id', 'project', 'title', 'description', 'priority', 'status', 'price', 'is_paid', 'due_date', 'attachments'],
            ])
            ->assertJson([
                'data' => [
                    'id'          => $edit->id,
                    'project'     => ['id' => $this->project->id, 'name' => 'Test Project'],
                    'attachments' => [],
                ],
            ]);
    }

    public function test_get_edit_details_returns_404_for_missing_edit(): void
    {
        $this->getJson('/api/edits/999999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_can_partially_update_edit(): void
    {
        $edit = Edit::create([
            'project_id' => $this->project->id,
            'title'      => 'Original Title',
            'priority'   => 'Low',
            'status'     => 'Pending',
        ]);

        $response = $this->patchJson("/api/edits/{$edit->id}", [
            'title'  => 'Updated Title',
            'status' => 'In Progress',
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'title'    => 'Updated Title',
                    'status'   => 'In Progress',
                    'priority' => 'Low',  // unchanged
                ],
            ]);

        $edit->refresh();
        $this->assertEquals('Updated Title', $edit->title);
        $this->assertEquals('Low', $edit->priority);  // unchanged
    }

    public function test_update_edit_rejects_empty_payload(): void
    {
        $edit = Edit::create([
            'project_id' => $this->project->id,
            'title'      => 'Some Edit',
            'priority'   => 'Low',
            'status'     => 'Pending',
        ]);

        $this->patchJson("/api/edits/{$edit->id}", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['payload']);
    }

    public function test_update_edit_fails_with_invalid_status(): void
    {
        $edit = Edit::create([
            'project_id' => $this->project->id,
            'title'      => 'Edit',
            'priority'   => 'Low',
            'status'     => 'Pending',
        ]);

        $this->patchJson("/api/edits/{$edit->id}", ['status' => 'InvalidStatus'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_can_delete_edit(): void
    {
        $edit = Edit::create([
            'project_id' => $this->project->id,
            'title'      => 'To Be Deleted',
            'priority'   => 'Low',
            'status'     => 'Done',
        ]);

        $this->deleteJson("/api/edits/{$edit->id}")
            ->assertOk()
            ->assertJson(['message' => 'Edit deleted successfully.']);

        $this->assertDatabaseMissing('edits', ['id' => $edit->id]);
    }

    public function test_delete_edit_cascades_to_attachments(): void
    {
        $edit = Edit::create([
            'project_id' => $this->project->id,
            'title'      => 'Edit With Attachment',
            'priority'   => 'Low',
            'status'     => 'Done',
        ]);

        // Create a fake attachment record (no actual file needed for DB cascade test)
        $edit->attachments()->create([
            'file_path'          => 'attachments/2025/01/fake-file.pdf',
            'original_file_name' => 'fake-file.pdf',
        ]);

        $this->assertDatabaseHas('attachments', ['edit_id' => $edit->id]);

        $this->deleteJson("/api/edits/{$edit->id}")->assertOk();

        $this->assertDatabaseMissing('edits', ['id' => $edit->id]);
        $this->assertDatabaseMissing('attachments', ['edit_id' => $edit->id]);
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->app['auth']->forgetGuards();

        $this->withServerVariables(['HTTP_AUTHORIZATION' => ''])
            ->getJson('/api/edits')
            ->assertUnauthorized();
    }
}

