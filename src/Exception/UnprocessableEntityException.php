<?php

declare(strict_types=1);

namespace App\Exception;

final class UnprocessableEntityException extends HttpException
{
    public function __construct(string $message = 'Unprocessable entity', ?\Throwable $previous = null)
    {
        parent::__construct(422, 'unprocessable_entity', $message, $previous);
    }
}
