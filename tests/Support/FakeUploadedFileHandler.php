<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Service\Upload\UploadedFileHandlerInterface;

/** Treats any existing file as an upload and "moves" it with rename(), so no HTTP request is needed. */
final class FakeUploadedFileHandler implements UploadedFileHandlerInterface
{
    public function isUploadedFile(string $path): bool
    {
        return is_file($path);
    }

    public function moveUploadedFile(string $from, string $to): bool
    {
        return rename($from, $to);
    }
}
