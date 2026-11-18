<?php

namespace App\Features\Projects\Controllers;

use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Edits\Requests\ListEditsRequest;
use App\Features\Edits\Resources\EditListResource;
use App\Features\Projects\DTOs\CreateProjectDTO;
use App\Features\Projects\DTOs\UpdateProjectDTO;
use App\Features\Projects\Models\Project;
use App\Features\Projects\Requests\ListProjectsRequest;
use App\Features\Projects\Requests\StoreProjectRequest;
use App\Features\Projects\Requests\UpdateProjectRequest;
use App\Features\Projects\Resources\ClientProjectResource;
use App\Features\Projects\Resources\CompanyProjectResource;
use App\Features\Projects\Resources\ProjectDetailsResource;
use App\Features\Projects\Resources\ProjectListResource;
use App\Features\Projects\Resources\ProjectResource;
use App\Features\Projects\Services\ProjectService;
use App\Helpers\PaginationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService,
    ) {
    }

    /**
     * Retrieve a paginated list of all projects.
     */
    public function getPaginatedProjects(ListProjectsRequest $request): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->validated('per_page'));

        $projects = $this->projectService->getPaginatedProjects($request->validated('search'), $perPage);

        return ProjectListResource::collection($projects);
    }

    /**
     * Store a newly created project.
     */
    public function createProject(StoreProjectRequest $request): JsonResponse
    {
        $dto = new CreateProjectDTO(
            name: $request->validated('name'),
            companyId: (int) $request->validated('company_id'),
        );

        $project = $this->projectService->createProject($dto);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified project with its company and client.
     */
    public function getProjectDetails(Project $project): ProjectDetailsResource
    {
        $projectWithDetails = $this->projectService->getProjectDetails($project);

        return new ProjectDetailsResource($projectWithDetails);
    }

    /**
     * Update the specified project partially.
     */
    public function updateProject(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $dto = new UpdateProjectDTO($request->validated());
        $updatedProject = $this->projectService->updateProject($project, $dto);

        return new ProjectResource($updatedProject);
    }

    /**
     * Remove the specified project.
     */
    public function deleteProject(Project $project): JsonResponse
    {
        $this->projectService->deleteProject($project);

        return response()->json([
            'message' => 'Project deleted successfully.',
        ]);
    }

    /**
     * Retrieve paginated projects belonging to a client through companies.
     */
    public function clientProjects(ListProjectsRequest $request, Client $client): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->validated('per_page'));

        $projects = $this->projectService->getProjectsByClient($client, $request->validated('search'), $perPage);

        return ClientProjectResource::collection($projects);
    }

    /**
     * Retrieve paginated projects belonging to a company.
     */
    public function companyProjects(ListProjectsRequest $request, Company $company): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->validated('per_page'));

        $projects = $this->projectService->getProjectsByCompany($company, $request->validated('search'), $perPage);

        return CompanyProjectResource::collection($projects);
    }

    /**
     * Retrieve paginated edits belonging to a specific project.
     */
    public function projectEdits(ListEditsRequest $request, Project $project): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->validated('per_page'));

        $edits = $this->projectService->getEditsByProject($project, $request->filters(), $perPage);

        return EditListResource::collection($edits);
    }
}
