<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Represents an application user (TEAM_PRINCIPAL or FIA_STEWARD).
 */
final class User
{
    public function __construct(
        private ?int    $id,
        private string  $email,
        private string  $passwordHash,
        private string  $role,          // 'TEAM_PRINCIPAL' | 'FIA_STEWARD'
        private ?int    $teamId,
        private string  $displayName,
        private \DateTimeImmutable $createdAt,
    ) {}

    public function getId(): ?int            { return $this->id; }
    public function getEmail(): string        { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getRole(): string         { return $this->role; }
    public function getTeamId(): ?int         { return $this->teamId; }
    public function getDisplayName(): string  { return $this->displayName; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function isSteward(): bool
    {
        return $this->role === 'FIA_STEWARD';
    }

    public function isTeamPrincipal(): bool
    {
        return $this->role === 'TEAM_PRINCIPAL';
    }
}
