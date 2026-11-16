<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Driver;
use App\Model\GrandPrix;
use App\Model\InfringementArticle;
use App\Model\Team;

/** Data the protest form needs: reporting team, selectable Grand Prix, drivers and the article catalogue. */
final class ReportForm
{
    /**
     * @param list<GrandPrix>           $grandsPrix
     * @param list<Driver>              $accusedDrivers drivers of other teams
     * @param list<Driver>              $ownDrivers     drivers of the reporting team
     * @param list<InfringementArticle> $articles
     */
    public function __construct(
        private readonly Team $team,
        private readonly string $representative,
        private readonly array $grandsPrix,
        private readonly ?GrandPrix $defaultGrandPrix,
        private readonly array $accusedDrivers,
        private readonly array $ownDrivers,
        private readonly array $articles,
    ) {
    }

    public function getTeam(): Team { return $this->team; }
    public function getRepresentative(): string { return $this->representative; }
    /** @return list<GrandPrix> */
    public function getGrandsPrix(): array { return $this->grandsPrix; }
    public function getDefaultGrandPrix(): ?GrandPrix { return $this->defaultGrandPrix; }
    /** @return list<Driver> */
    public function getAccusedDrivers(): array { return $this->accusedDrivers; }
    /** @return list<Driver> */
    public function getOwnDrivers(): array { return $this->ownDrivers; }
    /** @return list<InfringementArticle> */
    public function getArticles(): array { return $this->articles; }
}
