<?php

declare(strict_types=1);

namespace App\Repository;

final class PdoLoginAttemptRepository extends AbstractPdoRepository implements LoginAttemptRepositoryInterface
{
    private const TIMESTAMP_FORMAT = 'Y-m-d H:i:sP';

    public function record(string $emailKey, string $ip, \DateTimeImmutable $at): void
    {
        $this->execute(
            'INSERT INTO login_attempts (email_key, ip_address, attempted_at) VALUES (:email, :ip, :at)',
            ['email' => $emailKey, 'ip' => $ip, 'at' => $at->format(self::TIMESTAMP_FORMAT)]
        );
    }

    public function countSince(string $emailKey, \DateTimeImmutable $since): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total FROM login_attempts WHERE email_key = :email AND attempted_at >= :since',
            ['email' => $emailKey, 'since' => $since->format(self::TIMESTAMP_FORMAT)]
        );

        return (int) ($row['total'] ?? 0);
    }

    public function oldestSince(string $emailKey, \DateTimeImmutable $since): ?\DateTimeImmutable
    {
        $row = $this->fetchOne(
            'SELECT MIN(attempted_at) AS oldest FROM login_attempts WHERE email_key = :email AND attempted_at >= :since',
            ['email' => $emailKey, 'since' => $since->format(self::TIMESTAMP_FORMAT)]
        );

        return isset($row['oldest']) ? $this->toDate($row['oldest']) : null;
    }

    public function clear(string $emailKey): void
    {
        $this->execute('DELETE FROM login_attempts WHERE email_key = :email', ['email' => $emailKey]);
    }
}
