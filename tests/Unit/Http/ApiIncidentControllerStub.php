<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Http\Request;
use App\Http\Response;

/** Stands in for ApiIncidentController in SecurityMiddlewareOrderTest; records which action was reached. */
final class ApiIncidentControllerStub
{
    public function investigate(Request $request, int $id): Response
    {
        return new Response('investigate:' . $id, 200);
    }

    public function dismiss(Request $request, int $id): Response
    {
        return new Response('dismiss:' . $id, 200);
    }
}
