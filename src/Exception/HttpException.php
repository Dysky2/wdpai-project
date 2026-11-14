<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Base class for errors that map directly to an HTTP status code.
 * The Router turns any HttpException into an HTML error page or a JSON error body.
 */
class HttpException extends \RuntimeException
{
    public function __construct(
        private readonly int $statusCode,
        private readonly string $errorCode,
        string $message = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
