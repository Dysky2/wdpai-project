<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Missing or invalid CSRF token on a state-changing request (HTTP 403, code "csrf_invalid").
 * Kept apart from ForbiddenException so the client is not told that its role is the problem.
 */
final class CsrfTokenException extends HttpException
{
    public const ERROR_CODE = 'csrf_invalid';

    public function __construct(string $message = 'Invalid or missing CSRF token', ?\Throwable $previous = null)
    {
        parent::__construct(403, self::ERROR_CODE, $message, $previous);
    }

    public function getTranslationPrefix(): string
    {
        return 'error.csrf';
    }
}
