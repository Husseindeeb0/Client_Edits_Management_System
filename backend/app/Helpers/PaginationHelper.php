<?php

namespace App\Helpers;

class PaginationHelper
{
    /**
     * Resolve and normalize the requested per_page value.
     */
    public static function resolvePerPage(mixed $perPage): int
    {
        $default = (int) config('pagination.per_page', 10);
        $max = (int) config('pagination.max_per_page', 100);

        if (! is_numeric($perPage)) {
            return $default;
        }

        $perPageInt = (int) $perPage;

        if ($perPageInt <= 0) {
            return $default;
        }

        if ($perPageInt > $max) {
            return $max;
        }

        return $perPageInt;
    }
}

