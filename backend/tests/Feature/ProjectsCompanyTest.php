<?php

namespace Tests\Feature;

use App\Features\Authentication\Models\User;
use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectsCompanyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_get_paginated_projects_belonging_to_company(): void
    {
        $client = Client::create(['name' => 'Client 1', 'phone_number' => '123']);
        $companyA = Company::create(['name' => 'Company A', 'client_id' => $client->id]);
        $companyB = Company::create(['name' => 'Company B', 'client_id' => $client->id]);

        $projectA1 = Project::create(['name' => 'Project 1A', 'company_id' => $companyA->id]);
        $projectA2 = Project::create(['name' => 'Project 2A', 'company_id' => $companyA->id]);
        $projectB = Project::create(['name' => 'Project 1B', 'company_id' => $companyB->id]);

        $response = $this->getJson("/api/projects/company/{$companyA->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name'],
                ],
                'links',
                'meta',
            ]);

        $projectIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($projectA1->id, $projectIds);
        $this->assertContains($projectA2->id, $projectIds);
        $this->assertNotContains($projectB->id, $projectIds);
    }

    public function test_company_projects_requires_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $client = Client::create(['name' => 'Client', 'phone_number' => '123']);
        $company = Company::create(['name' => 'Company', 'client_id' => $client->id]);

        $this->withServerVariables(['HTTP_AUTHORIZATION' => ''])
            ->getJson("/api/projects/company/{$company->id}")
            ->assertUnauthorized();
    }
}

