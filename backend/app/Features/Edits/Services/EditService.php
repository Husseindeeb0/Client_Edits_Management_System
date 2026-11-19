<?php

namespace App\Features\Edits\Services;

use App\Features\Attachments\Services\AttachmentStorageService;
use App\Features\Edits\DTOs\CreateEditDTO;
use App\Features\Edits\DTOs\EditFiltersDTO;
use App\Features\Edits\DTOs\UpdateEditDTO;
use App\Features\Edits\Enums\EditStatus;
use App\Features\Edits\Models\Edit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EditService
{
    public function __construct(
        protected AttachmentStorageService $attachmentStorageService,
    ) {
    }

    /**
     * Retrieve a paginated list of edits matching the given filters.
     */
    public function getPaginatedEdits(EditFiltersDTO $filters, int $perPage): LengthAwarePaginator
    {
        $query = Edit::query()->with('project:project_id,name');

        if ($filters->search !== null) {
            $query->where('title', 'like', '%'.$filters->search.'%');
        }

        if ($filters->status !== null) {
            $query->where('status', $filters->status);
        }

        if ($filters->priority !== null) {
            $query->where('priority', $filters->priority);
        }

        if ($filters->isPaid !== null) {
            $query->where('is_paid', $filters->isPaid);
        }

        if ($filters->projectId !== null) {
            $query->where('project_id', $filters->projectId);
        }

        if ($filters->clientId !== null) {
            $query->whereHas('project.company', function (Builder $q) use ($filters) {
                $q->where('client_id', $filters->clientId);
            });
        }

        if ($filters->activeOnly) {
            $query->whereIn('status', EditStatus::active());
        }

        return $query->latest('edit_id')->paginate($perPage);
    }

    /**
     * Create a new edit.
     */
    public function createEdit(CreateEditDTO $dto): Edit
    {
        return Edit::create([
            'project_id' => $dto->projectId,
            'title' => $dto->title,
            'description' => $dto->description,
            'priority' => $dto->priority,
            'status' => $dto->status,
            'price' => $dto->price,
            'is_paid' => $dto->isPaid,
            'due_date' => $dto->dueDate,
        ]);
    }

    /**
     * Retrieve edit details with the client -> company -> project breadcrumb
     * and the attachments.
     */
    public function getEditDetails(Edit $edit): Edit
    {
        return $edit->load([
            'project:project_id,name,company_id',
            'project.company:company_id,name,client_id',
            'project.company.client:client_id,name',
            'attachments',
        ]);
    }

    /**
     * Partially update an edit with the validated fields that were sent.
     */
    public function updateEdit(Edit $edit, UpdateEditDTO $dto): Edit
    {
        $edit->update($dto->toArray());

        return $edit;
    }

    /**
     * Delete an edit and its physical attachments.
     */
    public function deleteEdit(Edit $edit): void
    {
        // Gather file paths before the row (and its attachment rows, via cascade) are deleted
        $filePaths = $edit->attachments()->pluck('file_path')->all();

        $edit->delete();

        $this->attachmentStorageService->deleteFiles($filePaths);
    }
}
