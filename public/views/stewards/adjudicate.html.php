<nav class="breadcrumb" aria-label="Okruszki">
    <a href="/incidents">Incydenty</a>
    <span aria-hidden="true">/</span>
    <a href="/incidents/<?= (int) $incident['id'] ?>"><?= $view->e($incident['code']) ?></a>
    <span aria-hidden="true">/</span>
    <span aria-current="page">Orzeczenie</span>
</nav>

<section class="card form-card" aria-labelledby="adjudicate-heading">
    <header class="form-card__head">
        <div>
            <h1 id="adjudicate-heading" class="card__title">Oficjalne orzeczenie sędziów</h1>
            <p class="text-muted">Nałożenie formalnej kary za incydent <?= $view->e($incident['code']) ?> (<?= $view->e($incident['title']) ?>). Decyzja jest wiążąca zgodnie z Międzynarodowym Kodeksem Sportowym FIA.</p>
        </div>
    </header>

    <form class="form form-card__body" action="/stewards/adjudicate/<?= (int) $incident['id'] ?>" method="post">
        <?= $view->csrfField() ?>
        <div class="form__row">
            <div class="form-field">
                <label for="incident-code">ID incydentu</label>
                <input type="text" id="incident-code" value="<?= $view->e($incident['code']) ?> (okr. <?= (int) $incident['lap'] ?>)" readonly>
            </div>
            <div class="form-field">
                <label for="driver">Ukarany kierowca</label>
                <input type="text" id="driver" value="Bolid <?= (int) $incident['accused']['number'] ?> — <?= $view->e($incident['accused']['name']) ?> (<?= $view->e($incident['accused']['team']) ?>)" readonly>
            </div>
        </div>

        <div class="form-field">
            <label for="article_id">Przepis / artykuł naruszenia <span class="required">wymagane</span></label>
            <select id="article_id" name="article_id" required>
                <?php foreach ($articles as $article): ?>
                    <?php $preset = \App\Model\SanctionOption::forPreset($article->getDefaultPenaltyType(), $article->getDefaultSanctionValue()); ?>
                    <option value="<?= (int) $article->getId() ?>"
                            data-sanction="<?= $view->e($preset?->toField() ?? '') ?>"
                            data-points="<?= (int) $article->getDefaultPoints() ?>"
                            data-wording="<?= $view->e($article->getPrecedentWording()) ?>"><?= $view->e($article->getReference()) ?>: <?= $view->e($article->getTitle()) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="form-field__hint">Główny artykuł określający naruszenie standardów jazdy. Wybór artykułu sugeruje sankcję i punkty (można je zmienić).</p>
        </div>

        <fieldset class="fieldset">
            <legend>Sankcja <span class="required">wybierz jedną</span></legend>
            <div class="radio-cards">
                <?php foreach ($sanctions as $sanction): ?>
                    <label class="radio-card">
                        <input type="radio" name="sanction" value="<?= $view->e($sanction->toField()) ?>"<?= $sanction->toField() === $defaultSanction ? ' checked' : '' ?>>
                        <span><strong><?= $view->e($sanction->getTitle()) ?></strong><small><?= $view->e($sanction->getText()) ?></small></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="form-field">
            <label for="points">Punkty karne do naliczenia (0–3)</label>
            <div class="points-row">
                <input type="number" id="points" name="points" min="0" max="3" value="<?= (int) $defaultPoints ?>" inputmode="numeric" data-current-points="<?= (int) $currentPoints ?>">
                <p class="text-muted">Maks. 12 punktów w ciągu 12 miesięcy oznacza automatyczne zawieszenie na 1 wyścig.<br>
                    <span class="accent-text">Obecny bilans kierowcy: <?= (int) $currentPoints ?> pkt → prognozowany: <span data-projected-points><?= (int) $projectedPoints ?></span> pkt</span></p>
            </div>
        </div>

        <div class="form-field">
            <label for="reason">Pisemne uzasadnienie sędziów (trafi do oficjalnego biuletynu FIA)</label>
            <textarea id="reason" name="reason" rows="6" maxlength="1000" required></textarea>
            <button class="button button--secondary" type="button" data-insert-precedent hidden>Wstaw wzorcowe sformułowanie</button>
        </div>

        <label class="check-card">
            <input type="checkbox" name="notify_team" value="1" checked>
            <span><strong>Powiadom szefów zespołów (<?= $view->e($incident['accused']['team']) ?>, <?= $view->e($incident['reporterTeam']) ?>) natychmiast</strong>
                <small>Wysyła e-mail z treścią orzeczenia do obu zespołów.</small></span>
        </label>

        <div class="form-actions">
            <a class="button button--secondary" href="/incidents/<?= (int) $incident['id'] ?>">← Anuluj / wróć do sprawy</a>
            <button class="button" type="submit">Zapisz i zastosuj karę</button>
        </div>
    </form>
</section>
