<?php

namespace App\Features\Authentication\DTOs;

readonly class ChangePasswordDTO
{
    public function __construct(
        public string $newPassword,
    ) {
    }
}
