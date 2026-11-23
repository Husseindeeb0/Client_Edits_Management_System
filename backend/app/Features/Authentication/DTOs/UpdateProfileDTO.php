<?php

namespace App\Features\Authentication\DTOs;

readonly class UpdateProfileDTO
{
    /**
     * @param array<string, mixed> $data Only the validated fields the admin sent.
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
