<?php

namespace App\Features\Clients\Services;

use App\Features\Attachments\Models\Attachment;
use App\Features\Attachments\Services\AttachmentStorageService;
use App\Features\Clients\DTOs\ClientSummaryDTO;
use App\Features\Clients\DTOs\CreateClientDTO;
use App\Features\Clients\DTOs\UpdateClientDTO;
use App\Features\Clients\Models\Client;
use App\Features\Edits\Enums\EditStatus;
use App\Features\Edits\Models\Edit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientService
{
    public function __construct(
        protected AttachmentStorageService $attachmentStorageService,
    ) {
    }

    /**
     * Retrieve a paginated list of clients with optional search.
     */
    public function getPaginatedClients(?string $search, int $perPage): LengthAwarePaginator
    {
        $query = Client::query();

        if ($search !== null) {
            $term = '%'.$search.'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        return $query->latest('client_id')->paginate($perPage);
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
     * Compute the numbers shown on the client details summary cards. Every
     * value is a single aggregate query; no rows are loaded.
     *
     * The unpaid total counts edits that are not paid and not cancelled,
     * since a cancelled edit is not owed.
     */
    public function getClientSummary(Client $client): ClientSummaryDTO
    {
        $clientEdits = Edit::query()->whereHas('project.company', function (Builder $query) use ($client) {
            $query->where('client_id', $client->client_id);
        });

        return new ClientSummaryDTO(
            companiesCount: $client->companies()->count(),
            projectsCount: $client->projects()->count(),
            activeEditsCount: (clone $clientEdits)->whereIn('status', EditStatus::active())->count(),
            unpaidTotal: round((float) (clone $clientEdits)
                ->where('is_paid', false)
                ->where('status', '!=', EditStatus::Cancelled)
                ->sum('price'), 2),
        );
    }

    /**
     * Partially update a client with the validated fields that were sent.
     */
    public function updateClient(Client $client, UpdateClientDTO $dto): Client
    {
        $client->update($dto->toArray());

        return $client;
    }

    /**
     * Delete a client if none of its edits are still active.
     *
     * @throws ValidationException
     */
    public function deleteClient(Client $client): void
    {
        $filePaths = DB::transaction(function () use ($client) {
            // Lock the client row so concurrent requests can't add active edits mid-check
            $lockedClient = Client::query()
                ->whereKey($client->client_id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasActiveEdits = $lockedClient->companies()
                ->whereHas('projects.edits', function (Builder $query) {
                    $query->whereIn('status', EditStatus::active());
                })
                ->exists();

            if ($hasActiveEdits) {
                throw ValidationException::withMessages([
                    'client' => ['Client cannot be deleted because it has active edits in progress.'],
                ]);
            }

            // Collect file paths before the cascade removes the attachment rows
            $filePaths = Attachment::query()
                ->whereHas('edit.project.company', function (Builder $query) use ($lockedClient) {
                    $query->where('client_id', $lockedClient->client_id);
                })
                ->pluck('file_path')
                ->all();

            $lockedClient->delete();

            return $filePaths;
        });

        // Remove physical files only after the transaction has committed
        $this->attachmentStorageService->deleteFiles($filePaths);
    }
}
