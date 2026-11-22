<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core;

use App\Service\ValidationMessages;
use App\Tests\Support\FixedLanguage;
use PHPUnit\Framework\TestCase;

final class TranslatorTest extends TestCase
{
    public function testTranslatesInTheActiveLanguage(): void
    {
        self::assertSame('Incydenty', FixedLanguage::translator('pl')->translate('nav.incidents'));
        self::assertSame('Incidents', FixedLanguage::translator('en')->translate('nav.incidents'));
    }

    public function testReplacesPlaceholders(): void
    {
        self::assertSame('maks. 57', FixedLanguage::translator('pl')->translate('report.lap_hint', ['max' => 57]));
        self::assertSame('max. 57', FixedLanguage::translator('en')->translate('report.lap_hint', ['max' => 57]));
    }

    public function testUnknownKeyIsReturnedAsIs(): void
    {
        self::assertSame('no.such.key', FixedLanguage::translator('en')->translate('no.such.key'));
    }

    public function testUnsupportedLanguageFallsBackToPolish(): void
    {
        self::assertSame('Incydenty', FixedLanguage::translator('de')->translate('nav.incidents'));
    }

    public function testValidationMessagesFollowTheLanguage(): void
    {
        $errors = ['lap_number' => ['key' => 'validation.lap_range', 'params' => ['max' => 57]]];

        self::assertSame(
            ['lap_number' => 'The lap number must be between 1 and 57.'],
            (new ValidationMessages(FixedLanguage::translator('en')))->messages($errors),
        );
        self::assertSame(
            ['lap_number' => 'Numer okrążenia musi mieścić się w zakresie 1–57.'],
            (new ValidationMessages(FixedLanguage::translator('pl')))->messages($errors),
        );
    }
}
