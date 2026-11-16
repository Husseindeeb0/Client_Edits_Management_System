<?php

namespace App\Features\Clients\DTOs;

readonly class UpdateClientDTO
{
    /**
     * @param array<string, mixed> $data Only the validated fields the client sent.
     */
    public function __construct(
        public array $data,
    ) {
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

