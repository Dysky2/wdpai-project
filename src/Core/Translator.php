<?php

declare(strict_types=1);

namespace App\Core;

use App\Http\Session;

/**
 * Looks up UI texts by dotted key (e.g. "nav.incidents") in config/lang/{pl,en}.php.
 * The active language comes from the session; a key missing in the active language falls back
 * to Polish and finally to the key itself, so a gap is visible but never fatal.
 */
final class Translator
{
    public const DEFAULT_LANG = 'pl';
    public const SUPPORTED = ['pl', 'en'];

    /** @var array<string, array<string, string>> */
    private array $dictionaries = [];

    public function __construct(
        private readonly Session $session,
        private readonly string $langDir,
    ) {
    }

    public function getLang(): string
    {
        return $this->session->getLang();
    }

    /** @param array<string, scalar> $params values replace ":name" placeholders */
    public function translate(string $key, array $params = []): string
    {
        $text = $this->dictionary($this->getLang())[$key]
            ?? $this->dictionary(self::DEFAULT_LANG)[$key]
            ?? $key;

        foreach ($params as $name => $value) {
            $text = str_replace(':' . $name, (string) $value, $text);
        }

        return $text;
    }

    /** @return array<string, string> */
    private function dictionary(string $lang): array
    {
        if (!isset($this->dictionaries[$lang])) {
            $file = $this->langDir . '/' . $lang . '.php';
            $this->dictionaries[$lang] = is_file($file) ? (require $file) : [];
        }

        return $this->dictionaries[$lang];
    }
}
