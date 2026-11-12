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

class CompaniesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        $this->client = Client::create([
            'name' => 'Test Client',
            'phone_number' => '+123456789',
            'email' => 'client@example.com',
        ]);
    }

    public function test_can_create_company(): void
    {
        $response = $this->postJson('/api/companies', [
            'name' => 'Acme Corporation',
            'client_id' => $this->client->id,
        ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'name' => 'Acme Corporation',
                    'client_id' => $this->client->id,
                ],
            ]);

        $this->assertArrayNotHasKey('client', $response->json('data'));

        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Corporation',
            'client_id' => $this->client->id,
        ]);
    }

    public function test_create_company_fails_with_invalid_client(): void
    {
        $response = $this->postJson('/api/companies', [
            'name' => 'Acme Corp',
            'client_id' => 999999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    }

    public function test_can_retrieve_paginated_companies_with_client(): void
    {
        Company::create(['name' => 'Company A', 'client_id' => $this->client->id]);
        Company::create(['name' => 'Company B', 'client_id' => $this->client->id]);

        $response = $this->getJson('/api/companies');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'client_id', 'client_name'],
                ],
                'links',
                'meta',
            ])
            ->assertJsonFragment([
                'client_name' => 'Test Client',
            ]);
    }

    public function test_can_search_companies_by_name_and_client_name(): void
    {
        $client2 = Client::create(['name' => 'Wayne Enterprises Owner', 'phone_number' => '999']);

        Company::create(['name' => 'Stark Industries', 'client_id' => $this->client->id]);
        Company::create(['name' => 'Wayne Enterprises', 'client_id' => $client2->id]);

        $resByComp = $this->getJson('/api/companies?search=Stark');
        $resByComp->assertOk()->assertJsonCount(1, 'data');

        $resByClient = $this->getJson('/api/companies?search=Wayne Enterprises Owner');
        $resByClient->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_can_get_company_details_without_projects(): void
    {
        $company = Company::create(['name' => 'Cyberdyne', 'client_id' => $this->client->id]);
        Project::create(['name' => 'Project T-800', 'company_id' => $company->id]);

        $response = $this->getJson("/api/companies/{$company->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $company->id,
                    'name' => 'Cyberdyne',
                    'client_id' => $this->client->id,
                    'client' => [
                        'id' => $this->client->id,
                    ],
                ],
            ]);

        $this->assertArrayNotHasKey('projects', $response->json('data'));
    }

    public function test_can_update_company_partially(): void
    {
        $company = Company::create(['name' => 'Old Company Name', 'client_id' => $this->client->id]);
        $newClient = Client::create(['name' => 'New Client', 'phone_number' => '888']);

        $response = $this->patchJson("/api/companies/{$company->id}", [
            'name' => 'Updated Company Name',
            'client_id' => $newClient->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'name' => 'Updated Company Name',
                    'client_id' => $newClient->id,
                ],
            ]);

        $company->refresh();
        $this->assertEquals('Updated Company Name', $company->name);
        $this->assertEquals($newClient->id, $company->client_id);
    }

    public function test_update_company_rejects_empty_payload(): void
    {
        $company = Company::create(['name' => 'Some Company', 'client_id' => $this->client->id]);

        $response = $this->patchJson("/api/companies/{$company->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payload']);
    }

    public function test_cannot_delete_company_with_active_edits(): void
    {
        $company = Company::create(['name' => 'Locked Company', 'client_id' => $this->client->id]);
        $project = Project::create(['name' => 'Project Alpha', 'company_id' => $company->id]);

        Edit::create([
            'project_id' => $project->id,
            'title' => 'In Progress Edit',
            'priority' => 'High',
            'status' => 'In Progress',
        ]);

        $response = $this->deleteJson("/api/companies/{$company->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company']);

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_can_delete_company_with_inactive_edits(): void
    {
        $company = Company::create(['name' => 'Deletable Company', 'client_id' => $this->client->id]);
        $project = Project::create(['name' => 'Project Beta', 'company_id' => $company->id]);

        Edit::create([
            'project_id' => $project->id,
            'title' => 'Completed Edit',
            'priority' => 'Medium',
            'status' => 'Done',
        ]);

        $response = $this->deleteJson("/api/companies/{$company->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Company deleted successfully.']);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->app['auth']->forgetGuards();

        $this->withServerVariables(['HTTP_AUTHORIZATION' => ''])
            ->getJson('/api/companies')
            ->assertUnauthorized();
    }
}

