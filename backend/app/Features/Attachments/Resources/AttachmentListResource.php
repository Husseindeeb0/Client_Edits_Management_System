<?php

namespace App\Features\Attachments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentListResource extends JsonResource
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
            'edit_id' => $this->edit_id,
            'edit_title' => $this->edit?->title,
            'original_file_name' => $this->original_file_name,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
