<?php

namespace Tests\Feature;

use App\Features\Authentication\Models\User;
use App\Features\Edits\Enums\EditPriority;
use App\Features\Edits\Enums\EditStatus;
use App\Features\Edits\Models\Edit;
use App\Features\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectsEditsTest extends TestCase
{
    use RefreshDatabase;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());

        $this->project = Project::factory()->create(['name' => 'Website Redesign']);
    }

    public function test_lists_only_edits_of_the_project_with_project_name(): void
    {
        Edit::factory()->count(2)->for($this->project)->create();
        Edit::factory()->create(); // another project

        $this->getJson("/api/projects/{$this->project->project_id}/edits")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.project_id', $this->project->project_id)
            ->assertJsonPath('data.0.project_name', 'Website Redesign');
    }

    public function test_can_filter_project_edits(): void
    {
        Edit::factory()->for($this->project)->create([
            'title' => 'Fix footer', 'status' => EditStatus::Done, 'priority' => EditPriority::High, 'is_paid' => true,
        ]);
        Edit::factory()->for($this->project)->create([
            'title' => 'Fix header', 'status' => EditStatus::Pending, 'priority' => EditPriority::High, 'is_paid' => false,
        ]);
        Edit::factory()->for($this->project)->create([
            'title' => 'New page', 'status' => EditStatus::Done, 'priority' => EditPriority::Low, 'is_paid' => true,
        ]);

        $url = "/api/projects/{$this->project->project_id}/edits";

        $this->getJson("$url?search=Fix")->assertJsonCount(2, 'data');
        $this->getJson("$url?status=Done")->assertJsonCount(2, 'data');
        $this->getJson("$url?priority=High")->assertJsonCount(2, 'data');
        $this->getJson("$url?is_paid=false")->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Fix header');
        $this->getJson("$url?status=Done&priority=High&is_paid=true")->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Fix footer');
    }

    public function test_project_id_query_parameter_cannot_escape_the_route_project(): void
    {
        $otherProject = Project::factory()->create();
        Edit::factory()->for($this->project)->create();
        Edit::factory()->count(3)->for($otherProject)->create();

        $this->getJson("/api/projects/{$this->project->project_id}/edits?project_id={$otherProject->project_id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.project_id', $this->project->project_id);
    }

    public function test_listing_project_edits_runs_a_constant_number_of_queries(): void
    {
        Edit::factory()->for($this->project)->create();
        $queriesForOne = $this->countQueries("/api/projects/{$this->project->project_id}/edits");

        Edit::factory()->count(9)->for($this->project)->create();
        $queriesForTen = $this->countQueries("/api/projects/{$this->project->project_id}/edits");

        $this->assertSame($queriesForOne, $queriesForTen, 'Listing project edits must not run one query per edit (N+1).');
    }

    public function test_returns_404_for_missing_project(): void
    {
        $this->getJson('/api/projects/999999/edits')->assertNotFound();
    }

    public function test_requires_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson("/api/projects/{$this->project->project_id}/edits")->assertUnauthorized();
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
