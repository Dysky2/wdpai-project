<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Application user: a team principal (bound to a team) or an FIA steward.
 */
final class User
{
    public function __construct(
        private readonly int $id,
        private readonly string $email,
        private readonly string $passwordHash,
        private readonly Role $role,
        private readonly ?int $teamId,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getRole(): Role { return $this->role; }
    public function getTeamId(): ?int { return $this->teamId; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getDisplayName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function isSteward(): bool
    {
        return $this->role === Role::FIA_STEWARD;
    }

    public function isTeamPrincipal(): bool
    {
        return $this->role === Role::TEAM_PRINCIPAL;
    }
}
