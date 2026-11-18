<?php

namespace App\Features\Projects\Services;

use App\Features\Attachments\Models\Attachment;
use App\Features\Attachments\Services\AttachmentStorageService;
use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Edits\DTOs\EditFiltersDTO;
use App\Features\Edits\Enums\EditStatus;
use App\Features\Edits\Services\EditService;
use App\Features\Projects\DTOs\CreateProjectDTO;
use App\Features\Projects\DTOs\UpdateProjectDTO;
use App\Features\Projects\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    public function __construct(
        protected EditService $editService,
        protected AttachmentStorageService $attachmentStorageService,
    ) {
    }

    /**
     * Retrieve a paginated list of all projects, searchable by project,
     * company or client name.
     */
    public function getPaginatedProjects(?string $search, int $perPage): LengthAwarePaginator
    {
        $query = Project::query()->with(['company:id,name,client_id', 'company.client:id,name']);

        if ($search !== null) {
            $term = '%'.$search.'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhereHas('company', function (Builder $companyQuery) use ($term) {
                        $companyQuery->where('name', 'like', $term)
                            ->orWhereHas('client', function (Builder $clientQuery) use ($term) {
                                $clientQuery->where('name', 'like', $term);
                            });
                    });
            });
        }

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Retrieve paginated projects belonging to a client through its companies,
     * optionally searched by project name.
     */
    public function getProjectsByClient(Client $client, ?string $search, int $perPage): LengthAwarePaginator
    {
        return Project::query()
            ->whereHas('company', function (Builder $query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->when($search !== null, fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
            ->with('company:id,name')
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Retrieve paginated projects belonging to a specified company, optionally
     * searched by project name.
     */
    public function getProjectsByCompany(Company $company, ?string $search, int $perPage): LengthAwarePaginator
    {
        return $company->projects()
            ->when($search !== null, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Create a new project.
     */
    public function createProject(CreateProjectDTO $dto): Project
    {
        return Project::create([
            'name' => $dto->name,
            'company_id' => $dto->companyId,
        ]);
    }

    /**
     * Retrieve project details with its company and client for the breadcrumb.
     */
    public function getProjectDetails(Project $project): Project
    {
        return $project->load(['company:id,name,client_id', 'company.client:id,name']);
    }

    /**
     * Partially update a project with the validated fields that were sent.
     */
    public function updateProject(Project $project, UpdateProjectDTO $dto): Project
    {
        $project->update($dto->toArray());

        return $project;
    }

    /**
     * Delete a project if none of its edits are still active.
     *
     * @throws ValidationException
     */
    public function deleteProject(Project $project): void
    {
        $filePaths = DB::transaction(function () use ($project) {
            // Lock the project row so concurrent requests can't add active edits mid-check
            $lockedProject = Project::query()
                ->whereKey($project->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasActiveEdits = $lockedProject->edits()
                ->whereIn('status', EditStatus::active())
                ->exists();

            if ($hasActiveEdits) {
                throw ValidationException::withMessages([
                    'project' => ['Project cannot be deleted because it has active edits in progress.'],
                ]);
            }

            // Collect file paths before the cascade removes the attachment rows
            $filePaths = Attachment::query()
                ->whereHas('edit', function (Builder $query) use ($lockedProject) {
                    $query->where('project_id', $lockedProject->id);
                })
                ->pluck('file_path')
                ->all();

            $lockedProject->delete();

            return $filePaths;
        });

        // Remove physical files only after the transaction has committed
        $this->attachmentStorageService->deleteFiles($filePaths);
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
