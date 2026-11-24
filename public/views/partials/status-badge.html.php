<?php

declare(strict_types=1);

/** Status badge. Set $badge = ['status' => 'UNDER_INVESTIGATION', 'label' => 'Text shown'] before including. */
$badgeClasses = [
    'REPORTED' => 'reported',
    'UNDER_INVESTIGATION' => 'investigation',
    'PENALTY_ISSUED' => 'penalty',
    'NO_FURTHER_ACTION' => 'closed',
    'SUSPENDED' => 'suspended',
    'AT_BAN_RISK' => 'penalty',
    'CAUTION' => 'investigation',
    'ACTIVE' => 'closed',
    'CLEAN' => 'reported',
];
?>
<span class="badge badge--<?= $view->e($badgeClasses[$badge['status']] ?? 'closed') ?>"><?= $view->e($badge['label']) ?></span>
