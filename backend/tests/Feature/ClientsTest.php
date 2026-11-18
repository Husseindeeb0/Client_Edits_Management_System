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

class ClientsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_create_client_with_required_fields(): void
    {
        $response = $this->postJson('/api/clients', [
            'name' => 'John Doe',
            'phone_number' => '+96112345678',
        ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => 'John Doe',
                    'phone_number' => '+96112345678',
                    'email' => null,
                    'last_follow_up_date' => null,
                ],
            ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'John Doe',
            'phone_number' => '+96112345678',
        ]);
    }

    public function test_can_create_client_with_all_fields(): void
    {
        $response = $this->postJson('/api/clients', [
            'name' => 'Jane Smith',
            'phone_number' => '+1234567890',
            'email' => 'jane@example.com',
            'last_follow_up_date' => '2026-11-12',
        ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => 'Jane Smith',
                    'phone_number' => '+1234567890',
                    'email' => 'jane@example.com',
                    'last_follow_up_date' => '2026-11-12',
                ],
            ]);
    }

    public function test_create_client_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->postJson('/api/clients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone_number']);
    }

    public function test_can_retrieve_paginated_clients(): void
    {
        Client::create(['name' => 'Client 1', 'phone_number' => '111']);
        Client::create(['name' => 'Client 2', 'phone_number' => '222']);

        $response = $this->getJson('/api/clients');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'phone_number', 'email', 'last_follow_up_date'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_search_clients_by_name_phone_or_email(): void
    {
        Client::create(['name' => 'Alice Johnson', 'phone_number' => '12345', 'email' => 'alice@test.com']);
        Client::create(['name' => 'Bob Smith', 'phone_number' => '98765', 'email' => 'bob@test.com']);

        $responseByName = $this->getJson('/api/clients?search=Alice');
        $responseByName->assertOk()->assertJsonCount(1, 'data');

        $responseByPhone = $this->getJson('/api/clients?search=98765');
        $responseByPhone->assertOk()->assertJsonCount(1, 'data');

        $responseByEmail = $this->getJson('/api/clients?search=alice@test.com');
        $responseByEmail->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_client_details_do_not_embed_related_lists(): void
    {
        $client = Client::create(['name' => 'Acme Corp Client', 'phone_number' => '555']);
        Company::create(['client_id' => $client->id, 'name' => 'Company A']);

        $response = $this->getJson("/api/clients/{$client->id}");

        // Companies, projects and edits are loaded by the details page tabs
        $response->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $client->id,
                    'name' => 'Acme Corp Client',
                    'phone_number' => '555',
                    'email' => null,
                    'last_follow_up_date' => null,
                ],
            ]);
    }

    public function test_partial_update_preserves_omitted_fields(): void
    {
        $client = Client::create([
            'name' => 'Original Name',
            'phone_number' => '123456',
            'email' => 'original@example.com',
            'last_follow_up_date' => '2026-01-01',
        ]);

        $response = $this->patchJson("/api/clients/{$client->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                    'phone_number' => '123456',
                    'email' => 'original@example.com',
                    'last_follow_up_date' => '2026-01-01',
                ],
            ]);

        $client->refresh();
        $this->assertEquals('Updated Name', $client->name);
        $this->assertEquals('original@example.com', $client->email);
    }

    public function test_partial_update_clears_field_when_explicitly_null(): void
    {
        $client = Client::create([
            'name' => 'John',
            'phone_number' => '123456',
            'email' => 'john@example.com',
            'last_follow_up_date' => '2026-01-01',
        ]);

        $response = $this->patchJson("/api/clients/{$client->id}", [
            'email' => null,
            'last_follow_up_date' => null,
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'name' => 'John',
                    'phone_number' => '123456',
                    'email' => null,
                    'last_follow_up_date' => null,
                ],
            ]);

        $client->refresh();
        $this->assertNull($client->email);
        $this->assertNull($client->last_follow_up_date);
    }

    public function test_update_client_rejects_empty_payload(): void
    {
        $client = Client::create([
            'name' => 'John',
            'phone_number' => '123456',
        ]);

        $response = $this->patchJson("/api/clients/{$client->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payload']);
    }

    public function test_cannot_delete_client_when_active_edits_exist(): void
    {
        $client = Client::create(['name' => 'Client With Edits', 'phone_number' => '111']);
        $company = Company::create(['client_id' => $client->id, 'name' => 'Company X']);
        $project = Project::create(['company_id' => $company->id, 'name' => 'Project 1']);

        Edit::create([
            'project_id' => $project->id,
            'title' => 'Active Edit',
            'priority' => 'High',
            'status' => 'In Progress',
        ]);

        $response = $this->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client']);

        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    public function test_can_delete_client_when_edits_are_inactive(): void
    {
        $client = Client::create(['name' => 'Client With Inactive Edits', 'phone_number' => '111']);
        $company = Company::create(['client_id' => $client->id, 'name' => 'Company Y']);
        $project = Project::create(['company_id' => $company->id, 'name' => 'Project 2']);

        Edit::create([
            'project_id' => $project->id,
            'title' => 'Done Edit',
            'priority' => 'Low',
            'status' => 'Done',
        ]);

        $response = $this->deleteJson("/api/clients/{$client->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Client deleted successfully.']);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        // Nullify auth by acting as guest
        $this->app['auth']->forgetGuards();

        $this->withServerVariables(['HTTP_AUTHORIZATION' => ''])
            ->getJson('/api/clients')
            ->assertUnauthorized();
    }
}

