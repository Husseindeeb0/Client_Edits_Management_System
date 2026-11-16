<?php

namespace App\Helpers;

class PaginationHelper
{
    /**
     * Validation rules for the per_page query parameter, shared by every
     * paginated list request.
     *
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }

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

