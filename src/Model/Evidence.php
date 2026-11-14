<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Metadata of an uploaded evidence image; the file itself lives under public/uploads/.
 */
final class Evidence
{
    public function __construct(
        private ?int $id,
        private readonly int $incidentId,
        private readonly string $filePath,
        private readonly string $originalName,
        private readonly string $mimeType,
        private readonly int $sizeBytes,
        private readonly string $sha256,
        private readonly \DateTimeImmutable $uploadedAt,
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getIncidentId(): int { return $this->incidentId; }
    public function getFilePath(): string { return $this->filePath; }
    public function getOriginalName(): string { return $this->originalName; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getSizeBytes(): int { return $this->sizeBytes; }
    public function getSha256(): string { return $this->sha256; }
    public function getUploadedAt(): \DateTimeImmutable { return $this->uploadedAt; }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }
}
