<?php

declare(strict_types=1);

namespace App\Tests\Unit\View;

use App\Http\Session;
use App\Tests\Support\FixedLanguage;
use App\View\ViewHelper;
use PHPUnit\Framework\TestCase;

final class ViewHelperTest extends TestCase
{
    private function helper(): ViewHelper
    {
        // badgeModifier() and e() do not touch the session, so it is created without its configuration.
        $session = (new \ReflectionClass(Session::class))->newInstanceWithoutConstructor();

        return new ViewHelper($session, FixedLanguage::translator());
    }

    public function testKnownStatusesMapToTheirBadge(): void
    {
        $view = $this->helper();

        self::assertSame('investigation', $view->badgeModifier('UNDER_INVESTIGATION'));
        self::assertSame('penalty', $view->badgeModifier('PENALTY_ISSUED'));
        self::assertSame('suspended', $view->badgeModifier('SUSPENDED'));
    }

    public function testUnknownStatusFallsBackToClosed(): void
    {
        self::assertSame('closed', $this->helper()->badgeModifier('<script>'));
    }

    public function testEscapeEncodesQuotesAndTags(): void
    {
        self::assertSame('&lt;b&gt;&quot;x&quot;&#039;', $this->helper()->e('<b>"x"\''));
    }
}
