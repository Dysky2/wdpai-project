<?php
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
<span class="badge badge--<?= htmlspecialchars($badgeClasses[$badge['status']] ?? 'closed', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($badge['label'], ENT_QUOTES, 'UTF-8') ?></span>
