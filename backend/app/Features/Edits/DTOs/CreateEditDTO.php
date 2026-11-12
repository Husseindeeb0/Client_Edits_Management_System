<?php

namespace App\Features\Edits\DTOs;

readonly class CreateEditDTO
{
    public function __construct(
        public int $projectId,
        public string $title,
        public ?string $description,
        public string $priority,
        public string $status,
        public ?float $price = null,
        public bool $isPaid = false,
        public ?string $dueDate = null,
    ) {
    }
}

