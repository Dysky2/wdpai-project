<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core;

use App\Core\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testSecureCookieDefaultsToOnInProduction(): void
    {
        self::assertTrue((new Config(['APP_ENV' => 'production']))->isSessionCookieSecure());
    }

    public function testSecureCookieDefaultsToOffOutsideProduction(): void
    {
        self::assertFalse((new Config(['APP_ENV' => 'local']))->isSessionCookieSecure());
        self::assertFalse((new Config([]))->isSessionCookieSecure());
    }

    public function testEmptyValueFollowsAppEnv(): void
    {
        self::assertTrue((new Config(['APP_ENV' => 'production', 'SESSION_SECURE_COOKIE' => ' ']))->isSessionCookieSecure());
    }

    public function testExplicitFalseIsTheEmergencySwitchForHttp(): void
    {
        $config = new Config(['APP_ENV' => 'production', 'SESSION_SECURE_COOKIE' => 'false']);

        self::assertFalse($config->isSessionCookieSecure());
    }

    public function testExplicitTrueForcesSecureOutsideProduction(): void
    {
        self::assertTrue((new Config(['APP_ENV' => 'local', 'SESSION_SECURE_COOKIE' => 'true']))->isSessionCookieSecure());
    }

    #[DataProvider('invalidValues')]
    public function testUnrecognisedValueFailsSafeToSecure(string $value): void
    {
        $config = new Config(['APP_ENV' => 'local', 'SESSION_SECURE_COOKIE' => $value]);

        self::assertTrue($config->isSessionCookieSecure());
    }

    /** @return iterable<string, array{string}> */
    public static function invalidValues(): iterable
    {
        yield 'typo' => ['flase'];
        yield 'number' => ['2'];
        yield 'word' => ['maybe'];
    }
}
