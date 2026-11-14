<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\InfringementArticle;

interface InfringementArticleRepositoryInterface
{
    public function findById(int $id): ?InfringementArticle;

    /** @return list<InfringementArticle> ordered by code */
    public function findAll(): array;
}
