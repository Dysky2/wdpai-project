<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Invalid form input (HTTP 422). Errors are keyed by field name; each holds a translation key
 * and its parameters, never a ready-made text.
 */
final class ValidationException extends HttpException
{
    /** @param array<string, array{key: string, params: array<string, scalar>}> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct(422, 'validation_failed', 'Validation failed');
    }

    /** @return array<string, array{key: string, params: array<string, scalar>}> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
