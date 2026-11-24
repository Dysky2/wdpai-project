<?php

declare(strict_types=1);

namespace App\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;

final class TranslationKeysTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../config/lang/';

    protected function setUp(): void
    {
        if (!is_file(self::ROOT . 'pl.php') || !is_file(self::ROOT . 'en.php')) {
            self::markTestSkipped('config/lang/{pl,en}.php do not exist yet (card S5-24b).');
        }
    }

    /** @return list<string> dotted keys of a (possibly nested) dictionary */
    private function keys(string $file): array
    {
        $flatten = static function (array $tree, string $prefix = '') use (&$flatten): array {
            $keys = [];
            foreach ($tree as $key => $value) {
                $path = $prefix . $key;
                if (is_array($value)) {
                    array_push($keys, ...$flatten($value, $path . '.'));
                } else {
                    $keys[] = $path;
                }
            }

            return $keys;
        };

        $keys = $flatten(require self::ROOT . $file);
        sort($keys);

        return $keys;
    }

    public function testPolishAndEnglishDictionariesHaveTheSameKeys(): void
    {
        $pl = $this->keys('pl.php');
        $en = $this->keys('en.php');

        self::assertSame([], array_values(array_diff($pl, $en)), 'keys missing in en.php');
        self::assertSame([], array_values(array_diff($en, $pl)), 'keys missing in pl.php');
    }

    public function testNoTranslationIsEmpty(): void
    {
        foreach (['pl.php', 'en.php'] as $file) {
            $translations = require self::ROOT . $file;
            array_walk_recursive(
                $translations,
                static fn ($value, $key) => self::assertNotSame('', trim((string) $value), $file . ': empty value for ' . $key),
            );
        }
    }
}
