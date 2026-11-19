<?php

namespace App\Features\Projects\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array. The company and client are
     * returned as id + name only, for the page breadcrumb.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->project_id,
            'name' => $this->name,
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', fn () => [
                'id' => $this->company->company_id,
                'name' => $this->company->name,
            ]),
            'client' => $this->whenLoaded('company', fn () => [
                'id' => $this->company->client->client_id,
                'name' => $this->company->client->name,
            ]),
        ];
    }
}
