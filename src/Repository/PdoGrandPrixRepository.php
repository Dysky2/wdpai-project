<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\GrandPrix;

final class PdoGrandPrixRepository extends AbstractPdoRepository implements GrandPrixRepositoryInterface
{
    private const COLUMNS = 'id, name, circuit_name, country, season_year, round_number, race_date, total_laps, is_active';

    public function findById(int $id): ?GrandPrix
    {
        $row = $this->fetchOne('SELECT ' . self::COLUMNS . ' FROM grands_prix WHERE id = :id', ['id' => $id]);

        return $row === null ? null : $this->hydrate($row);
    }

    public function findAll(): array
    {
        return array_map(
            fn (array $row): GrandPrix => $this->hydrate($row),
            $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM grands_prix ORDER BY season_year, round_number')
        );
    }

    public function findActive(): ?GrandPrix
    {
        $row = $this->fetchOne('SELECT ' . self::COLUMNS . ' FROM grands_prix WHERE is_active LIMIT 1');

        return $row === null ? null : $this->hydrate($row);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): GrandPrix
    {
        return new GrandPrix(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['circuit_name'],
            (string) $row['country'],
            (int) $row['season_year'],
            (int) $row['round_number'],
            $this->toDate($row['race_date']),
            (int) $row['total_laps'],
            (bool) $row['is_active'],
        );
    }
}
