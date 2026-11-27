<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\AbstractController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Shared form-input helper used by IncidentController and StewardController. */
final class AbstractControllerTest extends TestCase
{
    /** @return array<string, array{string, int}> */
    public static function values(): array
    {
        return [
            'positive number' => ['42', 42],
            'leading zero' => ['007', 7],
            'nine digits' => ['999999999', 999999999],
            'empty' => ['', 0],
            'zero' => ['0', 0],
            'negative' => ['-3', 0],
            'decimal' => ['1.5', 0],
            'text' => ['abc', 0],
            'space' => [' 1', 0],
            'too long (int overflow guard)' => ['9999999999', 0],
        ];
    }

    #[DataProvider('values')]
    public function testPositiveIntAcceptsOnlyPositiveDigits(string $input, int $expected): void
    {
        $controller = (new \ReflectionClass(AbstractControllerProbe::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(AbstractController::class, 'positiveInt');

        self::assertSame($expected, $method->invoke($controller, $input));
    }
}
