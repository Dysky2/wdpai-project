<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\InfringementArticle;
use App\Model\PenaltyType;

final class PdoInfringementArticleRepository extends AbstractPdoRepository implements InfringementArticleRepositoryInterface
{
    private const COLUMNS = 'id, code, title, reference, default_penalty_type, default_sanction_value, default_points, precedent_wording';

    public function findById(int $id): ?InfringementArticle
    {
        $row = $this->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM infringement_articles WHERE id = :id',
            ['id' => $id]
        );

        return $row === null ? null : $this->hydrate($row);
    }

    public function findAll(): array
    {
        return array_map(
            fn (array $row): InfringementArticle => $this->hydrate($row),
            $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM infringement_articles ORDER BY code')
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): InfringementArticle
    {
        return new InfringementArticle(
            (int) $row['id'],
            (string) $row['code'],
            (string) $row['title'],
            (string) $row['reference'],
            PenaltyType::from((string) $row['default_penalty_type']),
            $this->toNullableInt($row['default_sanction_value']),
            (int) $row['default_points'],
            (string) $row['precedent_wording'],
        );
    }
}
