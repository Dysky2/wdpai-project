<?php
/** @var \App\Service\Mail\RulingMessage $message */
/** @var \App\View\ViewHelper $view */
?>
<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1a1a1a;">
<h1 style="font-size: 20px;">FIA Stewards Decision</h1>
<table cellpadding="6" style="border-collapse: collapse;">
    <tr><th align="left">Case</th><td><?= $view->e($message->incidentCode) ?></td></tr>
    <tr><th align="left">Driver</th><td><?= $view->e($message->driverName) ?></td></tr>
    <tr><th align="left">Article</th><td><?= $view->e($message->article) ?></td></tr>
    <tr><th align="left">Sanction</th><td><?= $view->e($message->sanction) ?></td></tr>
    <tr><th align="left">Penalty points</th><td><?= $view->e($message->points) ?></td></tr>
</table>
<h2 style="font-size: 16px;">Reasoning</h2>
<p><?= nl2br($view->e($message->reason)) ?></p>
<p style="color: #666; font-size: 12px;">This is an automated message from ApexSteward (FIA Stewards Hub).</p>
</body>
</html>
