<?php

namespace Tests\Unit;

use App\Features\Edits\Enums\EditStatus;
use PHPUnit\Framework\TestCase;

class EditStatusTest extends TestCase
{
    public function test_active_statuses_are_pending_confirmed_and_in_progress(): void
    {
        $this->assertSame(
            [EditStatus::Pending, EditStatus::Confirmed, EditStatus::InProgress],
            EditStatus::active(),
        );
    }

    public function test_finished_statuses_are_not_active(): void
    {
        $this->assertNotContains(EditStatus::Done, EditStatus::active());
        $this->assertNotContains(EditStatus::Cancelled, EditStatus::active());
    }

    public function test_values_match_the_stored_strings(): void
    {
        $this->assertSame(
            ['Pending', 'Confirmed', 'In Progress', 'Done', 'Cancelled'],
            array_column(EditStatus::cases(), 'value'),
        );
    }
}
