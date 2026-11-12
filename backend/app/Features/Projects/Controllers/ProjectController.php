<?php

namespace App\Features\Projects\Controllers;

use App\Features\Clients\Models\Client;
use App\Features\Companies\Models\Company;
use App\Features\Edits\Resources\EditListResource;
use App\Features\Projects\Models\Project;
use App\Features\Projects\Resources\ClientProjectResource;
use App\Features\Projects\Resources\CompanyProjectResource;
use App\Features\Projects\Services\ProjectService;
use App\Helpers\PaginationHelper;
use Illuminate\Http\Request;
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
    public function clientProjects(Request $request, Client $client): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->query('per_page'));

        $projects = $this->projectService->getProjectsByClient($client, $perPage);

        return ClientProjectResource::collection($projects);
    }

    /**
     * Retrieve paginated projects belonging to a company.
     */
    public function companyProjects(Request $request, Company $company): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->query('per_page'));

        $projects = $this->projectService->getProjectsByCompany($company, $perPage);

        return CompanyProjectResource::collection($projects);
    }

    /**
     * Retrieve paginated edits belonging to a specific project.
     */
    public function projectEdits(Request $request, Project $project): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->query('per_page'));

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
            'is_paid' => $request->query('is_paid'),
        ];

        $edits = $this->projectService->getEditsByProject($project, $filters, $perPage);

        return EditListResource::collection($edits);
    }
}
