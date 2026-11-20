<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Core\LoggerInterface;

final class FakeLogger implements LoggerInterface
{
    /** @var list<string> */
    public array $errors = [];

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }
}
