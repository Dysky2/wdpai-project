<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Http\Response;

/** Controller stub for RouterTest. */
final class RouterTestController
{
    public function show(int $id): Response
    {
        return new Response('int:' . $id, 200);
    }

    public function boom(): Response
    {
        throw new \RuntimeException('secret-detail');
    }
}
