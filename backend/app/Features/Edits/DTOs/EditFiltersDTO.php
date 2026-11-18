<?php

namespace App\Features\Edits\DTOs;

use App\Features\Edits\Enums\EditPriority;
use App\Features\Edits\Enums\EditStatus;

readonly class EditFiltersDTO
{
    public function __construct(
        public ?string $search = null,
        public ?EditStatus $status = null,
        public ?EditPriority $priority = null,
        public ?bool $isPaid = null,
        public ?int $projectId = null,
        public ?int $clientId = null,
        public bool $activeOnly = false,
    ) {
    }

    /**
     * Return a copy of the filters scoped to the given project.
     */
    public function forProject(int $projectId): self
    {
        return new self(
            search: $this->search,
            status: $this->status,
            priority: $this->priority,
            isPaid: $this->isPaid,
            projectId: $projectId,
            clientId: $this->clientId,
            activeOnly: $this->activeOnly,
        );
    }
}
