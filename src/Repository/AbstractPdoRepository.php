<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

/**
 * Shared plumbing for PDO repositories: every query goes through Database::run() (prepared statements).
 */
abstract class AbstractPdoRepository
{
    public function __construct(protected readonly Database $db)
    {
    }

    /**
     * @param array<string, scalar|null>|list<scalar|null> $params
     * @return array<string, mixed>|null
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->db->run($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @param array<string, scalar|null>|list<scalar|null> $params
     * @return list<array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->db->run($sql, $params)->fetchAll();
    }

    /** @param array<string, scalar|null>|list<scalar|null> $params */
    protected function execute(string $sql, array $params = []): int
    {
        return $this->db->run($sql, $params)->rowCount();
    }

    /**
     * Runs an INSERT ... RETURNING id and returns the generated key.
     *
     * @param array<string, scalar|null>|list<scalar|null> $params
     */
    protected function insertReturningId(string $sql, array $params): int
    {
        $row = $this->fetchOne($sql, $params);

        return (int) ($row['id'] ?? throw new \RuntimeException('INSERT did not return an id.'));
    }

    protected function toDate(mixed $value): \DateTimeImmutable
    {
        return new \DateTimeImmutable((string) $value);
    }

    protected function toNullableDate(mixed $value): ?\DateTimeImmutable
    {
        return $value === null ? null : $this->toDate($value);
    }

    protected function toNullableInt(mixed $value): ?int
    {
        return $value === null ? null : (int) $value;
    }
}
