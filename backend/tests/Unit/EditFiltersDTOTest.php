<?php

namespace Tests\Unit;

use App\Features\Edits\DTOs\EditFiltersDTO;
use App\Features\Edits\Enums\EditPriority;
use App\Features\Edits\Enums\EditStatus;
use PHPUnit\Framework\TestCase;

class EditFiltersDTOTest extends TestCase
{
    public function test_for_project_replaces_project_and_keeps_other_filters(): void
    {
        $filters = new EditFiltersDTO(
            search: 'logo',
            status: EditStatus::Done,
            priority: EditPriority::High,
            isPaid: false,
            projectId: 99,
            clientId: 3,
            activeOnly: true,
        );

        $scoped = $filters->forProject(7);

        $this->assertSame(7, $scoped->projectId);
        $this->assertSame('logo', $scoped->search);
        $this->assertSame(EditStatus::Done, $scoped->status);
        $this->assertSame(EditPriority::High, $scoped->priority);
        $this->assertFalse($scoped->isPaid);
        $this->assertSame(3, $scoped->clientId);
        $this->assertTrue($scoped->activeOnly);
        $this->assertSame(99, $filters->projectId, 'The original filters must not change.');
    }
}
