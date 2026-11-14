<nav class="breadcrumb" aria-label="Okruszki">
    <a href="/incidents">← Incydenty</a>
    <span aria-hidden="true">/</span>
    <span aria-current="page"><?= htmlspecialchars($incident['code'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($incident['title'], ENT_QUOTES, 'UTF-8') ?></span>
</nav>

<div class="dossier">
    <div class="dossier__main">
        <article class="card" aria-labelledby="dossier-heading">
            <header class="card__header">
                <h1 id="dossier-heading" class="card__title">Akta incydentu: <?= htmlspecialchars($incident['code'], ENT_QUOTES, 'UTF-8') ?></h1>
                <span class="meta-chip"><?= htmlspecialchars($incident['reportedAt'], ENT_QUOTES, 'UTF-8') ?></span>
            </header>
            <div class="card__body">
                <dl class="facts">
                    <div><dt>Grand Prix</dt><dd><strong><?= htmlspecialchars($incident['grandPrix'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($incident['circuit'], ENT_QUOTES, 'UTF-8') ?></small></dd></div>
                    <div><dt>Przebieg sesji</dt><dd><strong>Okrążenie <?= (int) $incident['lap'] ?> <span class="text-muted">/ <?= (int) $incident['totalLaps'] ?></span></strong><small>Aktywny wyścig</small></dd></div>
                    <div><dt>Sektor i zakręt</dt><dd><strong><?= htmlspecialchars($incident['corner'], ENT_QUOTES, 'UTF-8') ?></strong><small>Sektor 1</small></dd></div>
                </dl>

                <h2 class="section-label">Zaangażowani kierowcy</h2>
                <div class="drivers-involved">
                    <?php foreach (array_filter([$incident['accused'], $incident['affected']]) as $index => $driver): ?>
                        <div class="driver-card">
                            <span class="driver-card__number"><?= (int) $driver['number'] ?></span>
                            <div>
                                <strong><?= htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <small><?= htmlspecialchars($driver['team'], ENT_QUOTES, 'UTF-8') ?></small>
                            </div>
                            <span class="meta-chip"><?= $index === 0 ? 'Oskarżony' : 'Poszkodowany' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h2 class="section-label">Zarzucane naruszenie</h2>
                <div class="inset">
                    <strong><?= htmlspecialchars($incident['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars($incident['article'], ENT_QUOTES, 'UTF-8') ?></small>
                </div>

                <h2 class="section-label">Opis sprawy</h2>
                <p class="inset"><?= htmlspecialchars($incident['summary'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </article>

        <section class="card" aria-labelledby="evidence-heading">
            <header class="card__header">
                <h2 id="evidence-heading" class="card__title">Dołączone dowody (zdjęcie)</h2>
            </header>
            <figure class="evidence">
                <div class="evidence__image" role="img" aria-label="Klatka z kamery pokładowej: kontakt bolidów na wierzchołku zakrętu 1 (obraz poglądowy)">
                    <span>Podgląd klatki z kamery</span>
                </div>
                <figcaption>
                    <strong><?= htmlspecialchars($incident['evidence']['caption'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <small>Plik: <?= htmlspecialchars($incident['evidence']['file'], ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($incident['evidence']['size'], ENT_QUOTES, 'UTF-8') ?> • SHA-256: <?= htmlspecialchars($incident['evidence']['sha256'], ENT_QUOTES, 'UTF-8') ?></small>
                </figcaption>
            </figure>
        </section>
    </div>

    <aside class="dossier__side" aria-label="Status i akcje">
        <section class="card">
            <header class="card__header"><h2 class="card__title">Status orzeczenia</h2></header>
            <div class="card__body side-block">
                <h3 class="section-label">Aktualne orzeczenie</h3>
                <p class="status-banner status-banner--<?= $incident['status'] === 'UNDER_INVESTIGATION' ? 'investigation' : 'default' ?>">
                    <?php
                    $badge = ['status' => $incident['status'], 'label' => $incident['penaltyLabel'] ?? $incident['statusLabel']];
                    require __DIR__ . '/../partials/status-badge.html.php';
                    ?>
                </p>

                <h3 class="section-label">Sprawę zgłosił</h3>
                <div class="inset">
                    <strong><?= htmlspecialchars($incident['lodgedBy'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars($incident['lodgedByPerson'], ENT_QUOTES, 'UTF-8') ?></small>
                    <small><?= htmlspecialchars($incident['lodgedAt'], ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <p class="doc-ref"><?= htmlspecialchars($incident['docRef'], ENT_QUOTES, 'UTF-8') ?></p>

                <?php if ($incident['penalty'] !== null): ?>
                    <h3 class="section-label">Nałożona kara</h3>
                    <dl class="inset penalty-block">
                        <div><dt>Sankcja</dt><dd><?= htmlspecialchars($incident['penalty']['type'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt>Punkty karne</dt><dd><?= (int) $incident['penalty']['points'] ?></dd></div>
                        <div><dt>Artykuł</dt><dd><?= htmlspecialchars($incident['penalty']['article'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt>Uzasadnienie</dt><dd><?= htmlspecialchars($incident['penalty']['reason'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                    </dl>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <header class="card__header"><h2 class="card__title">Akcje sędziego</h2></header>
            <div class="card__body actions">
                <p class="text-muted">Wybierz rozstrzygnięcie dla tego incydentu:</p>
                <button class="button button--secondary button--block" type="button"<?= $incident['status'] === 'REPORTED' ? '' : ' disabled' ?>>Oznacz jako w trakcie dochodzenia</button>
                <?php if ($incident['status'] === 'UNDER_INVESTIGATION'): ?>
                    <a class="button button--block" href="/stewards/adjudicate/<?= (int) $incident['id'] ?>">Nałóż karę <span aria-hidden="true">→</span></a>
                <?php else: ?>
                    <span class="button button--block button--disabled" aria-disabled="true">Nałóż karę</span>
                <?php endif; ?>
                <button class="button button--secondary button--block" type="button"<?= $incident['status'] === 'UNDER_INVESTIGATION' ? '' : ' disabled' ?>>Umorz sprawę (bez dalszych działań)</button>
            </div>
        </section>
    </aside>
</div>
