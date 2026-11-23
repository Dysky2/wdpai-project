<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repository\LoginAttemptRepositoryInterface;

final class InMemoryLoginAttemptRepository implements LoginAttemptRepositoryInterface
{
    /** @var list<array{key: string, ip: string, at: \DateTimeImmutable}> */
    private array $rows = [];

    public function record(string $emailKey, string $ip, \DateTimeImmutable $at): void
    {
        $this->rows[] = ['key' => $emailKey, 'ip' => $ip, 'at' => $at];
    }

    public function countSince(string $emailKey, \DateTimeImmutable $since): int
    {
        return count($this->matching($emailKey, $since));
    }

    public function oldestSince(string $emailKey, \DateTimeImmutable $since): ?\DateTimeImmutable
    {
        $times = array_map(static fn (array $row): \DateTimeImmutable => $row['at'], $this->matching($emailKey, $since));
        if ($times === []) {
            return null;
        }
        usort($times, static fn (\DateTimeImmutable $a, \DateTimeImmutable $b): int => $a <=> $b);

        return $times[0];
    }

    public function clear(string $emailKey): void
    {
        $this->rows = array_values(array_filter($this->rows, static fn (array $row): bool => $row['key'] !== $emailKey));
    }

    /** @return list<array{key: string, ip: string, at: \DateTimeImmutable}> */
    private function matching(string $emailKey, \DateTimeImmutable $since): array
    {
        return array_values(array_filter(
            $this->rows,
            static fn (array $row): bool => $row['key'] === $emailKey && $row['at'] >= $since,
        ));
    }
}
