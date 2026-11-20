<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Http\JsonResponse;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** /api/* paths are used throughout, so errors come back as JSON and no view rendering is involved. */
final class RouterTest extends TestCase
{
    private function router(): Router
    {
        return (new Router())->get('/api/incidents/{id}', RouterTestController::class, 'show');
    }

    private function dispatch(string $method, string $path): Response
    {
        return $this->router()->dispatch(new Request($method, $path));
    }

    /** @return array<string, array{string}> */
    public static function invalidIds(): array
    {
        return ['letters' => ['abc'], 'zero' => ['0'], 'negative' => ['-1'], 'decimal' => ['1.5'], 'plus sign' => ['+3']];
    }

    #[DataProvider('invalidIds')]
    public function testInvalidIdGivesBadRequest(string $id): void
    {
        $response = $this->dispatch('GET', '/api/incidents/' . $id);

        self::assertSame(400, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertStringContainsString('"code"', $response->getContent());
    }

    public function testValidIdIsPassedToTheControllerAsInteger(): void
    {
        $response = $this->dispatch('GET', '/api/incidents/42');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('int:42', $response->getContent());
    }

    public function testUnknownRouteGivesNotFound(): void
    {
        $response = $this->dispatch('GET', '/api/nothing-here');

        self::assertSame(404, $response->getStatusCode());
    }

    public function testKnownPathWithWrongMethodGivesNotFound(): void
    {
        $response = $this->dispatch('DELETE', '/api/incidents/42');

        self::assertSame(404, $response->getStatusCode());
    }

    public function testControllerExceptionBecomesServerErrorWithoutLeakingDetails(): void
    {
        $router = (new Router())->get('/api/boom', RouterTestController::class, 'boom');
        $previous = ini_set('error_log', sys_get_temp_dir() . '/apex_router_test.log');

        try {
            $response = $router->dispatch(new Request('GET', '/api/boom'));
        } finally {
            ini_set('error_log', (string) $previous);
        }

        self::assertSame(500, $response->getStatusCode());
        self::assertStringNotContainsString('secret-detail', $response->getContent());
    }
}
