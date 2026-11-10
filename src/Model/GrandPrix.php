<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Represents a Formula 1 Grand Prix weekend.
 */
final class GrandPrix
{
    public function __construct(
        private ?int    $id,
        private string  $name,
        private string  $circuit,
        private string  $country,
        private int     $round,
        private int     $season,
        private \DateTimeImmutable $raceDate,
        private bool    $isActive,
    ) {}

    public function getId(): ?int             { return $this->id; }
    public function getName(): string         { return $this->name; }
    public function getCircuit(): string      { return $this->circuit; }
    public function getCountry(): string      { return $this->country; }
    public function getRound(): int           { return $this->round; }
    public function getSeason(): int          { return $this->season; }
    public function getRaceDate(): \DateTimeImmutable { return $this->raceDate; }
    public function isActive(): bool          { return $this->isActive; }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }
}
