<?php

declare(strict_types=1);

namespace App\Core;

/** Tells the Translator which interface language is active ("pl" or "en"). */
interface LanguageProviderInterface
{
    public function getLang(): string;
}
