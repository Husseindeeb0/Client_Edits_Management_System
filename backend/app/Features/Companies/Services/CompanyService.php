<?php

namespace App\Features\Companies\Services;

use App\Features\Attachments\Models\Attachment;
use App\Features\Attachments\Services\AttachmentStorageService;
use App\Features\Companies\DTOs\CreateCompanyDTO;
use App\Features\Companies\DTOs\UpdateCompanyDTO;
use App\Features\Companies\Models\Company;
use App\Features\Edits\Enums\EditStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyService
{
    public function __construct(
        protected AttachmentStorageService $attachmentStorageService,
    ) {
    }

    /**
     * Retrieve a paginated list of companies, optionally limited to one
     * client and searched by company or client name.
     */
    public function getPaginatedCompanies(?string $search, ?int $clientId, int $perPage): LengthAwarePaginator
    {
        $query = Company::query()->with('client:client_id,name');

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        if ($search !== null) {
            $term = '%'.$search.'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhereHas('client', function (Builder $clientQuery) use ($term) {
                        $clientQuery->where('name', 'like', $term);
                    });
            });
        }

        return $query->latest('company_id')->paginate($perPage);
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
     * Partially update a company with the validated fields that were sent.
     */
    public function updateCompany(Company $company, UpdateCompanyDTO $dto): Company
    {
        $company->update($dto->toArray());

        return $company->load('client');
    }

    /**
     * Delete a company if none of its edits are still active.
     *
     * @throws ValidationException
     */
    public function deleteCompany(Company $company): void
    {
        $filePaths = DB::transaction(function () use ($company) {
            // Lock the company row so concurrent requests can't add active edits mid-check
            $lockedCompany = Company::query()
                ->whereKey($company->company_id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasActiveEdits = $lockedCompany->projects()
                ->whereHas('edits', function (Builder $query) {
                    $query->whereIn('status', EditStatus::active());
                })
                ->exists();

            if ($hasActiveEdits) {
                throw ValidationException::withMessages([
                    'company' => ['Company cannot be deleted because it has active edits in progress.'],
                ]);
            }

            // Collect file paths before the cascade removes the attachment rows
            $filePaths = Attachment::query()
                ->whereHas('edit.project', function (Builder $query) use ($lockedCompany) {
                    $query->where('company_id', $lockedCompany->company_id);
                })
                ->pluck('file_path')
                ->all();

            $lockedCompany->delete();

            return $filePaths;
        });

        // Remove physical files only after the transaction has committed
        $this->attachmentStorageService->deleteFiles($filePaths);
    }
}
