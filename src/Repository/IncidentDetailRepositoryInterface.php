<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\IncidentDetail;

/** Read side of the case file. */
interface IncidentDetailRepositoryInterface
{
    public function findById(int $id): ?IncidentDetail;
}
