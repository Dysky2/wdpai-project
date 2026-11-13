<?php

declare(strict_types=1);

namespace App\Http;

final class JsonResponse extends Response
{
    public function __construct(
        mixed $data = null,
        int $statusCode = 200,
        array $headers = [],
    ) {
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $headers['Content-Type'] = 'application/json; charset=utf-8';

        parent::__construct(
            content: $encoded,
            statusCode: $statusCode,
            headers: $headers,
        );
    }
}
