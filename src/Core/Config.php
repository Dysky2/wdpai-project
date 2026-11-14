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
}
