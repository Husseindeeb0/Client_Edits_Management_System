<?php

namespace App\Features\Companies\DTOs;

readonly class UpdateCompanyDTO
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
