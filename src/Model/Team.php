<?php

declare(strict_types=1);

namespace App\Model;

/** Formula 1 team (constructor). */
final class Team
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $licenseCountry,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getLicenseCountry(): string { return $this->licenseCountry; }
}
