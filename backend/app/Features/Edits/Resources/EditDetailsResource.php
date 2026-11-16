<?php

namespace App\Features\Edits\Resources;

use App\Features\Attachments\Resources\AttachmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                ];
            }),
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority?->value,
            'status' => $this->status?->value,
            'price' => $this->price !== null ? (float) $this->price : null,
            'is_paid' => (bool) $this->is_paid,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}

