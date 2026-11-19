<?php

namespace Tests\Feature;

use App\Features\Authentication\Models\User;
use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectsClientTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_get_paginated_projects_belonging_to_client_through_companies(): void
    {
        $clientA = Client::create(['name' => 'Client A', 'phone_number' => '111']);
        $companyA1 = Company::create(['client_id' => $clientA->client_id, 'name' => 'Company A1']);
        $companyA2 = Company::create(['client_id' => $clientA->client_id, 'name' => 'Company A2']);

        $projectA1 = Project::create(['company_id' => $companyA1->company_id, 'name' => 'Project Alpha']);
        $projectA2 = Project::create(['company_id' => $companyA2->company_id, 'name' => 'Project Beta']);

        // Another client with projects
        $clientB = Client::create(['name' => 'Client B', 'phone_number' => '222']);
        $companyB = Company::create(['client_id' => $clientB->client_id, 'name' => 'Company B']);
        $projectB = Project::create(['company_id' => $companyB->company_id, 'name' => 'Project Gamma']);

        $response = $this->getJson("/api/projects/client/{$clientA->client_id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'company_id', 'company_name'],
                ],
                'links',
                'meta',
            ]);

        $projectIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($projectA1->project_id, $projectIds);
        $this->assertContains($projectA2->project_id, $projectIds);
        $this->assertNotContains($projectB->project_id, $projectIds);
    }

    public function test_returns_empty_when_client_has_no_projects(): void
    {
        $client = Client::create(['name' => 'Client No Projects', 'phone_number' => '333']);

        $response = $this->getJson("/api/projects/client/{$client->client_id}");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_projects_client_endpoint_requires_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $client = Client::create(['name' => 'Client X', 'phone_number' => '444']);

        $this->withServerVariables(['HTTP_AUTHORIZATION' => ''])
            ->getJson("/api/projects/client/{$client->client_id}")
            ->assertUnauthorized();
    }
}

