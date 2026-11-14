<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\StandingRow;

interface StandingRepositoryInterface
{
    public const FILTER_ALL = 'all';
    public const FILTER_RISK = 'risk';
    public const FILTER_CLEAN = 'clean';

    /**
     * Rows of v_super_licence_standings, most points first.
     *
     * @param string $filter FILTER_ALL | FILTER_RISK (>= 7 points) | FILTER_CLEAN (0 points)
     * @param string|null $query matches driver name, code, number or team
     * @return list<StandingRow>
     */
    public function findAll(string $filter = self::FILTER_ALL, ?string $query = null): array;
}
