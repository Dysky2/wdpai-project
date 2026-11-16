<?php

declare(strict_types=1);

namespace App\Service\Upload;

use App\Exception\HttpException;

/**
 * Rejected evidence file. Carries a translation key (and parameters) instead of a user-facing text,
 * so the caller can attach it to the `evidence` form field.
 */
final class InvalidUploadException extends HttpException
{
    /** @param array<string, scalar> $params */
    public function __construct(private readonly string $errorKey, private readonly array $params = [])
    {
        parent::__construct(422, 'unprocessable_entity', $errorKey);
    }

    public function getErrorKey(): string
    {
        return $this->errorKey;
    }

    /** @return array<string, scalar> */
    public function getParams(): array
    {
        return $this->params;
    }
}
