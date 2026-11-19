<?php

declare(strict_types=1);

namespace App\Service;

/** Outcome of one CRON run: how many penalties expired, how many points they carried, who was reinstated. */
final class ExpirationResult
{
    /** @param list<string> $reinstatedDrivers three-letter codes of drivers whose suspension was lifted */
    public function __construct(
        private readonly int $expiredPenalties,
        private readonly int $expiredPoints,
        private readonly array $reinstatedDrivers,
    ) {
    }

    public function getExpiredPenalties(): int
    {
        return $this->expiredPenalties;
    }

    public function getExpiredPoints(): int
    {
        return $this->expiredPoints;
    }

    /** @return list<string> */
    public function getReinstatedDrivers(): array
    {
        return $this->reinstatedDrivers;
    }

    public function describe(): string
    {
        return sprintf(
            'expire-penalties: penalties=%d points=%d reinstated=%d%s',
            $this->expiredPenalties,
            $this->expiredPoints,
            count($this->reinstatedDrivers),
            $this->reinstatedDrivers === [] ? '' : ' (' . implode(',', $this->reinstatedDrivers) . ')',
        );
    }
}
