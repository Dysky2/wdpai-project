<?php declare(strict_types=1) ?>
<section class="card panel-card error-card" aria-labelledby="error-heading">
    <div class="card__body">
        <p class="error-card__code" aria-hidden="true"><?= (int) $status ?></p>
        <p class="eyebrow"><?= $view->te('error.eyebrow', ['status' => (int) $status]) ?></p>
        <h1 id="error-heading"><?= $view->e($title) ?></h1>
        <p class="error-card__text"><?= $view->e($description) ?></p>
        <a class="button" href="/"><?= $view->te('error.back_home') ?> <span aria-hidden="true">→</span></a>
    </div>
</section>
