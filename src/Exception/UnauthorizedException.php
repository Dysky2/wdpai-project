<?php

declare(strict_types=1);

namespace App\Exception;

final class UnauthorizedException extends HttpException
{
    public function __construct(string $message = 'Authentication required', ?\Throwable $previous = null)
    {
        parent::__construct(401, 'unauthorized', $message, $previous);
    }
}
