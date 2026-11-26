<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Controller\ApiIncidentController;
use App\Core\Container;
use App\Core\Translator;
use App\Http\CsrfTokenStoreInterface;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RoleMiddleware;
use App\Model\User;
use App\Service\CurrentUserProviderInterface;
use App\Tests\Support\FakeCurrentUserProvider;
use App\Tests\Support\FixedCsrfTokenStore;
use App\Tests\Support\FixedLanguage;
use App\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Dispatches through the real config/routes.php: session (401) -> CSRF (403 csrf_invalid) -> role (403 forbidden).
 * The API controller is replaced by a stub, so no database is involved.
 */
final class SecurityMiddlewareOrderTest extends TestCase
{
    private FakeCurrentUserProvider $users;
    private Router $router;

    protected function setUp(): void
    {
        $this->users = new FakeCurrentUserProvider();

        $container = new Container();
        $container->set(CurrentUserProviderInterface::class, $this->users);
        $container->set(CsrfTokenStoreInterface::class, new FixedCsrfTokenStore());
        $container->set(Translator::class, FixedLanguage::translator('pl'));
        $container->set(ApiIncidentController::class, new ApiIncidentControllerStub());

        $this->router = new Router($container);
        (require __DIR__ . '/../../../config/routes.php')($this->router, $container);
    }

    private function post(string $path, ?User $user, ?string $token): Response
    {
        $this->users->logIn($user);
        $server = $token === null ? [] : ['HTTP_X_CSRF_TOKEN' => $token];

        return $this->router->dispatch(new Request('POST', $path, server: $server));
    }

    /** @return array{code: string, message: string} */
    private function error(Response $response): array
    {
        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR)['error'];
    }

    /** @return array<string, array{string}> */
    public static function actions(): array
    {
        return ['investigate' => ['investigate'], 'dismiss' => ['dismiss']];
    }

    #[DataProvider('actions')]
    public function testGuestGetsUnauthorizedEvenWithoutToken(string $action): void
    {
        $response = $this->post('/api/incidents/1/' . $action, null, null);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('unauthorized', $this->error($response)['code']);
    }

    #[DataProvider('actions')]
    public function testStewardWithoutTokenGetsCsrfError(string $action): void
    {
        $response = $this->post('/api/incidents/1/' . $action, Fixtures::steward(), null);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame('csrf_invalid', $this->error($response)['code']);
        self::assertSame(
            FixedLanguage::translator('pl')->translate('error.csrf.message'),
            $this->error($response)['message'],
        );
    }

    #[DataProvider('actions')]
    public function testStewardWithWrongTokenGetsCsrfError(string $action): void
    {
        $response = $this->post('/api/incidents/1/' . $action, Fixtures::steward(), 'forged-token');

        self::assertSame(403, $response->getStatusCode());
        self::assertSame('csrf_invalid', $this->error($response)['code']);
    }

    public function testPrincipalWithWrongTokenGetsCsrfErrorBeforeRoleCheck(): void
    {
        $response = $this->post('/api/incidents/1/investigate', Fixtures::principal(), 'forged-token');

        self::assertSame(403, $response->getStatusCode());
        self::assertSame('csrf_invalid', $this->error($response)['code']);
    }

    #[DataProvider('actions')]
    public function testPrincipalWithValidTokenGetsRoleError(string $action): void
    {
        $response = $this->post('/api/incidents/1/' . $action, Fixtures::principal(), FixedCsrfTokenStore::TOKEN);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame('forbidden', $this->error($response)['code']);
        self::assertSame(
            FixedLanguage::translator('pl')->translate('error.403.message'),
            $this->error($response)['message'],
        );
    }

    #[DataProvider('actions')]
    public function testStewardWithValidTokenReachesController(string $action): void
    {
        $response = $this->post('/api/incidents/7/' . $action, Fixtures::steward(), FixedCsrfTokenStore::TOKEN);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame($action . ':7', $response->getContent());
    }

    public function testGuestPostingAHtmlFormIsRedirectedToLoginBeforeCsrfCheck(): void
    {
        $response = $this->post('/incidents', null, null);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getHeaders()['Location'] ?? null);
    }

    public function testEveryPostRouteChecksCsrfAfterSessionAndBeforeRole(): void
    {
        $posts = array_filter($this->router->getRoutes(), static fn (array $route): bool => $route['method'] === 'POST');
        self::assertNotEmpty($posts);

        foreach ($posts as $route) {
            $chain = array_map(
                static fn (string|object $m): string => is_string($m) ? $m : $m::class,
                $route['middlewares'],
            );
            $csrf = array_search(CsrfMiddleware::class, $chain, true);
            self::assertNotFalse($csrf, 'POST ' . $route['path'] . ' has no CsrfMiddleware');

            $auth = array_search(AuthMiddleware::class, $chain, true);
            if ($auth !== false) {
                self::assertLessThan($csrf, $auth, 'POST ' . $route['path'] . ': session must be checked before CSRF');
            }

            $role = array_search(RoleMiddleware::class, $chain, true);
            if ($role !== false) {
                self::assertGreaterThan($csrf, $role, 'POST ' . $route['path'] . ': CSRF must be checked before the role');
            }
        }
    }
}
