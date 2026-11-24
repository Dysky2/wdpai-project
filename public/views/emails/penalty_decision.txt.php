<?php
/** @var \App\Service\Mail\RulingMessage $message */
?>
FIA Stewards Decision

Case: <?= $message->incidentCode ?>

Driver: <?= $message->driverName ?>

Article: <?= $message->article ?>

Sanction: <?= $message->sanction ?>

Penalty points: <?= $message->points ?>


Reasoning:
<?= $message->reason ?>

