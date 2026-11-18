<?php

namespace App\Features\Clients\Resources;

use App\Features\Clients\DTOs\ClientSummaryDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ClientSummaryDTO $resource
 */
class ClientSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'companies_count' => $this->resource->companiesCount,
            'projects_count' => $this->resource->projectsCount,
            'active_edits_count' => $this->resource->activeEditsCount,
            'unpaid_total' => $this->resource->unpaidTotal,
        ];
    }
}
