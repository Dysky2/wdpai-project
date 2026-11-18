<header class="page-header">
    <div>
        <p class="eyebrow">Kolejka orzeczeń FIA</p>
        <h1>Dziennik incydentów Grand Prix</h1>
        <p class="page-header__lead">Oficjalna kolejka spraw sędziów FIA i zarejestrowane incydenty na torze</p>
    </div>
    <?php if ($activeGrandPrix !== null): ?>
        <div class="session-chip">
            <span class="session-chip__label">Aktywna sesja</span>
            <strong><?= $view->e($activeGrandPrix->getName()) ?> • <?= $activeGrandPrix->getTotalLaps() ?> okr.</strong>
        </div>
    <?php endif; ?>
</header>

<section class="kpi-grid" aria-label="Podsumowanie incydentów">
    <?php
    foreach ([
        ['label' => 'Wszystkie zarejestrowane', 'value' => $counts['TOTAL'], 'icon' => '▤'],
        ['label' => 'Zgłoszone', 'value' => $counts['REPORTED'], 'icon' => '!'],
        ['label' => 'W trakcie dochodzenia', 'value' => $counts['UNDER_INVESTIGATION'], 'icon' => '⏳', 'tone' => 'warning'],
        ['label' => 'Nałożone kary', 'value' => $counts['PENALTY_ISSUED'], 'icon' => '⚑', 'tone' => 'accent'],
    ] as $kpi) {
        require __DIR__ . '/../partials/kpi-card.html.php';
    }
    ?>
</section>

<section class="card toolbar" aria-label="Filtry incydentów">
    <form class="toolbar__search" action="/incidents" method="get" role="search" data-incidents-form>
        <div class="form-field">
            <label class="visually-hidden" for="gp">Grand Prix</label>
            <select id="gp" name="gp">
                <option value="all"<?= $gpValue === 'all' ? ' selected' : '' ?>>Wszystkie Grand Prix</option>
                <?php foreach ($grandsPrix as $grandPrix): ?>
                    <option value="<?= $grandPrix->getId() ?>"<?= $gpValue === (string) $grandPrix->getId() ? ' selected' : '' ?>><?= $view->e($grandPrix->getName()) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field">
            <label class="visually-hidden" for="q">Szukaj</label>
            <input type="search" id="q" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" placeholder="Szukaj kierowcy (np. VER), ID (INC-042) lub opisu…">
        </div>
        <input type="hidden" name="status" data-incidents-status value="<?= htmlspecialchars($filter, ENT_QUOTES, 'UTF-8') ?>">
        <button class="button button--secondary" type="submit">Szukaj</button>
    </form>

    <nav class="chips" aria-label="Filtr statusu" data-incidents-chips>
        <?php
        foreach ([
            'all' => ['Wszystkie', $counts['TOTAL']],
            'reported' => ['Zgłoszone', $counts['REPORTED']],
            'investigation' => ['W trakcie', $counts['UNDER_INVESTIGATION']],
            'closed' => ['Zamknięte / rozstrzygnięte', $counts['CLOSED']],
        ] as $key => [$chipLabel, $chipCount]):
            ?>
            <a class="chip<?= $filter === $key ? ' chip--active' : '' ?>" href="/incidents?<?= htmlspecialchars(http_build_query(['status' => $key, 'q' => $query, 'gp' => $gpValue]), ENT_QUOTES, 'UTF-8') ?>"<?= $filter === $key ? ' aria-current="true"' : '' ?>>
                <?= htmlspecialchars($chipLabel, ENT_QUOTES, 'UTF-8') ?> (<?= (int) $chipCount ?>)
            </a>
        <?php endforeach; ?>
    </nav>
</section>

<div data-incidents-results aria-busy="false">
    <p class="alert" role="status" data-incidents-empty<?= $rows === [] ? '' : ' hidden' ?>>Brak incydentów spełniających kryteria wyszukiwania.</p>
    <p class="alert alert--error" role="alert" data-incidents-error hidden></p>
    <div class="table-wrap" data-incidents-table<?= $rows === [] ? ' hidden' : '' ?>>
        <table class="table table--cards table--incidents">
            <caption class="visually-hidden">Lista incydentów</caption>
            <thead>
                <tr>
                    <th scope="col">Okr.</th>
                    <th scope="col">ID incydentu</th>
                    <th scope="col">Zaangażowani kierowcy</th>
                    <th scope="col">Kategoria / opis</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="visually-hidden">Akcja</span></th>
                </tr>
            </thead>
            <tbody data-incidents-body>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="cell-lap"><span class="lap-chip">L<?= str_pad((string) $row['lap'], 2, '0', STR_PAD_LEFT) ?></span></td>
                        <td class="cell-id"><strong class="incident-id"><?= htmlspecialchars($row['code'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td class="cell-drivers">
                            <?php foreach ($row['drivers'] as $index => $driver): ?>
                                <?= $index > 0 ? '<span class="drivers__vs">vs</span>' : '' ?>
                                <span class="driver-tag">Bolid <?= (int) $driver['number'] ?> <small>(<?= htmlspecialchars($driver['code'], ENT_QUOTES, 'UTF-8') ?>)</small></span>
                            <?php endforeach; ?>
                        </td>
                        <td class="cell-category">
                            <strong><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars($row['summary'], ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td class="cell-status">
                            <?php
                            $badge = ['status' => $row['status'], 'label' => $row['penaltyLabel'] ?? $row['statusLabel']];
                            require __DIR__ . '/../partials/status-badge.html.php';
                            ?>
                        </td>
                        <td class="cell-action"><a class="button button--secondary button--small" href="<?= htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8') ?>">Szczegóły <span class="visually-hidden"><?= htmlspecialchars($row['code'], ENT_QUOTES, 'UTF-8') ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="table-footer">
            <p data-incidents-summary role="status">Pokazano <?= count($rows) ?> z <?= (int) $total ?> incydentów</p>
            <div data-incidents-pager>
                <?php
                $pager = ['path' => '/incidents', 'params' => ['status' => $filter, 'q' => $query, 'gp' => $gpValue], 'page' => $page, 'pages' => $pages];
                require __DIR__ . '/../partials/pagination.html.php';
                ?>
            </div>
        </div>
    </div>
</div>
