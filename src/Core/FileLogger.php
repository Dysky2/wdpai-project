<?php

declare(strict_types=1);

namespace App\Core;

/** Appends one line per event to a log file (storage/logs/app.log). */
final class FileLogger implements LoggerInterface
{
    public function __construct(private readonly string $file)
    {
    }

    public function error(string $message): void
    {
        $line = sprintf("[%s] ERROR %s\n", gmdate('Y-m-d H:i:s'), str_replace(["\r", "\n"], ' ', $message));
        @file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }
}
