<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Core\LanguageProviderInterface;
use App\Core\Translator;

/** Fixed interface language for tests; translator() reads the real dictionaries from config/lang. */
final class FixedLanguage implements LanguageProviderInterface
{
    public function __construct(private readonly string $lang = 'pl')
    {
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public static function translator(string $lang = 'pl'): Translator
    {
        return new Translator(new self($lang), __DIR__ . '/../../config/lang');
    }
}
