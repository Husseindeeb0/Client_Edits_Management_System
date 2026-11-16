<?php

namespace App\Features\Edits\DTOs;

use App\Features\Edits\Enums\EditPriority;
use App\Features\Edits\Enums\EditStatus;

readonly class CreateEditDTO
{
    public function __construct(
        public int $projectId,
        public string $title,
        public ?string $description,
        public EditPriority $priority,
        public EditStatus $status,
        public ?float $price = null,
        public bool $isPaid = false,
        public ?string $dueDate = null,
    ) {
    }
}

