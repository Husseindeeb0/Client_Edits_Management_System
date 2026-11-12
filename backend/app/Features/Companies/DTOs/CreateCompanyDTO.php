<?php

namespace App\Features\Companies\DTOs;

readonly class CreateCompanyDTO
{
    public function __construct(
        public string $name,
        public int $clientId,
    ) {
    }
}

