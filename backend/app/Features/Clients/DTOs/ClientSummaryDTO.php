<?php

namespace App\Features\Clients\DTOs;

readonly class ClientSummaryDTO
{
    public function __construct(
        public int $companiesCount,
        public int $projectsCount,
        public int $activeEditsCount,
        public float $unpaidTotal,
    ) {
    }
}
