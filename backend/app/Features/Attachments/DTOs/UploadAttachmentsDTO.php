<?php

namespace App\Features\Attachments\DTOs;

use Illuminate\Http\UploadedFile;

readonly class UploadAttachmentsDTO
{
    /**
     * @param array<int, UploadedFile> $files
     */
    public function __construct(
        public array $files,
        public ?string $description = null,
    ) {
    }
}

