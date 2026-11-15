<?php

declare(strict_types=1);

namespace App\View;

use App\Http\Session;

/** Helpers available in templates as $view. */
final class ViewHelper
{
    public function __construct(private readonly Session $session)
    {
    }

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
