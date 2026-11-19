<?php

declare(strict_types=1);

namespace App\Core;

interface LoggerInterface
{
    public function info(string $message): void;

    public function error(string $message): void;
}
