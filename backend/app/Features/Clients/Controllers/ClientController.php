<?php

namespace App\Features\Clients\Controllers;

use App\Features\Clients\DTOs\CreateClientDTO;
use App\Features\Clients\DTOs\UpdateClientDTO;
use App\Features\Clients\Models\Client;
use App\Features\Clients\Requests\StoreClientRequest;
use App\Features\Clients\Requests\UpdateClientRequest;
use App\Features\Clients\Resources\ClientDetailsResource;
use App\Features\Clients\Resources\ClientListResource;
use App\Features\Clients\Resources\ClientResource;
use App\Features\Clients\Services\ClientService;
use App\Helpers\PaginationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class ClientController extends Controller
{
    public function __construct(
        protected ClientService $clientService,
    ) {
    }

    /**
     * Retrieve a paginated list of clients.
     */
    public function getPaginatedClients(Request $request): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->query('per_page'));

        $search = $request->query('search');
        if (is_string($search)) {
            $search = trim($search);
        } else {
            $search = null;
        }

        $clients = $this->clientService->getPaginatedClients($search, $perPage);

        return ClientListResource::collection($clients);
    }

    /**
     * Store a newly created client.
     */
    public function createClient(StoreClientRequest $request): JsonResponse
    {
        $dto = new CreateClientDTO(
            name: $request->validated('name'),
            phoneNumber: $request->validated('phone_number'),
            email: $request->validated('email'),
            lastFollowUpDate: $request->validated('last_follow_up_date'),
        );

        $client = $this->clientService->createClient($dto);

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified client with associated companies.
     */
    public function getClientDetails(Client $client): ClientDetailsResource
    {
        $clientWithDetails = $this->clientService->getClientDetails($client);

        return new ClientDetailsResource($clientWithDetails);
    }

    /**
     * Update the specified client partially.
     */
    public function updateClient(UpdateClientRequest $request, Client $client): ClientResource
    {
        $allowedKeys = ['name', 'phone_number', 'email', 'last_follow_up_date'];
        $validatedData = $request->validated();
        $filteredData = [];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $validatedData)) {
                $filteredData[$key] = $validatedData[$key];
            }
        }

        $dto = new UpdateClientDTO($filteredData);
        $updatedClient = $this->clientService->updateClient($client, $dto);

        return new ClientResource($updatedClient);
    }

    /**
     * Remove the specified client.
     */
    public function deleteClient(Client $client): JsonResponse
    {
        $this->clientService->deleteClient($client);

        return response()->json([
            'message' => 'Client deleted successfully.',
        ]);
    }
}
