<header class="page-header">
    <div>
        <p class="eyebrow">Przepisy Sportowe FIA • Załącznik L</p>
        <h1>Klasyfikacja punktów karnych Superlicencji</h1>
        <p class="page-header__lead">Oficjalna tabela punktów dyscyplinarnych z 12 miesięcy (12 punktów = obowiązkowe zawieszenie na 1 wyścig wg ISC, Załącznik L).</p>
    </div>
</header>

<section class="kpi-grid" aria-label="Podsumowanie klasyfikacji">
    <?php
    foreach ([
        ['label' => 'Śledzeni kierowcy', 'value' => $kpis['total'] . ' kierowców', 'caption' => 'Aktywni na liście startowej', 'icon' => '☰'],
        ['label' => 'Próg krytyczny', 'value' => $kpis['top']['name'], 'caption' => $kpis['top']['points'] . ' / 12 pkt • do zawieszenia: ' . $kpis['toBan'] . ' pkt', 'icon' => '⚠', 'tone' => 'accent'],
        ['label' => 'Oczekujące dochodzenia', 'value' => $kpis['pending'] . ' spraw', 'caption' => 'Incydenty w trakcie dochodzenia', 'icon' => '⚑', 'tone' => 'warning'],
        ['label' => 'Czysty rekord', 'value' => $kpis['clean'] . ' kierowców', 'caption' => '0 punktów (' . $kpis['cleanPercent'] . '% stawki)', 'icon' => '✔'],
    ] as $kpi) {
        require __DIR__ . '/../partials/kpi-card.html.php';
    }
    ?>
</section>

<section class="card toolbar" aria-label="Filtry klasyfikacji">
    <form class="toolbar__search" action="/standings" method="get" role="search">
        <div class="form-field">
            <label class="visually-hidden" for="q">Szukaj</label>
            <input type="search" id="q" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" placeholder="Filtruj po nazwisku, zespole lub numerze bolidu…">
        </div>
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter, ENT_QUOTES, 'UTF-8') ?>">
        <button class="button button--secondary" type="submit">Szukaj</button>
    </form>

    <nav class="chips" aria-label="Filtr kierowców">
        <?php
        foreach (['all' => 'Wszyscy kierowcy', 'risk' => 'Zagrożeni (≥7 pkt)', 'clean' => 'Czysta licencja (0 pkt)'] as $key => $chipLabel):
            ?>
            <a class="chip<?= $filter === $key ? ' chip--active' : '' ?>" href="/standings?<?= htmlspecialchars(http_build_query(['filter' => $key, 'q' => $query]), ENT_QUOTES, 'UTF-8') ?>"<?= $filter === $key ? ' aria-current="true"' : '' ?>>
                <?= htmlspecialchars($chipLabel, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </nav>
</section>

<?php if ($rows === []): ?>
    <p class="alert" role="status">Brak kierowców spełniających kryteria.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--cards table--standings">
            <caption class="visually-hidden">Klasyfikacja punktów karnych Superlicencji</caption>
            <thead>
                <tr>
                    <th scope="col">Poz.</th>
                    <th scope="col">Bolid</th>
                    <th scope="col">Kierowca</th>
                    <th scope="col">Zespół</th>
                    <th scope="col">Punkty</th>
                    <th scope="col">Postęp do zawieszenia (12 pkt)</th>
                    <th scope="col">Najbliższe wygaśnięcie</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="cell-pos"><?= (int) $row['position'] ?></td>
                        <td class="cell-car"><span class="lap-chip">#<?= (int) $row['number'] ?></span></td>
                        <th class="cell-name" scope="row"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></th>
                        <td class="cell-team"><?= htmlspecialchars($row['team'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="cell-points"><strong><?= (int) $row['points'] ?></strong> / 12</td>
                        <td class="cell-progress">
                            <div class="progress" role="progressbar" aria-label="Punkty karne: <?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>" aria-valuemin="0" aria-valuemax="12" aria-valuenow="<?= (int) $row['points'] ?>" aria-valuetext="<?= (int) $row['points'] ?> z 12 punktów">
                                <span class="progress__bar progress__bar--<?= $row['points'] >= 9 ? 'high' : ($row['points'] >= 7 ? 'medium' : 'low') ?> progress__bar--p<?= min(12, (int) $row['points']) ?>"></span>
                            </div>
                            <span class="progress__label"><?= (int) $row['percent'] ?>%</span>
                        </td>
                        <td class="cell-expiry">
                            <?php if ($row['expiry'] !== null): ?>
                                <strong><?= htmlspecialchars($row['expiry'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <small>pozostało <?= (int) $row['daysRemaining'] ?> dni</small>
                            <?php else: ?>
                                <span class="text-muted">Brak (czysty rekord)</span>
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
            <p>Pokazano <?= count($rows) ?> z <?= (int) $total ?> kierowców • punkty Superlicencji wygasają dokładnie 365 dni po dacie naruszenia</p>
            <?php
            $pager = ['path' => '/standings', 'params' => ['filter' => $filter, 'q' => $query], 'page' => $page, 'pages' => $pages];
            require __DIR__ . '/../partials/pagination.html.php';
            ?>
        </div>
    </div>
<?php endif; ?>
