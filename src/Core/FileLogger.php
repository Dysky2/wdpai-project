<?php

declare(strict_types=1);

namespace App\Core;

/** Appends one UTC-stamped line per event to a log file (storage/logs/app.log, storage/logs/cron.log). */
final class FileLogger implements LoggerInterface
{
    public function __construct(private readonly string $file)
    {
    }

    public function info(string $message): void
    {
        $this->write('INFO', $message);
    }

    public function error(string $message): void
    {
        $this->write('ERROR', $message);
    }

    private function write(string $level, string $message): void
    {
        $line = sprintf("[%s] %s %s\n", gmdate('Y-m-d H:i:s'), $level, str_replace(["\r", "\n"], ' ', $message));
        @file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }
}
