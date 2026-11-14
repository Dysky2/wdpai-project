<?php
/** KPI card. Set $kpi = ['label', 'value', 'caption' => null, 'icon' => '', 'tone' => 'default'|'accent'|'warning'] before including. */
?>
<div class="card kpi kpi--<?= htmlspecialchars($kpi['tone'] ?? 'default', ENT_QUOTES, 'UTF-8') ?>">
    <span class="kpi__icon" aria-hidden="true"><?= htmlspecialchars($kpi['icon'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    <div>
        <p class="kpi__label"><?= htmlspecialchars($kpi['label'], ENT_QUOTES, 'UTF-8') ?></p>
        <p class="kpi__value"><?= htmlspecialchars((string) $kpi['value'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if (!empty($kpi['caption'])): ?>
            <p class="kpi__caption"><?= htmlspecialchars($kpi['caption'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
</div>
