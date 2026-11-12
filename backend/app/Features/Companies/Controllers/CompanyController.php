<?php

namespace App\Features\Companies\Controllers;

use App\Features\Companies\DTOs\CreateCompanyDTO;
use App\Features\Companies\DTOs\UpdateCompanyDTO;
use App\Features\Companies\Models\Company;
use App\Features\Companies\Requests\StoreCompanyRequest;
use App\Features\Companies\Requests\UpdateCompanyRequest;
use App\Features\Companies\Resources\CompanyDetailsResource;
use App\Features\Companies\Resources\CompanyListResource;
use App\Features\Companies\Resources\CompanyResource;
use App\Features\Companies\Services\CompanyService;
use App\Helpers\PaginationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class CompanyController extends Controller
{
    public function __construct(
        protected CompanyService $companyService,
    ) {
    }

    /**
     * Retrieve a paginated list of companies.
     */
    public function getPaginatedCompanies(Request $request): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->query('per_page'));

        $search = $request->query('search');
        if (is_string($search)) {
            $search = trim($search);
        } else {
            $search = null;
        }

        $companies = $this->companyService->getPaginatedCompanies($search, $perPage);

        return CompanyListResource::collection($companies);
    }

    /**
     * Store a newly created company.
     */
    public function createCompany(StoreCompanyRequest $request): JsonResponse
    {
        $dto = new CreateCompanyDTO(
            name: $request->validated('name'),
            clientId: (int) $request->validated('client_id'),
        );

        $company = $this->companyService->createCompany($dto);

        return (new CompanyResource($company))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified company details.
     */
    public function getCompanyDetails(Company $company): CompanyDetailsResource
    {
        $companyWithDetails = $this->companyService->getCompanyDetails($company);

        return new CompanyDetailsResource($companyWithDetails);
    }

    /**
     * Update the specified company partially.
     */
    public function updateCompany(UpdateCompanyRequest $request, Company $company): CompanyResource
    {
        $allowedKeys = ['name', 'client_id'];
        $validatedData = $request->validated();
        $filteredData = [];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $validatedData)) {
                $filteredData[$key] = $validatedData[$key];
            }
        }

        $dto = new UpdateCompanyDTO($filteredData);
        $updatedCompany = $this->companyService->updateCompany($company, $dto);

        return new CompanyResource($updatedCompany);
    }

    /**
     * Remove the specified company.
     */
    public function deleteCompany(Company $company): JsonResponse
    {
        $this->companyService->deleteCompany($company);

        return response()->json([
            'message' => 'Company deleted successfully.',
        ]);
    }
}

