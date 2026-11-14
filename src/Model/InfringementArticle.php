<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Regulation article with a suggested sanction preset (used by the adjudication form).
 */
final class InfringementArticle
{
    public function __construct(
        private readonly int $id,
        private readonly string $code,
        private readonly string $title,
        private readonly string $reference,
        private readonly PenaltyType $defaultPenaltyType,
        private readonly ?int $defaultSanctionValue,
        private readonly int $defaultPoints,
        private readonly string $precedentWording,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getTitle(): string { return $this->title; }
    public function getReference(): string { return $this->reference; }
    public function getDefaultPenaltyType(): PenaltyType { return $this->defaultPenaltyType; }
    public function getDefaultSanctionValue(): ?int { return $this->defaultSanctionValue; }
    public function getDefaultPoints(): int { return $this->defaultPoints; }
    public function getPrecedentWording(): string { return $this->precedentWording; }
}
