<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    /** @return list<User> team principals of the team (their e-mails receive official rulings) */
    public function findTeamPrincipals(int $teamId): array;

    /** Stores a new hash (e.g. after password_needs_rehash). */
    public function updatePasswordHash(int $id, string $passwordHash): void;
}
