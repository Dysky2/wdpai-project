<?php declare(strict_types=1) ?>
<nav class="breadcrumb" aria-label="<?= $view->te('show.breadcrumb_aria') ?>">
    <a href="/incidents">← <?= $view->te('nav.incidents') ?></a>
    <span aria-hidden="true">/</span>
    <span aria-current="page"><?= $view->e($incident['code']) ?> — <?= $view->e($incident['title']) ?></span>
</nav>

<div class="dossier">
    <div class="dossier__main">
        <article class="card" aria-labelledby="dossier-heading">
            <header class="card__header">
                <h1 id="dossier-heading" class="card__title"><?= $view->te('show.heading', ['code' => $incident['code']]) ?></h1>
                <span class="meta-chip"><?= $view->e($incident['reportedAt']) ?></span>
            </header>
            <div class="card__body">
                <dl class="facts">
                    <div><dt><?= $view->te('show.grand_prix') ?></dt><dd><strong><?= $view->e($incident['grandPrix']) ?></strong><small><?= $view->e($incident['circuit']) ?></small></dd></div>
                    <div><dt><?= $view->te('show.session_progress') ?></dt><dd><strong><?= $view->te('show.lap', ['lap' => (int) $incident['lap']]) ?> <span class="text-muted">/ <?= (int) $incident['totalLaps'] ?></span></strong></dd></div>
                </dl>

                <h2 class="section-label"><?= $view->te('show.drivers_involved') ?></h2>
                <div class="drivers-involved">
                    <?php foreach ([[$view->t('show.role_accused'), $incident['accused']], [$view->t('show.role_affected'), $incident['affected']]] as [$role, $driver]): ?>
                        <?php if ($driver !== null): ?>
                            <div class="driver-card">
                                <span class="driver-card__number"><?= (int) $driver['number'] ?></span>
                                <div>
                                    <strong><?= $view->e($driver['name']) ?></strong>
                                    <small><?= $view->e($driver['team']) ?></small>
                                </div>
                                <span class="meta-chip"><?= $view->e($role) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <h2 class="section-label"><?= $view->te('show.alleged_infringement') ?></h2>
                <div class="inset">
                    <strong><?= $view->e($incident['title']) ?></strong>
                    <small><?= $view->e($incident['article']) ?></small>
                </div>

                <h2 class="section-label"><?= $view->te('show.case_description') ?></h2>
                <p class="inset"><?= $view->e($incident['description']) ?></p>
            </div>
        </article>

        <section class="card" aria-labelledby="evidence-heading">
            <header class="card__header">
                <h2 id="evidence-heading" class="card__title"><?= $view->te('show.evidence_heading') ?></h2>
            </header>
            <?php if ($incident['evidence'] !== null): ?>
                <figure class="evidence">
                    <img class="evidence__image" src="<?= $view->e($incident['evidence']['url']) ?>"
                         alt="<?= $view->te('show.evidence_alt', ['code' => $incident['code'], 'lap' => (int) $incident['lap'], 'file' => $incident['evidence']['name']]) ?>">
                    <figcaption>
                        <small><?= $view->te('show.evidence_file') ?> <?= $view->e($incident['evidence']['name']) ?> • <?= $view->e($incident['evidence']['size']) ?> • SHA-256: <?= $view->e($incident['evidence']['sha256']) ?></small>
                    </figcaption>
                </figure>
            <?php else: ?>
                <p class="card__body text-muted"><?= $view->te('show.no_evidence') ?></p>
            <?php endif; ?>
        </section>
    </div>

    <aside class="dossier__side" aria-label="<?= $view->te('show.side_aria') ?>">
        <section class="card">
            <header class="card__header"><h2 class="card__title"><?= $view->te('show.ruling_status') ?></h2></header>
            <div class="card__body side-block">
                <h3 class="section-label"><?= $view->te('show.current_ruling') ?></h3>
                <p class="status-banner status-banner--<?= $incident['status'] === 'UNDER_INVESTIGATION' ? 'investigation' : 'default' ?>" data-status-banner>
                    <?php
                    $badge = ['status' => $incident['status'], 'label' => $incident['penalty']['label'] ?? $incident['statusLabel']];
                    require __DIR__ . '/../partials/status-badge.html.php';
                    ?>
                </p>

                <h3 class="section-label"><?= $view->te('show.reported_by') ?></h3>
                <div class="inset">
                    <strong><?= $view->e($incident['reporterTeam']) ?></strong>
                    <small><?= $view->e($incident['reporter']) ?></small>
                </div>

                <?php if ($incident['penalty'] !== null): ?>
                    <h3 class="section-label"><?= $view->te('show.penalty_imposed') ?></h3>
                    <dl class="inset penalty-block">
                        <div><dt><?= $view->te('show.sanction') ?></dt><dd><?= $view->e($incident['penalty']['label']) ?></dd></div>
                        <div><dt><?= $view->te('show.penalty_points') ?></dt><dd><?= (int) $incident['penalty']['points'] ?></dd></div>
                        <div><dt><?= $view->te('show.article') ?></dt><dd><?= $view->e($incident['penalty']['article']) ?></dd></div>
                        <div><dt><?= $view->te('show.reason') ?></dt><dd><?= $view->e($incident['penalty']['reason']) ?></dd></div>
                    </dl>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($isSteward): ?>
            <section class="card">
                <header class="card__header"><h2 class="card__title"><?= $view->te('show.steward_actions') ?></h2></header>
                <div class="card__body actions">
                    <p class="text-muted"><?= $view->te('show.choose_ruling') ?></p>
                    <p class="alert alert--error" role="alert" data-incident-error hidden></p>
                    <button class="button button--secondary button--block" type="button" data-incident-action="investigate" data-incident-id="<?= (int) $incident['id'] ?>"<?= $incident['status'] === 'REPORTED' ? '' : ' disabled' ?>><?= $view->te('show.mark_investigation') ?></button>
                    <a class="button button--block" data-adjudicate-link href="/stewards/adjudicate/<?= (int) $incident['id'] ?>"<?= $incident['status'] === 'UNDER_INVESTIGATION' ? '' : ' hidden' ?>><?= $view->te('show.issue_penalty') ?> <span aria-hidden="true">→</span></a>
                    <span class="button button--block button--disabled" data-adjudicate-disabled aria-disabled="true"<?= $incident['status'] === 'UNDER_INVESTIGATION' ? ' hidden' : '' ?>><?= $view->te('show.issue_penalty') ?></span>
                    <button class="button button--secondary button--block" type="button" data-incident-action="dismiss" data-incident-id="<?= (int) $incident['id'] ?>"<?= $incident['status'] === 'UNDER_INVESTIGATION' ? '' : ' disabled' ?>><?= $view->te('show.dismiss') ?></button>
                </div>
            </section>
        <?php endif; ?>
    </aside>
</div>
