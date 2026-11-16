<?php

declare(strict_types=1);

namespace App\Service\Upload;

/**
 * Seam over the PHP functions that only work for real HTTP uploads, so UploadService can be unit-tested.
 */
interface UploadedFileHandlerInterface
{
    public function isUploadedFile(string $path): bool;

    public function moveUploadedFile(string $from, string $to): bool;
}
