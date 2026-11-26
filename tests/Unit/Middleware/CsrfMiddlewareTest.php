<?php

declare(strict_types=1);

namespace App\Tests\Unit\Middleware;

use App\Exception\CsrfTokenException;
use App\Http\Request;
use App\Http\Response;
use App\Middleware\CsrfMiddleware;
use App\Tests\Support\FixedCsrfTokenStore;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CsrfMiddlewareTest extends TestCase
{
    private function handle(Request $request): Response
    {
        return (new CsrfMiddleware(new FixedCsrfTokenStore()))
            ->handle($request, static fn (Request $r): Response => new Response('passed', 200));
    }

    public function testValidHeaderTokenPasses(): void
    {
        $request = new Request('POST', '/api/incidents/1/investigate', server: ['HTTP_X_CSRF_TOKEN' => FixedCsrfTokenStore::TOKEN]);

        self::assertSame('passed', $this->handle($request)->getContent());
    }

    public function testValidFormFieldPasses(): void
    {
        $request = new Request('POST', '/login', parsedBody: ['csrf_token' => FixedCsrfTokenStore::TOKEN]);

        self::assertSame('passed', $this->handle($request)->getContent());
    }

    public function testSafeMethodNeedsNoToken(): void
    {
        self::assertSame('passed', $this->handle(new Request('GET', '/incidents'))->getContent());
    }

    /** @return array<string, array{Request}> */
    public static function badTokens(): array
    {
        return [
            'missing' => [new Request('POST', '/logout')],
            'empty header' => [new Request('POST', '/logout', server: ['HTTP_X_CSRF_TOKEN' => ''])],
            'wrong header' => [new Request('POST', '/logout', server: ['HTTP_X_CSRF_TOKEN' => 'forged'])],
            'wrong field' => [new Request('POST', '/logout', parsedBody: ['csrf_token' => 'forged'])],
            'array field' => [new Request('POST', '/logout', parsedBody: ['csrf_token' => ['x']])],
        ];
    }

    #[DataProvider('badTokens')]
    public function testMissingOrWrongTokenIsRejectedAsCsrfError(Request $request): void
    {
        try {
            $this->handle($request);
            self::fail('CsrfTokenException expected');
        } catch (CsrfTokenException $e) {
            self::assertSame(403, $e->getStatusCode());
            self::assertSame('csrf_invalid', $e->getErrorCode());
            self::assertSame('error.csrf', $e->getTranslationPrefix());
        }
    }
}
