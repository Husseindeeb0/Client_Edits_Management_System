<?php

namespace App\Features\Companies\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->company_id,
            'name' => $this->name,
            'client_id' => $this->client_id,
            'client_name' => $this->client?->name,
        ];
    }
}
