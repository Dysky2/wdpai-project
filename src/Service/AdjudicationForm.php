<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\IncidentDetail;
use App\Model\InfringementArticle;
use App\Model\SanctionOption;

/** Data the adjudication form needs: the case, the article catalogue, the sanctions and the points forecast. */
final class AdjudicationForm
{
    /**
     * @param list<InfringementArticle> $articles
     * @param list<SanctionOption>      $sanctions
     */
    public function __construct(
        private readonly IncidentDetail $incident,
        private readonly array $articles,
        private readonly array $sanctions,
        private readonly SanctionOption $defaultSanction,
        private readonly int $defaultPoints,
        private readonly int $currentPoints,
        private readonly int $projectedPoints,
    ) {
    }

    public function getIncident(): IncidentDetail { return $this->incident; }
    /** @return list<InfringementArticle> */
    public function getArticles(): array { return $this->articles; }
    /** @return list<SanctionOption> */
    public function getSanctions(): array { return $this->sanctions; }
    public function getDefaultSanction(): SanctionOption { return $this->defaultSanction; }
    public function getDefaultPoints(): int { return $this->defaultPoints; }
    public function getCurrentPoints(): int { return $this->currentPoints; }
    public function getProjectedPoints(): int { return $this->projectedPoints; }
}
