<?php

declare(strict_types=1);

namespace App\Service\Upload;

final class PhpUploadedFileHandler implements UploadedFileHandlerInterface
{
    public function isUploadedFile(string $path): bool
    {
        return is_uploaded_file($path);
    }

    public function moveUploadedFile(string $from, string $to): bool
    {
        return move_uploaded_file($from, $to);
    }
}
