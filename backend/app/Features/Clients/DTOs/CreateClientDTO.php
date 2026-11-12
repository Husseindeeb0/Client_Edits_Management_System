<?php

namespace App\Features\Clients\DTOs;

readonly class CreateClientDTO
{
    public function __construct(
        public string $name,
        public string $phoneNumber,
        public ?string $email = null,
        public ?string $lastFollowUpDate = null,
    ) {
    }
}

