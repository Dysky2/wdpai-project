<?php

declare(strict_types=1);

namespace App\Core;

interface LoggerInterface
{
    public function error(string $message): void;
}
