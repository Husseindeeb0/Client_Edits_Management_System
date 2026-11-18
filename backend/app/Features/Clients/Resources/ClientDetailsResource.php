<?php

namespace App\Features\Clients\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array. Related companies, projects and
     * edits are not embedded: the details page loads each one from its own
     * paginated endpoint (one per tab).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'last_follow_up_date' => $this->last_follow_up_date?->format('Y-m-d'),
        ];
    }
}
