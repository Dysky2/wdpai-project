<?php

declare(strict_types=1);

namespace App\Http;

use App\Core\Config;
use App\Core\LanguageProviderInterface;

/**
 * The only place that touches $_SESSION. Starts lazily with hardened cookie settings.
 * The session stores only user_id, lang and the CSRF token.
 */
final class Session implements LanguageProviderInterface, CsrfTokenStoreInterface
{
    private const KEY_USER_ID = 'user_id';
    private const KEY_LANG = 'lang';
    private const KEY_CSRF = 'csrf_token';
    private const KEY_FLASH = 'flash';

    public function __construct(private readonly Config $config)
    {
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_set_cookie_params($this->cookieParams());
        session_start();
    }

    public function getUserId(): ?int
    {
        $this->start();
        $id = $_SESSION[self::KEY_USER_ID] ?? null;

        return is_int($id) ? $id : null;
    }

    public function getLang(): string
    {
        $this->start();
        $lang = $_SESSION[self::KEY_LANG] ?? 'pl';

        return $lang === 'en' ? 'en' : 'pl';
    }

    public function setLang(string $lang): void
    {
        $this->start();
        $_SESSION[self::KEY_LANG] = $lang === 'en' ? 'en' : 'pl';
    }

    /** Queues a one-shot message shown on the next rendered page. */
    public function addFlash(string $type, string $message): void
    {
        $this->start();
        $_SESSION[self::KEY_FLASH][] = ['type' => $type, 'message' => $message];
    }

    /**
     * Returns the queued messages and clears them.
     *
     * @return list<array{type: string, message: string}>
     */
    public function pullFlash(): array
    {
        $this->start();
        $messages = $_SESSION[self::KEY_FLASH] ?? [];
        unset($_SESSION[self::KEY_FLASH]);

        return is_array($messages) ? array_values($messages) : [];
    }

    /** Binds the session to a user: new session id and a fresh CSRF token (prevents fixation). */
    public function login(int $userId): void
    {
        $this->start();
        session_regenerate_id(true);
        $_SESSION[self::KEY_USER_ID] = $userId;
        unset($_SESSION[self::KEY_CSRF]);
    }

    public function csrfToken(): string
    {
        $this->start();
        if (!isset($_SESSION[self::KEY_CSRF]) || !is_string($_SESSION[self::KEY_CSRF])) {
            $_SESSION[self::KEY_CSRF] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY_CSRF];
    }

    /** Destroys the session data and expires the session cookie. */
    public function destroy(): void
    {
        $this->start();
        $_SESSION = [];

        $params = $this->cookieParams();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ]);

        session_destroy();
    }

    /** @return array{lifetime: int, path: string, secure: bool, httponly: bool, samesite: string} */
    private function cookieParams(): array
    {
        return [
            'lifetime' => 0,
            'path' => '/',
            'secure' => $this->config->isSessionCookieSecure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }
}
