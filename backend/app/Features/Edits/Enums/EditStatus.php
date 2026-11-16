<?php

namespace App\Features\Edits\Enums;

enum EditStatus: string
{
    case Pending = 'Pending';
    case Confirmed = 'Confirmed';
    case InProgress = 'In Progress';
    case Done = 'Done';
    case Cancelled = 'Cancelled';

    /**
     * Statuses that mean work on the edit is still open. Clients and companies
     * owning an edit in one of these statuses cannot be deleted.
     *
     * @return list<self>
     */
    public static function active(): array
    {
        return [self::Pending, self::Confirmed, self::InProgress];
    }
}
