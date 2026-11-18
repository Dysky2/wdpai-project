<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\LoggerInterface;
use App\Http\Session;

/**
 * Resolves dotted keys (nav.incidents) from config/lang/{pl,en}.php for the language kept in the session.
 * A missing key falls back to Polish, then to the key itself, and is logged; the page never breaks.
 */
final class TranslationService
{
    public const DEFAULT_LANG = 'pl';
    public const SUPPORTED = ['pl', 'en'];

    /** @var array<string, array<string, string>> */
    private array $dictionaries = [];

    public function __construct(
        private readonly Session $session,
        private readonly LoggerInterface $logger,
        private readonly string $langPath,
    ) {
    }

    public function getLang(): string
    {
        $lang = $this->session->getLang();

        return in_array($lang, self::SUPPORTED, true) ? $lang : self::DEFAULT_LANG;
    }

    /** @param array<string, string|int> $params values replace :name placeholders */
    public function t(string $key, array $params = []): string
    {
        $lang = $this->getLang();
        $text = $this->load($lang)[$key] ?? null;

        if ($text === null) {
            $this->logger->error(sprintf('Missing translation key "%s" for language "%s"', $key, $lang));
            $text = $this->load(self::DEFAULT_LANG)[$key] ?? $key;
        }

        $replacements = [];
        foreach ($params as $name => $value) {
            $replacements[':' . $name] = (string) $value;
        }

        return strtr($text, $replacements);
    }

    /** @return array<string, string> */
    private function load(string $lang): array
    {
        if (!isset($this->dictionaries[$lang])) {
            $file = $this->langPath . '/' . $lang . '.php';
            $data = is_file($file) ? require $file : [];
            $this->dictionaries[$lang] = is_array($data) ? $data : [];
        }

        return $this->dictionaries[$lang];
    }
}
