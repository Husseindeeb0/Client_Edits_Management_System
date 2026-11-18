<?php

namespace App\Features\Projects\DTOs;

readonly class CreateProjectDTO
{
    public function __construct(
        public string $name,
        public int $companyId,
    ) {
    }
}
