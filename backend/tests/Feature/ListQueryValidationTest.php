<?php

namespace Tests\Feature;

use App\Features\Authentication\Models\User;
use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Query parameters on list endpoints are validated, so malformed input
 * returns 422 instead of being ignored or causing a server error.
 */
class ListQueryValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_search_must_be_a_string_on_every_list(): void
    {
        $project = Project::factory()->create();

        foreach (['/api/clients', '/api/companies', '/api/edits', "/api/projects/{$project->project_id}/edits", '/api/attachments'] as $url) {
            $this->getJson("$url?search[]=x")
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['search']);
        }
    }

    public function test_search_is_limited_in_length(): void
    {
        $this->getJson('/api/clients?search='.str_repeat('a', 256))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['search']);
    }

    public function test_per_page_must_be_a_positive_integer(): void
    {
        $client = Client::factory()->create();
        $company = Company::factory()->create();

        $urls = ['/api/clients', '/api/companies', '/api/edits', "/api/projects/client/{$client->client_id}", "/api/projects/company/{$company->company_id}", '/api/attachments'];

        foreach ($urls as $url) {
            $this->getJson("$url?per_page=abc")->assertUnprocessable()->assertJsonValidationErrors(['per_page']);
            $this->getJson("$url?per_page=0")->assertUnprocessable()->assertJsonValidationErrors(['per_page']);
        }
    }

    public function test_per_page_is_capped_at_configured_maximum(): void
    {
        $this->getJson('/api/clients?per_page=1000')
            ->assertOk()
            ->assertJsonPath('meta.per_page', config('pagination.max_per_page'));
    }

    public function test_per_page_defaults_when_not_given(): void
    {
        $this->getJson('/api/clients')
            ->assertOk()
            ->assertJsonPath('meta.per_page', config('pagination.per_page'));
    }

    public function test_edit_filters_reject_unknown_values(): void
    {
        $project = Project::factory()->create();

        foreach (['/api/edits', "/api/projects/{$project->project_id}/edits"] as $url) {
            $this->getJson("$url?status=Bogus")->assertUnprocessable()->assertJsonValidationErrors(['status']);
            $this->getJson("$url?priority=Urgent")->assertUnprocessable()->assertJsonValidationErrors(['priority']);
            $this->getJson("$url?is_paid=maybe")->assertUnprocessable()->assertJsonValidationErrors(['is_paid']);
        }

        $this->getJson('/api/edits?project_id=abc')->assertUnprocessable()->assertJsonValidationErrors(['project_id']);
        $this->getJson('/api/edits?client_id=abc')->assertUnprocessable()->assertJsonValidationErrors(['client_id']);
        $this->getJson('/api/edits?client_id=0')->assertUnprocessable()->assertJsonValidationErrors(['client_id']);
        $this->getJson('/api/edits?active=maybe')->assertUnprocessable()->assertJsonValidationErrors(['active']);
    }

    public function test_company_client_filter_must_be_a_positive_integer(): void
    {
        $this->getJson('/api/companies?client_id=abc')->assertUnprocessable()->assertJsonValidationErrors(['client_id']);
        $this->getJson('/api/companies?client_id=0')->assertUnprocessable()->assertJsonValidationErrors(['client_id']);
    }

    public function test_edit_filters_accept_valid_values(): void
    {
        $this->getJson('/api/edits?status=In%20Progress&priority=High&is_paid=0&project_id=1&client_id=1&active=true&search=logo')
            ->assertOk();
    }
}
