<?php

namespace App\Features\Edits\Services;

use App\Features\Attachments\Services\AttachmentStorageService;
use App\Features\Edits\DTOs\CreateEditDTO;
use App\Features\Edits\DTOs\UpdateEditDTO;
use App\Features\Edits\Models\Edit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EditService
{
    public function __construct(
        protected AttachmentStorageService $attachmentStorageService,
    ) {
    }

    /**
     * Retrieve a paginated list of edits with optional filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedEdits(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Edit::query()->with('project:id,name');

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

        if (! empty($filters['project_id'])) {
            $query->where('project_id', (int) $filters['project_id']);
        }

        return $query->latest('id')->paginate($perPage);
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
     * Retrieve edit details including project and attachments.
     */
    public function getEditDetails(Edit $edit): Edit
    {
        return $edit->load(['project:id,name', 'attachments']);
    }

    /**
     * Partially update an edit.
     */
    public function updateEdit(Edit $edit, UpdateEditDTO $dto): Edit
    {
        $attributesToUpdate = [];

        $fieldList = ['project_id', 'title', 'description', 'priority', 'status', 'price', 'is_paid', 'due_date'];
        foreach ($fieldList as $field) {
            if ($dto->has($field)) {
                $attributesToUpdate[$field] = $dto->get($field);
            }
        }

        if (! empty($attributesToUpdate)) {
            $edit->update($attributesToUpdate);
        }

        return $edit;
    }

    /**
     * Delete an edit and its physical attachments.
     */
    public function deleteEdit(Edit $edit): void
    {
        // Gather file paths before deleting database records
        $filePaths = $edit->attachments()->pluck('file_path')->all();

        DB::transaction(function () use ($edit) {
            $edit->delete();
        });

        // Clean up physical files after database transaction commits
        $this->attachmentStorageService->deleteFiles($filePaths);
    }
}

