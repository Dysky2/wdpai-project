<?php

declare(strict_types=1);

namespace App\Exception;

final class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Resource not found', ?\Throwable $previous = null)
    {
        parent::__construct(404, 'not_found', $message, $previous);
    }
}
