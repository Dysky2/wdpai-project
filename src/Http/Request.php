<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly array $queryParams = [],
        private readonly array $parsedBody = [],
        private readonly array $server = [],
        private readonly array $cookies = [],
        private readonly array $files = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return new self(
            method: $method,
            uri: $uri,
            queryParams: $_GET,
            parsedBody: $_POST,
            server: $_SERVER,
            cookies: $_COOKIE,
            files: $_FILES,
        );
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        return is_string($path) ? $path : '/';
    }

    public function getQuery(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->queryParams;
        }

        return $this->queryParams[$key] ?? $default;
    }

    public function getParsedBody(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->parsedBody;
        }

        return $this->parsedBody[$key] ?? $default;
    }

    public function getHeader(string $name, ?string $default = null): ?string
    {
        $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$normalized] ?? $default;
    }

    public function getCookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function getFile(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function getCsrfToken(): ?string
    {
        return $this->getParsedBody('csrf_token') 
            ?? $this->getHeader('X-CSRF-Token');
    }
}
