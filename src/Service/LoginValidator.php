<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Server-side checks of the login form input, run before the database is touched.
 */
final class LoginValidator
{
    public const EMAIL_MAX_LENGTH = 254;
    public const PASSWORD_MAX_LENGTH = 128;

    /**
     * @return array<string, string> field => translation key; empty when the input is well-formed
     */
    public function validate(string $email, string $password): array
    {
        $errors = [];

        if (mb_strlen($email) > self::EMAIL_MAX_LENGTH) {
            $errors['email'] = 'login.email_too_long';
        } elseif (filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'login.invalid_email';
        }

        if (mb_strlen($password) > self::PASSWORD_MAX_LENGTH) {
            $errors['password'] = 'login.password_too_long';
        }

        return $errors;
    }
}
