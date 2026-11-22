<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Looks up UI texts by dotted key (e.g. "nav.incidents") in config/lang/{pl,en}.php.
 * The active language comes from a LanguageProviderInterface (the session in production); a key missing in the active language falls back
 * to Polish and finally to the key itself, so a gap is visible but never fatal.
 */
final class Translator
{
    public const DEFAULT_LANG = 'pl';
    public const SUPPORTED = ['pl', 'en'];

    /** @var array<string, array<string, string>> */
    private array $dictionaries = [];

    public function __construct(
        private readonly LanguageProviderInterface $language,
        private readonly string $langDir,
    ) {
    }

    public function getLang(): string
    {
        return $this->language->getLang();
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

    /**
     * Every entry whose key starts with $prefix, in the active language (Polish fills any gap).
     * Used to hand the texts needed by scripts to the browser.
     *
     * @return array<string, string>
     */
    public function forPrefix(string $prefix): array
    {
        $matching = static fn (array $dictionary): array => array_filter(
            $dictionary,
            static fn (string $key): bool => str_starts_with($key, $prefix),
            ARRAY_FILTER_USE_KEY,
        );

        return $matching($this->dictionary($this->getLang())) + $matching($this->dictionary(self::DEFAULT_LANG));
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
