<?php

namespace App\Features\Projects\Controllers;

use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Edits\Requests\ListEditsRequest;
use App\Features\Edits\Resources\EditListResource;
use App\Features\Projects\Models\Project;
use App\Features\Projects\Requests\ListProjectsRequest;
use App\Features\Projects\Resources\ClientProjectResource;
use App\Features\Projects\Resources\CompanyProjectResource;
use App\Features\Projects\Services\ProjectService;
use App\Helpers\PaginationHelper;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService,
    ) {
    }

    /**
     * Retrieve paginated projects belonging to a client through companies.
     */
    public function clientProjects(ListProjectsRequest $request, Client $client): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->validated('per_page'));

        $projects = $this->projectService->getProjectsByClient($client, $perPage);

        return ClientProjectResource::collection($projects);
    }

    /**
     * Retrieve paginated projects belonging to a company.
     */
    public function companyProjects(ListProjectsRequest $request, Company $company): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->validated('per_page'));

        $projects = $this->projectService->getProjectsByCompany($company, $perPage);

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
