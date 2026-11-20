<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Model\InfringementArticle;
use App\Repository\InfringementArticleRepositoryInterface;

final class InMemoryArticleRepository implements InfringementArticleRepositoryInterface
{
    public function __construct(private readonly InfringementArticle $article)
    {
    }

    public function findById(int $id): ?InfringementArticle
    {
        return $id === $this->article->getId() ? $this->article : null;
    }

    public function findAll(): array
    {
        return [$this->article];
    }
}
