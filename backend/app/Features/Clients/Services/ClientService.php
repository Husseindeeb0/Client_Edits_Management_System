<?php

namespace App\Features\Clients\Services;

use App\Features\Attachments\Models\Attachment;
use App\Features\Attachments\Services\AttachmentStorageService;
use App\Features\Clients\DTOs\CreateClientDTO;
use App\Features\Clients\DTOs\UpdateClientDTO;
use App\Features\Clients\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientService
{
    public function __construct(
        protected ?AttachmentStorageService $attachmentStorageService = null,
    ) {
        $this->attachmentStorageService = $this->attachmentStorageService ?? new AttachmentStorageService();
    }

    /**
     * Retrieve a paginated list of clients with optional search.
     */
    public function getPaginatedClients(?string $search, int $perPage): LengthAwarePaginator
    {
        $query = Client::query();

        if ($search !== null && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Create a new client record.
     */
    public function createClient(CreateClientDTO $dto): Client
    {
        return Client::create([
            'name' => $dto->name,
            'phone_number' => $dto->phoneNumber,
            'email' => $dto->email,
            'last_follow_up_date' => $dto->lastFollowUpDate,
        ]);
    }

    /**
     * Retrieve client details including associated companies.
     */
    public function getClientDetails(Client $client): Client
    {
        return $client->load('companies');
    }

    /**
     * Partially update an existing client using explicit attributes from DTO.
     */
    public function updateClient(Client $client, UpdateClientDTO $dto): Client
    {
        $attributesToUpdate = [];

        if ($dto->has('name')) {
            $attributesToUpdate['name'] = $dto->get('name');
        }

        if ($dto->has('phone_number')) {
            $attributesToUpdate['phone_number'] = $dto->get('phone_number');
        }

        if ($dto->has('email')) {
            $attributesToUpdate['email'] = $dto->get('email');
        }

        if ($dto->has('last_follow_up_date')) {
            $attributesToUpdate['last_follow_up_date'] = $dto->get('last_follow_up_date');
        }

        if (! empty($attributesToUpdate)) {
            $client->update($attributesToUpdate);
        }

        return $client;
    }

    /**
     * Delete a client if there are no descendant active edits.
     *
     * @throws ValidationException
     */
    public function deleteClient(Client $client): void
    {
        $activeStatuses = ['Pending', 'Confirmed', 'In Progress'];

        // Gather all descendant attachment file paths before deleting database records
        $filePaths = Attachment::query()
            ->whereHas('edit.project.company', function (Builder $query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->pluck('file_path')
            ->all();

        DB::transaction(function () use ($client, $activeStatuses) {
            // Lock client record for update to prevent concurrent race conditions
            $lockedClient = Client::query()
                ->where('id', $client->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasActiveEdits = $lockedClient->companies()
                ->whereHas('projects.edits', function (Builder $query) use ($activeStatuses) {
                    $query->whereIn('status', $activeStatuses);
                })
                ->exists();

            if ($hasActiveEdits) {
                throw ValidationException::withMessages([
                    'client' => ['Client cannot be deleted because it has active edits in progress.'],
                ]);
            }

            $lockedClient->delete();
        });

        // Clean up physical files after database transaction commits
        $this->attachmentStorageService->deleteFiles($filePaths);
    }
}
