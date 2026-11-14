<?php

declare(strict_types=1);

namespace App\Exception;

final class ForbiddenException extends HttpException
{
    public function __construct(string $message = 'Access denied', ?\Throwable $previous = null)
    {
        parent::__construct(403, 'forbidden', $message, $previous);
    }
}
