<?php

namespace Tests\Unit;

use App\Helpers\PaginationHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaginationHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['pagination.per_page' => 10, 'pagination.max_per_page' => 100]);
    }

    /**
     * @return array<string, array{mixed, int}>
     */
    public static function perPageCases(): array
    {
        return [
            'null uses default' => [null, 10],
            'empty string uses default' => ['', 10],
            'non numeric uses default' => ['abc', 10],
            'zero uses default' => [0, 10],
            'negative uses default' => [-5, 10],
            'numeric string is used' => ['25', 25],
            'integer is used' => [50, 50],
            'maximum is allowed' => [100, 100],
            'above maximum is capped' => [1000, 100],
        ];
    }

    #[DataProvider('perPageCases')]
    public function test_resolves_per_page(mixed $input, int $expected): void
    {
        $this->assertSame($expected, PaginationHelper::resolvePerPage($input));
    }
}
