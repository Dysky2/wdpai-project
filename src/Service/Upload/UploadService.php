<?php

declare(strict_types=1);

namespace App\Service\Upload;

/**
 * Validates and stores an evidence image (JPG/PNG, up to 5 MB).
 * Trusts nothing from the client: the type comes from the file content, the name is random.
 */
final class UploadService
{
    public const MAX_BYTES = 5 * 1024 * 1024;
    /** URL prefix of evidence files; GET /uploads/{name} is served by EvidenceController after an ownership check. */
    public const PUBLIC_PREFIX = '/uploads/';

    /** Shape of a stored file name: 32 hex chars from random_bytes(16) + a whitelisted extension. */
    private const STORED_NAME = '~^[a-f0-9]{32}\.(?:png|jpg)\z~';

    /** Whitelist: real MIME type => extension. */
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    public function __construct(
        private readonly string $uploadDir,
        private readonly UploadedFileHandlerInterface $files = new PhpUploadedFileHandler(),
    ) {
    }

    /**
     * @param array{name?: mixed, tmp_name?: mixed, error?: mixed, size?: mixed} $file one entry of $_FILES
     * @throws InvalidUploadException
     */
    public function store(array $file): StoredEvidence
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        $tmp = $file['tmp_name'] ?? '';

        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new InvalidUploadException('validation.evidence_too_large', ['max' => 5]);
        }
        if ($error !== UPLOAD_ERR_OK || !is_string($tmp) || $tmp === '') {
            throw new InvalidUploadException('validation.evidence_failed');
        }
        if (!$this->files->isUploadedFile($tmp)) {
            throw new InvalidUploadException('validation.evidence_failed');
        }

        $size = filesize($tmp);
        if ($size === false || $size < 1) {
            throw new InvalidUploadException('validation.evidence_failed');
        }
        if ($size > self::MAX_BYTES) {
            throw new InvalidUploadException('validation.evidence_too_large', ['max' => 5]);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if ($mime === false || !isset(self::ALLOWED[$mime])) {
            throw new InvalidUploadException('validation.evidence_type');
        }
        // The file must really decode as an image, and the decoded type must match the finfo verdict.
        $info = @getimagesize($tmp);
        if ($info === false || ($info['mime'] ?? null) !== $mime) {
            throw new InvalidUploadException('validation.evidence_type');
        }

        $sha256 = hash_file('sha256', $tmp);
        if ($sha256 === false) {
            throw new InvalidUploadException('validation.evidence_failed');
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . self::ALLOWED[$mime];
        $target = $this->pathFor($fileName);
        if (!$this->files->moveUploadedFile($tmp, $target)) {
            throw new InvalidUploadException('validation.evidence_failed');
        }

        $original = is_string($file['name'] ?? null) ? basename($file['name']) : 'evidence';

        return new StoredEvidence(
            self::PUBLIC_PREFIX . $fileName,
            mb_substr($original, 0, 255),
            $mime,
            $size,
            $sha256,
        );
    }

    /** Compensation: removes a stored file when saving its metadata (or the case) failed. */
    public function delete(StoredEvidence $evidence): void
    {
        $path = $this->pathFor(basename($evidence->filePath));
        if (is_file($path)) {
            unlink($path);
        }
    }

    /** True only for names this service generates (rejects traversal, other extensions, anything user-made). */
    public function isStoredName(string $fileName): bool
    {
        return preg_match(self::STORED_NAME, $fileName) === 1;
    }

    /** True when a stored file with this name is on disk. */
    public function exists(string $fileName): bool
    {
        return $this->isStoredName($fileName) && is_file($this->pathFor($fileName));
    }

    /**
     * Content type of a stored file, from its extension (which was derived from the finfo MIME at upload).
     *
     * @throws \InvalidArgumentException when the name is not a stored file name
     */
    public function mimeTypeOf(string $fileName): string
    {
        $mime = $this->isStoredName($fileName)
            ? array_search(pathinfo($fileName, PATHINFO_EXTENSION), self::ALLOWED, true)
            : false;

        return is_string($mime) ? $mime : throw new \InvalidArgumentException('Not a stored evidence file name');
    }

    private function pathFor(string $fileName): string
    {
        return rtrim($this->uploadDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;
    }
}
