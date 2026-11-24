<?php

declare(strict_types=1);

/** KPI card. Set $kpi = ['label', 'value', 'caption' => null, 'icon' => '', 'tone' => 'default'|'accent'|'warning'] before including. */
?>
<div class="card kpi kpi--<?= $view->e($kpi['tone'] ?? 'default') ?>">
    <span class="kpi__icon" aria-hidden="true"><?= $view->e($kpi['icon'] ?? '') ?></span>
    <div>
        <p class="kpi__label"><?= $view->e($kpi['label']) ?></p>
        <p class="kpi__value"><?= $view->e($kpi['value']) ?></p>
        <?php if (!empty($kpi['caption'])): ?>
            <p class="kpi__caption"><?= $view->e($kpi['caption']) ?></p>
        <?php endif; ?>
    </div>
</div>
