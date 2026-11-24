<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Role;
use App\Model\User;

final class PdoUserRepository extends AbstractPdoRepository implements UserRepositoryInterface
{
    private const COLUMNS = 'id, email, password_hash, role, team_id, first_name, last_name, created_at';

    public function findById(int $id): ?User
    {
        $row = $this->fetchOne('SELECT ' . self::COLUMNS . ' FROM users WHERE id = :id', ['id' => $id]);

        return $row === null ? null : $this->hydrate($row);
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE lower(email) = lower(:email)',
            ['email' => $email]
        );

        return $row === null ? null : $this->hydrate($row);
    }

    public function findTeamPrincipals(int $teamId): array
    {
        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE team_id = :team AND role = :role ORDER BY id',
            ['team' => $teamId, 'role' => Role::TEAM_PRINCIPAL->value]
        );

        return array_map(fn (array $row): User => $this->hydrate($row), $rows);
    }

    public function updatePasswordHash(int $id, string $passwordHash): void
    {
        $this->execute(
            'UPDATE users SET password_hash = :hash WHERE id = :id',
            ['hash' => $passwordHash, 'id' => $id]
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): User
    {
        return new User(
            (int) $row['id'],
            (string) $row['email'],
            (string) $row['password_hash'],
            Role::from((string) $row['role']),
            $this->toNullableInt($row['team_id']),
            (string) $row['first_name'],
            (string) $row['last_name'],
            $this->toDate($row['created_at']),
        );
    }
}
