<?php

namespace App\Features\Projects\Services;

use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Projects\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    /**
     * Retrieve paginated projects belonging to a client through its companies.
     */
    public function getProjectsByClient(Client $client, int $perPage): LengthAwarePaginator
    {
        return Project::query()
            ->whereHas('company', function ($query) use ($client) {
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
        return Project::query()
            ->where('company_id', $company->id)
            ->with('company:id,name')
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Retrieve paginated edits belonging to a specified project.
     *
     * @param array<string, mixed> $filters
     */
    public function getEditsByProject(Project $project, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $project->edits();

        if (! empty($filters['search'])) {
            $term = '%'.trim((string) $filters['search']).'%';
            $query->where('title', 'like', $term);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (array_key_exists('is_paid', $filters) && $filters['is_paid'] !== null && $filters['is_paid'] !== '') {
            $query->where('is_paid', filter_var($filters['is_paid'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->latest('id')->paginate($perPage);
    }
}
