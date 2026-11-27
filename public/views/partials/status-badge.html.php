<?php

declare(strict_types=1);

/** Status badge. Set $badge = ['status' => 'UNDER_INVESTIGATION', 'label' => 'Text shown'] before including. */
?>
<span class="badge badge--<?= $view->e($view->badgeModifier($badge['status'])) ?>"><?= $view->e($badge['label']) ?></span>
