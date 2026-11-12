<?php

namespace App\Features\Companies\Services;

use App\Features\Attachments\Models\Attachment;
use App\Features\Attachments\Services\AttachmentStorageService;
use App\Features\Companies\DTOs\CreateCompanyDTO;
use App\Features\Companies\DTOs\UpdateCompanyDTO;
use App\Features\Companies\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyService
{
    public function __construct(
        protected ?AttachmentStorageService $attachmentStorageService = null,
    ) {
        $this->attachmentStorageService = $this->attachmentStorageService ?? new AttachmentStorageService();
    }

    /**
     * Retrieve a paginated list of companies with optional search.
     */
    public function getPaginatedCompanies(?string $search, int $perPage): LengthAwarePaginator
    {
        $query = Company::query()->with('client');

        if ($search !== null && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhereHas('client', function (Builder $clientQuery) use ($term) {
                        $clientQuery->where('name', 'like', $term);
                    });
            });
        }

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Create a new company record.
     */
    public function createCompany(CreateCompanyDTO $dto): Company
    {
        $company = Company::create([
            'name' => $dto->name,
            'client_id' => $dto->clientId,
        ]);

        return $company->load('client');
    }

    /**
     * Retrieve company details.
     */
    public function getCompanyDetails(Company $company): Company
    {
        return $company->load('client');
    }

    /**
     * Partially update an existing company.
     */
    public function updateCompany(Company $company, UpdateCompanyDTO $dto): Company
    {
        $attributesToUpdate = [];

        if ($dto->has('name')) {
            $attributesToUpdate['name'] = $dto->get('name');
        }

        if ($dto->has('client_id')) {
            $attributesToUpdate['client_id'] = $dto->get('client_id');
        }

        if (! empty($attributesToUpdate)) {
            $company->update($attributesToUpdate);
        }

        return $company->load('client');
    }

    /**
     * Delete a company if there are no descendant active edits.
     *
     * @throws ValidationException
     */
    public function deleteCompany(Company $company): void
    {
        $activeStatuses = ['Pending', 'Confirmed', 'In Progress'];

        // Gather all descendant attachment file paths before deleting database records
        $filePaths = Attachment::query()
            ->whereHas('edit.project', function (Builder $query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->pluck('file_path')
            ->all();

        DB::transaction(function () use ($company, $activeStatuses) {
            $lockedCompany = Company::query()
                ->where('id', $company->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasActiveEdits = $lockedCompany->projects()
                ->whereHas('edits', function (Builder $query) use ($activeStatuses) {
                    $query->whereIn('status', $activeStatuses);
                })
                ->exists();

            if ($hasActiveEdits) {
                throw ValidationException::withMessages([
                    'company' => ['Company cannot be deleted because it has active edits in progress.'],
                ]);
            }

            $lockedCompany->delete();
        });

        // Clean up physical files after database transaction commits
        $this->attachmentStorageService->deleteFiles($filePaths);
    }
}
