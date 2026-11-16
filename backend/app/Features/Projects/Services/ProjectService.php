<?php

namespace App\Features\Projects\Services;

use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Edits\DTOs\EditFiltersDTO;
use App\Features\Edits\Services\EditService;
use App\Features\Projects\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProjectService
{
    public function __construct(
        protected EditService $editService,
    ) {
    }

    /**
     * Retrieve paginated projects belonging to a client through its companies.
     */
    public function getProjectsByClient(Client $client, int $perPage): LengthAwarePaginator
    {
        return Project::query()
            ->whereHas('company', function (Builder $query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->with('company:id,name')
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Retrieve paginated projects belonging to a specified company.
     */
    public function getProjectsByCompany(Company $company, int $perPage): LengthAwarePaginator
    {
        return $company->projects()
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Retrieve paginated edits belonging to a specified project. Any project_id
     * in the filters is replaced by the route's project.
     */
    public function getEditsByProject(Project $project, EditFiltersDTO $filters, int $perPage): LengthAwarePaginator
    {
        return $this->editService->getPaginatedEdits($filters->forProject($project->id), $perPage);
    }
}
