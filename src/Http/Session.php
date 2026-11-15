<?php

declare(strict_types=1);

namespace App\Http;

use App\Core\Config;

/**
 * The only place that touches $_SESSION. Starts lazily with hardened cookie settings.
 * The session stores only user_id, lang and the CSRF token.
 */
final class Session
{
    private const KEY_USER_ID = 'user_id';
    private const KEY_LANG = 'lang';
    private const KEY_CSRF = 'csrf_token';

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
            'secure' => $this->config->isProduction(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }
}
