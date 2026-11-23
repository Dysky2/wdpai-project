<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\LoginAuditLogger;
use App\Tests\Support\FakeLogger;
use PHPUnit\Framework\TestCase;

final class LoginAuditLoggerTest extends TestCase
{
    public function testFailureIsLoggedWithEmailIpAndReason(): void
    {
        $logger = new FakeLogger();

        (new LoginAuditLogger($logger))->logFailure('toto@mercedes.f1', '172.18.0.1', 'invalid_credentials');

        self::assertSame(
            ['Failed login: reason=invalid_credentials email="toto@mercedes.f1" ip=172.18.0.1'],
            $logger->infos,
        );
    }

    public function testLogContainsNoPasswordParameter(): void
    {
        $logger = new FakeLogger();

        (new LoginAuditLogger($logger))->logFailure('toto@mercedes.f1', '127.0.0.1', 'invalid_credentials');

        self::assertStringNotContainsStringIgnoringCase('password', $logger->infos[0]);
    }

    public function testNewLinesAndControlCharactersAreStripped(): void
    {
        $logger = new FakeLogger();

        (new LoginAuditLogger($logger))->logFailure("a@b.c\r\n[2026-01-01] INFO Login OK\x00", '127.0.0.1', 'invalid_input');

        self::assertCount(1, $logger->infos);
        self::assertDoesNotMatchRegularExpression('/[\r\n\x00]/', $logger->infos[0]);
    }

    public function testQuotesCannotBreakOutOfTheEmailField(): void
    {
        $logger = new FakeLogger();

        (new LoginAuditLogger($logger))->logFailure('x" ip=1.1.1.1 "', '127.0.0.1', 'invalid_input');

        self::assertSame(1, substr_count($logger->infos[0], '"') / 2);
    }

    public function testVeryLongEmailIsTruncated(): void
    {
        $logger = new FakeLogger();

        (new LoginAuditLogger($logger))->logFailure(str_repeat('a', 5000), '127.0.0.1', 'invalid_input');

        self::assertLessThan(250, strlen($logger->infos[0]));
    }
}
