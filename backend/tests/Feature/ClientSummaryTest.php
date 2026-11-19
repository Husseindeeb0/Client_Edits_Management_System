<?php

namespace Tests\Feature;

use App\Features\Authentication\Models\User;
use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Edits\Enums\EditStatus;
use App\Features\Edits\Models\Edit;
use App\Features\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());

        $this->client = Client::factory()->create();
        $company = Company::factory()->for($this->client)->create();
        $this->project = Project::factory()->for($company)->create();
    }

    public function test_returns_zeroes_for_a_client_without_data(): void
    {
        $emptyClient = Client::factory()->create();

        $this->getJson("/api/clients/{$emptyClient->client_id}/summary")
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'companies_count' => 0,
                    'projects_count' => 0,
                    'active_edits_count' => 0,
                    'unpaid_total' => 0.0,
                ],
            ]);
    }

    public function test_counts_companies_and_projects_across_all_companies(): void
    {
        $secondCompany = Company::factory()->for($this->client)->create();
        Project::factory()->count(2)->for($secondCompany)->create();

        // Another client's data must not be counted
        Project::factory()->create();

        $this->getJson("/api/clients/{$this->client->client_id}/summary")
            ->assertOk()
            ->assertJsonPath('data.companies_count', 2)
            ->assertJsonPath('data.projects_count', 3);
    }

    public function test_counts_only_active_edits(): void
    {
        foreach (EditStatus::cases() as $status) {
            Edit::factory()->for($this->project)->create(['status' => $status]);
        }
        Edit::factory()->active()->create(); // another client

        $this->getJson("/api/clients/{$this->client->client_id}/summary")
            ->assertOk()
            ->assertJsonPath('data.active_edits_count', count(EditStatus::active()));
    }

    public function test_unpaid_total_excludes_paid_and_cancelled_edits(): void
    {
        $edit = fn (array $attributes) => Edit::factory()->for($this->project)->create($attributes);

        $edit(['price' => 100.25, 'is_paid' => false, 'status' => EditStatus::Pending]);   // counted
        $edit(['price' => 200.00, 'is_paid' => false, 'status' => EditStatus::Done]);      // counted
        $edit(['price' => 999.00, 'is_paid' => true, 'status' => EditStatus::Done]);       // paid
        $edit(['price' => 500.00, 'is_paid' => false, 'status' => EditStatus::Cancelled]); // cancelled
        $edit(['price' => null, 'is_paid' => false, 'status' => EditStatus::Pending]);     // no price
        Edit::factory()->create(['price' => 50, 'is_paid' => false, 'status' => EditStatus::Pending]); // another client

        $this->getJson("/api/clients/{$this->client->client_id}/summary")
            ->assertOk()
            ->assertJsonPath('data.unpaid_total', 300.25);
    }

    public function test_summary_runs_a_constant_number_of_queries(): void
    {
        Edit::factory()->for($this->project)->create();
        $queriesForOne = $this->countQueries("/api/clients/{$this->client->client_id}/summary");

        Edit::factory()->count(20)->for($this->project)->create();
        $queriesForMany = $this->countQueries("/api/clients/{$this->client->client_id}/summary");

        $this->assertSame($queriesForOne, $queriesForMany);
    }

    public function test_returns_404_for_missing_client(): void
    {
        $this->getJson('/api/clients/999999/summary')->assertNotFound();
    }

    public function test_requires_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson("/api/clients/{$this->client->client_id}/summary")->assertUnauthorized();
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
