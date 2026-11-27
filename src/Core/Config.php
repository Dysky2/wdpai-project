<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Read-only application settings; built from environment variables, injected by the container.
 */
final class Config
{
    /** @param array<string, string> $values */
    public function __construct(private readonly array $values)
    {
    }

    /** Collects every environment variable (php-fpm exposes the Docker env through getenv()). */
    public static function fromEnvironment(): self
    {
        $env = getenv();

        return new self(is_array($env) ? $env : []);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->values[$key] ?? $default;
    }

    public function require(string $key): string
    {
        return $this->values[$key] ?? throw new \RuntimeException("Missing configuration value '{$key}'.");
    }

    public function isProduction(): bool
    {
        return $this->get('APP_ENV') === 'production';
    }

    /**
     * Whether the session cookie carries the Secure flag (SESSION_SECURE_COOKIE).
     * Unset or empty: follows APP_ENV (on in production). An explicit false/0/off/no is the
     * emergency switch for a plain-HTTP deployment; any unrecognised value fails safe to true.
     */
    public function isSessionCookieSecure(): bool
    {
        $raw = trim($this->get('SESSION_SECURE_COOKIE') ?? '');
        if ($raw === '') {
            return $this->isProduction();
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }
}
