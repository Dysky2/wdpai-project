<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Represents an F1 driver participating in the championship.
 */
final class Driver
{
    public function __construct(
        private ?int    $id,
        private string  $firstName,
        private string  $lastName,
        private int     $number,
        private string  $nationality,
        private int     $teamId,
        private bool    $isSuspended,
    ) {}

    public function getId(): ?int             { return $this->id; }
    public function getFirstName(): string    { return $this->firstName; }
    public function getLastName(): string     { return $this->lastName; }
    public function getFullName(): string     { return $this->firstName . ' ' . $this->lastName; }
    public function getNumber(): int          { return $this->number; }
    public function getNationality(): string  { return $this->nationality; }
    public function getTeamId(): int          { return $this->teamId; }
    public function isSuspended(): bool       { return $this->isSuspended; }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withSuspension(bool $suspended): self
    {
        $clone = clone $this;
        $clone->isSuspended = $suspended;
        return $clone;
    }
}
