<?php

declare(strict_types=1);

namespace App\View;

use App\Http\Session;
use App\Service\TranslationService;

/** Helpers available in templates as $view. */
final class ViewHelper
{
    public function __construct(
        private readonly Session $session,
        private readonly TranslationService $translator,
    ) {
    }

    /** @param array<string, string|int> $params */
    public function t(string $key, array $params = []): string
    {
        return $this->translator->t($key, $params);
    }

    public function lang(): string
    {
        return $this->translator->getLang();
    }

    public function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /** @return list<array{type: string, message: string}> queued flash messages (cleared on read) */
    public function pullFlash(): array
    {
        return $this->session->pullFlash();
    }

    public function csrfToken(): string
    {
        return $this->session->csrfToken();
    }

    public function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . $this->e($this->csrfToken()) . '">';
    }
}
