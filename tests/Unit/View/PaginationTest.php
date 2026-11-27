<?php

declare(strict_types=1);

namespace App\Tests\Unit\View;

use App\View\Pagination;
use PHPUnit\Framework\TestCase;

final class PaginationTest extends TestCase
{
    public function testMiddlePageHasPreviousNextAndCurrentLink(): void
    {
        $pager = new Pagination('/incidents', ['status' => 'all', 'q' => 'a b', 'gp' => 'all'], 2, 3);

        self::assertTrue($pager->hasPages());
        self::assertSame('/incidents?status=all&q=a+b&gp=all&page=1', $pager->previousUrl());
        self::assertSame('/incidents?status=all&q=a+b&gp=all&page=3', $pager->nextUrl());
        self::assertSame([1, 2, 3], array_column($pager->links(), 'number'));
        self::assertSame([false, true, false], array_column($pager->links(), 'current'));
    }

    public function testFirstAndLastPageHaveNoLinkBeyondTheEdge(): void
    {
        self::assertNull((new Pagination('/standings', [], 1, 2))->previousUrl());
        self::assertNull((new Pagination('/standings', [], 2, 2))->nextUrl());
    }

    public function testSinglePageIsNotRendered(): void
    {
        $pager = new Pagination('/standings', ['filter' => 'all'], 1, 1);

        self::assertFalse($pager->hasPages());
        self::assertNull($pager->previousUrl());
        self::assertNull($pager->nextUrl());
    }

    public function testPageParameterFromTheQueryIsReplaced(): void
    {
        $pager = new Pagination('/standings', ['filter' => 'risk'], 1, 2);

        self::assertSame('/standings?filter=risk&page=2', $pager->nextUrl());
    }
}
