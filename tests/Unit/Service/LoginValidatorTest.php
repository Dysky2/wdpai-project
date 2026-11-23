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

    public function testPasswordAtTheLimitPasses(): void
    {
        $password = str_repeat('a', LoginValidator::PASSWORD_MAX_LENGTH);

        self::assertSame([], (new LoginValidator())->validate('toto@mercedes.f1', $password));
    }

    public function testTooLongPasswordIsRejected(): void
    {
        $password = str_repeat('a', LoginValidator::PASSWORD_MAX_LENGTH + 1);

        self::assertSame(
            ['password' => 'login.password_too_long'],
            (new LoginValidator())->validate('toto@mercedes.f1', $password),
        );
    }

    public function testTooLongEmailIsRejectedWithoutFormatCheck(): void
    {
        $email = str_repeat('a', LoginValidator::EMAIL_MAX_LENGTH) . '@mercedes.f1';

        self::assertSame(
            ['email' => 'login.email_too_long'],
            (new LoginValidator())->validate($email, 'demo123'),
        );
    }

    public function testBothFieldsReportTheirOwnError(): void
    {
        $errors = (new LoginValidator())->validate(str_repeat('a', 5000), str_repeat('p', 1_000_000));

        self::assertSame(
            ['email' => 'login.email_too_long', 'password' => 'login.password_too_long'],
            $errors,
        );
    }

    #[DataProvider('malformedEmails')]
    public function testMalformedEmailIsRejected(string $email): void
    {
        self::assertSame(['email' => 'login.invalid_email'], (new LoginValidator())->validate($email, 'demo123'));
    }
}
