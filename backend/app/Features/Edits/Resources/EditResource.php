<?php

namespace App\Features\Edits\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->edit_id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority?->value,
            'status' => $this->status?->value,
            'price' => $this->price !== null ? (float) $this->price : null,
            'is_paid' => (bool) $this->is_paid,
            'due_date' => $this->due_date?->format('Y-m-d'),
        ];
    }
}

