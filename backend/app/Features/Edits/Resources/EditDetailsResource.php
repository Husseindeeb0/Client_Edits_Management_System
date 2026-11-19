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
            'id' => $this->edit_id,
            'project_id' => $this->project_id,
            // Breadcrumb: client -> company -> project
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->project_id,
                    'name' => $this->project->name,
                ];
            }),
            'company' => $this->whenLoaded('project', function () {
                return $this->project->relationLoaded('company') ? [
                    'id' => $this->project->company->company_id,
                    'name' => $this->project->company->name,
                ] : null;
            }),
            'client' => $this->whenLoaded('project', function () {
                return $this->project->relationLoaded('company') && $this->project->company->relationLoaded('client') ? [
                    'id' => $this->project->company->client->client_id,
                    'name' => $this->project->company->client->name,
                ] : null;
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

