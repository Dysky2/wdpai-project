<nav class="breadcrumb" aria-label="Okruszki">
    <a href="/incidents">← Incydenty</a>
    <span aria-hidden="true">/</span>
    <span aria-current="page"><?= $view->e($incident['code']) ?> — <?= $view->e($incident['title']) ?></span>
</nav>

<div class="dossier">
    <div class="dossier__main">
        <article class="card" aria-labelledby="dossier-heading">
            <header class="card__header">
                <h1 id="dossier-heading" class="card__title">Akta incydentu: <?= $view->e($incident['code']) ?></h1>
                <span class="meta-chip"><?= $view->e($incident['reportedAt']) ?></span>
            </header>
            <div class="card__body">
                <dl class="facts">
                    <div><dt>Grand Prix</dt><dd><strong><?= $view->e($incident['grandPrix']) ?></strong><small><?= $view->e($incident['circuit']) ?></small></dd></div>
                    <div><dt>Przebieg sesji</dt><dd><strong>Okrążenie <?= (int) $incident['lap'] ?> <span class="text-muted">/ <?= (int) $incident['totalLaps'] ?></span></strong></dd></div>
                </dl>

                <h2 class="section-label">Zaangażowani kierowcy</h2>
                <div class="drivers-involved">
                    <?php foreach ([['Oskarżony', $incident['accused']], ['Poszkodowany', $incident['affected']]] as [$role, $driver]): ?>
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

                <h2 class="section-label">Zarzucane naruszenie</h2>
                <div class="inset">
                    <strong><?= $view->e($incident['title']) ?></strong>
                    <small><?= $view->e($incident['article']) ?></small>
                </div>

                <h2 class="section-label">Opis sprawy</h2>
                <p class="inset"><?= $view->e($incident['description']) ?></p>
            </div>
        </article>

        <section class="card" aria-labelledby="evidence-heading">
            <header class="card__header">
                <h2 id="evidence-heading" class="card__title">Dołączone dowody (zdjęcie)</h2>
            </header>
            <?php if ($incident['evidence'] !== null): ?>
                <figure class="evidence">
                    <img class="evidence__image" src="<?= $view->e($incident['evidence']['url']) ?>"
                         alt="Dowód do sprawy <?= $view->e($incident['code']) ?>: zdjęcie z okrążenia <?= (int) $incident['lap'] ?> (plik <?= $view->e($incident['evidence']['name']) ?>)">
                    <figcaption>
                        <small>Plik: <?= $view->e($incident['evidence']['name']) ?> • <?= $view->e($incident['evidence']['size']) ?> • SHA-256: <?= $view->e($incident['evidence']['sha256']) ?></small>
                    </figcaption>
                </figure>
            <?php else: ?>
                <p class="card__body text-muted">Do tej sprawy nie dołączono dowodu.</p>
            <?php endif; ?>
        </section>
    </div>

    <aside class="dossier__side" aria-label="Status i akcje">
        <section class="card">
            <header class="card__header"><h2 class="card__title">Status orzeczenia</h2></header>
            <div class="card__body side-block">
                <h3 class="section-label">Aktualne orzeczenie</h3>
                <p class="status-banner status-banner--<?= $incident['status'] === 'UNDER_INVESTIGATION' ? 'investigation' : 'default' ?>">
                    <?php
                    $badge = ['status' => $incident['status'], 'label' => $incident['penalty']['label'] ?? $incident['statusLabel']];
                    require __DIR__ . '/../partials/status-badge.html.php';
                    ?>
                </p>

                <h3 class="section-label">Sprawę zgłosił</h3>
                <div class="inset">
                    <strong><?= $view->e($incident['reporterTeam']) ?></strong>
                    <small><?= $view->e($incident['reporter']) ?></small>
                </div>

                <?php if ($incident['penalty'] !== null): ?>
                    <h3 class="section-label">Nałożona kara</h3>
                    <dl class="inset penalty-block">
                        <div><dt>Sankcja</dt><dd><?= $view->e($incident['penalty']['label']) ?></dd></div>
                        <div><dt>Punkty karne</dt><dd><?= (int) $incident['penalty']['points'] ?></dd></div>
                        <div><dt>Artykuł</dt><dd><?= $view->e($incident['penalty']['article']) ?></dd></div>
                        <div><dt>Uzasadnienie</dt><dd><?= $view->e($incident['penalty']['reason']) ?></dd></div>
                    </dl>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($isSteward): ?>
            <section class="card">
                <header class="card__header"><h2 class="card__title">Akcje sędziego</h2></header>
                <div class="card__body actions">
                    <p class="text-muted">Wybierz rozstrzygnięcie dla tego incydentu:</p>
                    <button class="button button--secondary button--block" type="button" data-incident-action="investigate" data-incident-id="<?= (int) $incident['id'] ?>"<?= $incident['status'] === 'REPORTED' ? '' : ' disabled' ?>>Oznacz jako w trakcie dochodzenia</button>
                    <?php if ($incident['status'] === 'UNDER_INVESTIGATION'): ?>
                        <a class="button button--block" href="/stewards/adjudicate/<?= (int) $incident['id'] ?>">Nałóż karę <span aria-hidden="true">→</span></a>
                    <?php else: ?>
                        <span class="button button--block button--disabled" aria-disabled="true">Nałóż karę</span>
                    <?php endif; ?>
                    <button class="button button--secondary button--block" type="button" data-incident-action="dismiss" data-incident-id="<?= (int) $incident['id'] ?>"<?= $incident['status'] === 'UNDER_INVESTIGATION' ? '' : ' disabled' ?>>Umorz sprawę (bez dalszych działań)</button>
                </div>
            </section>
        <?php endif; ?>
    </aside>
</div>
