<?php

declare(strict_types=1);

namespace App\View;

use App\Core\Translator;
use App\Http\Session;

/** Helpers available in templates as $view. */
final class ViewHelper
{
    /** CSS modifier of the status badge for case statuses and licence statuses (anything else: "closed"). */
    private const BADGE_MODIFIERS = [
        'REPORTED' => 'reported',
        'UNDER_INVESTIGATION' => 'investigation',
        'PENALTY_ISSUED' => 'penalty',
        'NO_FURTHER_ACTION' => 'closed',
        'SUSPENDED' => 'suspended',
        'AT_BAN_RISK' => 'penalty',
        'CAUTION' => 'investigation',
        'ACTIVE' => 'closed',
        'CLEAN' => 'reported',
    ];

    public function __construct(
        private readonly Session $session,
        private readonly Translator $translator,
    ) {
    }

    /** @param array<string, scalar> $params */
    public function t(string $key, array $params = []): string
    {
        return $this->translator->translate($key, $params);
    }

    /**
     * Escaped translation for direct output in templates.
     *
     * @param array<string, scalar> $params
     */
    public function te(string $key, array $params = []): string
    {
        return $this->e($this->t($key, $params));
    }

    /** JSON of the "js.*" texts for the page's script-readable <script type="application/json"> block. */
    public function scriptMessagesJson(): string
    {
        return json_encode(
            $this->translator->forPrefix('js.'),
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR,
        );
    }

    public function lang(): string
    {
        return $this->translator->getLang();
    }

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /** Badge modifier class for a status value, e.g. "UNDER_INVESTIGATION" -> "investigation". */
    public function badgeModifier(string $status): string
    {
        return self::BADGE_MODIFIERS[$status] ?? 'closed';
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
