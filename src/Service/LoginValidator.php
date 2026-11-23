<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Server-side checks of the login form input, run before the database is touched.
 */
final class LoginValidator
{
    /**
     * @return array<string, string> field => translation key; empty when the input is well-formed
     */
    public function validate(string $email, string $password): array
    {
        $errors = [];

        if (filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'login.invalid_email';
        }

        return $errors;
    }
}
