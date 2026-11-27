<?php declare(strict_types=1) ?>
<header class="page-header">
    <div>
        <p class="eyebrow"><?= $view->te('standings.eyebrow') ?></p>
        <h1><?= $view->te('standings.heading') ?></h1>
        <p class="page-header__lead"><?= $view->te('standings.lead') ?></p>
    </div>
</header>

<section class="kpi-grid" aria-label="<?= $view->te('standings.kpi_aria') ?>">
    <?php foreach ($kpis as $kpi): ?>
        <?php require __DIR__ . '/../partials/kpi-card.html.php'; ?>
    <?php endforeach; ?>
</section>

<section class="card toolbar" aria-label="<?= $view->te('standings.filters_aria') ?>">
    <form class="toolbar__search" action="/standings" method="get" role="search">
        <div class="form-field">
            <label class="visually-hidden" for="q"><?= $view->te('incidents.search') ?></label>
            <input type="search" id="q" name="q" value="<?= $view->e($query) ?>" placeholder="<?= $view->te('standings.search_placeholder') ?>">
        </div>
        <input type="hidden" name="filter" value="<?= $view->e($filter) ?>">
        <button class="button button--secondary" type="submit"><?= $view->te('incidents.search') ?></button>
    </form>

    <nav class="chips" aria-label="<?= $view->te('standings.driver_filter_aria') ?>">
        <?php foreach ($chips as $chip): ?>
            <a class="chip<?= $chip['active'] ? ' chip--active' : '' ?>" href="<?= $view->e($chip['href']) ?>"<?= $chip['active'] ? ' aria-current="true"' : '' ?>>
                <?= $view->e($chip['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</section>

<?php if ($rows === []): ?>
    <p class="alert" role="status"><?= $view->te('standings.empty') ?></p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--cards table--standings">
            <caption class="visually-hidden"><?= $view->te('standings.heading') ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= $view->te('standings.col_position') ?></th>
                    <th scope="col"><?= $view->te('standings.col_car') ?></th>
                    <th scope="col"><?= $view->te('standings.col_driver') ?></th>
                    <th scope="col"><?= $view->te('standings.col_team') ?></th>
                    <th scope="col"><?= $view->te('standings.col_points') ?></th>
                    <th scope="col"><?= $view->te('standings.col_progress') ?></th>
                    <th scope="col"><?= $view->te('standings.col_expiry') ?></th>
                    <th scope="col"><?= $view->te('incidents.col_status') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="cell-pos"><?= (int) $row['position'] ?></td>
                        <td class="cell-car"><span class="lap-chip">#<?= (int) $row['number'] ?></span></td>
                        <th class="cell-name" scope="row"><?= $view->e($row['name']) ?></th>
                        <td class="cell-team"><?= $view->e($row['team']) ?></td>
                        <td class="cell-points"><strong><?= (int) $row['points'] ?></strong> / <?= (int) $pointsLimit ?></td>
                        <td class="cell-progress">
                            <div class="progress" role="progressbar" aria-label="<?= $view->te('standings.progress_aria', ['name' => $row['name']]) ?>" aria-valuemin="0" aria-valuemax="<?= (int) $pointsLimit ?>" aria-valuenow="<?= (int) $row['points'] ?>" aria-valuetext="<?= $view->te('standings.progress_text', ['points' => (int) $row['points']]) ?>">
                                <span class="progress__bar progress__bar--<?= $view->e($row['progressTone']) ?> progress__bar--p<?= (int) $row['progressStep'] ?>"></span>
                            </div>
                            <span class="progress__label"><?= (int) $row['percent'] ?>%</span>
                        </td>
                        <td class="cell-expiry">
                            <?php if ($row['expiry'] !== null): ?>
                                <strong><?= $view->e($row['expiry']) ?></strong>
                                <small><?= $view->te('standings.days_left', ['days' => (int) $row['daysRemaining']]) ?></small>
                            <?php else: ?>
                                <span class="text-muted"><?= $view->te('standings.no_expiry') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="cell-status">
                            <?php
                            $badge = ['status' => $row['status'], 'label' => $row['statusLabel']];
                            require __DIR__ . '/../partials/status-badge.html.php';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="table-footer">
            <p><?= $view->te('standings.summary', ['shown' => (int) $shown, 'total' => (int) $total]) ?></p>
            <?php require __DIR__ . '/../partials/pagination.html.php'; ?>
        </div>
    </div>
<?php endif; ?>
