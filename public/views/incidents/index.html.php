<?php declare(strict_types=1) ?>
<header class="page-header">
    <div>
        <p class="eyebrow"><?= $view->te('incidents.eyebrow') ?></p>
        <h1><?= $view->te('incidents.heading') ?></h1>
        <p class="page-header__lead"><?= $view->te('incidents.lead') ?></p>
    </div>
    <?php if ($activeGrandPrix !== null): ?>
        <div class="session-chip">
            <span class="session-chip__label"><?= $view->te('incidents.active_session') ?></span>
            <strong><?= $view->e($activeGrandPrix->getName()) ?> • <?= $view->te('incidents.laps_short', ['count' => $activeGrandPrix->getTotalLaps()]) ?></strong>
        </div>
    <?php endif; ?>
</header>

<section class="kpi-grid" aria-label="<?= $view->te('incidents.kpi_aria') ?>">
    <?php
    foreach ([
        ['label' => $view->t('kpi.total'), 'value' => $counts['TOTAL'], 'icon' => '▤'],
        ['label' => $view->t('kpi.reported'), 'value' => $counts['REPORTED'], 'icon' => '!'],
        ['label' => $view->t('kpi.investigation'), 'value' => $counts['UNDER_INVESTIGATION'], 'icon' => '⏳', 'tone' => 'warning'],
        ['label' => $view->t('kpi.penalties'), 'value' => $counts['PENALTY_ISSUED'], 'icon' => '⚑', 'tone' => 'accent'],
    ] as $kpi) {
        require __DIR__ . '/../partials/kpi-card.html.php';
    }
    ?>
</section>

<section class="card toolbar" aria-label="<?= $view->te('incidents.filters_aria') ?>">
    <form class="toolbar__search" action="/incidents" method="get" role="search" data-incidents-form>
        <div class="form-field">
            <label class="visually-hidden" for="gp"><?= $view->te('incidents.gp_label') ?></label>
            <select id="gp" name="gp">
                <option value="all"<?= $gpValue === 'all' ? ' selected' : '' ?>><?= $view->te('incidents.all_gp') ?></option>
                <?php foreach ($grandsPrix as $grandPrix): ?>
                    <option value="<?= $grandPrix->getId() ?>"<?= $gpValue === (string) $grandPrix->getId() ? ' selected' : '' ?>><?= $view->e($grandPrix->getName()) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field">
            <label class="visually-hidden" for="q"><?= $view->te('incidents.search') ?></label>
            <input type="search" id="q" name="q" value="<?= $view->e($query) ?>" placeholder="<?= $view->te('incidents.search_placeholder') ?>">
        </div>
        <input type="hidden" name="status" data-incidents-status value="<?= $view->e($filter) ?>">
        <button class="button button--secondary" type="submit"><?= $view->te('incidents.search') ?></button>
    </form>

    <nav class="chips" aria-label="<?= $view->te('incidents.status_filter_aria') ?>" data-incidents-chips>
        <?php
        foreach ([
            'all' => [$view->t('incidents.chip_all'), $counts['TOTAL']],
            'reported' => [$view->t('incidents.chip_reported'), $counts['REPORTED']],
            'investigation' => [$view->t('incidents.chip_investigation'), $counts['UNDER_INVESTIGATION']],
            'closed' => [$view->t('incidents.chip_closed'), $counts['CLOSED']],
        ] as $key => [$chipLabel, $chipCount]):
            ?>
            <a class="chip<?= $filter === $key ? ' chip--active' : '' ?>" href="/incidents?<?= $view->e(http_build_query(['status' => $key, 'q' => $query, 'gp' => $gpValue])) ?>"<?= $filter === $key ? ' aria-current="true"' : '' ?>>
                <?= $view->e($chipLabel) ?> (<?= (int) $chipCount ?>)
            </a>
        <?php endforeach; ?>
    </nav>
</section>

<div data-incidents-results aria-busy="false">
    <p class="alert" role="status" data-incidents-empty<?= $rows === [] ? '' : ' hidden' ?>><?= $view->te('incidents.empty') ?></p>
    <p class="alert alert--error" role="alert" data-incidents-error hidden></p>
    <div class="table-wrap" data-incidents-table<?= $rows === [] ? ' hidden' : '' ?>>
        <table class="table table--cards table--incidents">
            <caption class="visually-hidden"><?= $view->te('incidents.table_caption') ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= $view->te('incidents.col_lap') ?></th>
                    <th scope="col"><?= $view->te('incidents.col_id') ?></th>
                    <th scope="col"><?= $view->te('incidents.col_drivers') ?></th>
                    <th scope="col"><?= $view->te('incidents.col_category') ?></th>
                    <th scope="col"><?= $view->te('incidents.col_status') ?></th>
                    <th scope="col"><span class="visually-hidden"><?= $view->te('incidents.col_action') ?></span></th>
                </tr>
            </thead>
            <tbody data-incidents-body>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="cell-lap"><span class="lap-chip">L<?= str_pad((string) $row['lap'], 2, '0', STR_PAD_LEFT) ?></span></td>
                        <td class="cell-id"><strong class="incident-id"><?= $view->e($row['code']) ?></strong></td>
                        <td class="cell-drivers">
                            <?php foreach ($row['drivers'] as $index => $driver): ?>
                                <?= $index > 0 ? '<span class="drivers__vs">vs</span>' : '' ?>
                                <span class="driver-tag"><?= $view->te('driver.car', ['number' => (int) $driver['number']]) ?> <small>(<?= $view->e($driver['code']) ?>)</small></span>
                            <?php endforeach; ?>
                        </td>
                        <td class="cell-category">
                            <strong><?= $view->e($row['title']) ?></strong>
                            <small><?= $view->e($row['summary']) ?></small>
                        </td>
                        <td class="cell-status">
                            <?php
                            $badge = ['status' => $row['status'], 'label' => $row['penaltyLabel'] ?? $row['statusLabel']];
                            require __DIR__ . '/../partials/status-badge.html.php';
                            ?>
                        </td>
                        <td class="cell-action"><a class="button button--secondary button--small" href="<?= $view->e($row['url']) ?>"><?= $view->te('incidents.details') ?> <span class="visually-hidden"><?= $view->e($row['code']) ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="table-footer">
            <p data-incidents-summary role="status"><?= $view->te('incidents.summary', ['shown' => count($rows), 'total' => (int) $total]) ?></p>
            <div data-incidents-pager>
                <?php
                $pager = ['path' => '/incidents', 'params' => ['status' => $filter, 'q' => $query, 'gp' => $gpValue], 'page' => $page, 'pages' => $pages];
                require __DIR__ . '/../partials/pagination.html.php';
                ?>
            </div>
        </div>
    </div>
</div>
