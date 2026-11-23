<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\LoginValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LoginValidatorTest extends TestCase
{
    public function testWellFormedEmailPasses(): void
    {
        self::assertSame([], (new LoginValidator())->validate('toto@mercedes.f1', 'demo123'));
    }

    public function testSurroundingWhitespaceIsIgnored(): void
    {
        self::assertSame([], (new LoginValidator())->validate('  toto@mercedes.f1 ', 'demo123'));
    }

    /** @return array<string, array{string}> */
    public static function malformedEmails(): array
    {
        return [
            'no at sign' => ['not-an-email'],
            'empty' => [''],
            'missing domain' => ['toto@'],
            'missing local part' => ['@mercedes.f1'],
            'spaces inside' => ['to to@mercedes.f1'],
            'sql payload' => ["x'; DROP TABLE users; --"],
        ];
    }

    #[DataProvider('malformedEmails')]
    public function testMalformedEmailIsRejected(string $email): void
    {
        self::assertSame(['email' => 'login.invalid_email'], (new LoginValidator())->validate($email, 'demo123'));
    }
}
