<nav class="breadcrumb" aria-label="Okruszki">
    <a href="/incidents">Incydenty</a>
    <span aria-hidden="true">/</span>
    <a href="<?= htmlspecialchars($incident['url'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($incident['code'], ENT_QUOTES, 'UTF-8') ?></a>
    <span aria-hidden="true">/</span>
    <span aria-current="page">Orzeczenie</span>
</nav>

<section class="card form-card" aria-labelledby="adjudicate-heading">
    <header class="form-card__head">
        <div>
            <h1 id="adjudicate-heading" class="card__title">Oficjalne orzeczenie sędziów <span class="doc-tag">DOC 48</span></h1>
            <p class="text-muted">Nałożenie formalnej kary za incydent <?= htmlspecialchars($incident['code'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($incident['title'], ENT_QUOTES, 'UTF-8') ?>). Decyzja jest wiążąca zgodnie z Międzynarodowym Kodeksem Sportowym FIA.</p>
        </div>
    </header>

    <form class="form form-card__body" action="/stewards/adjudicate/<?= (int) $incident['id'] ?>" method="post">
        <div class="form__row">
            <div class="form-field">
                <label for="incident-code">ID incydentu</label>
                <input type="text" id="incident-code" value="<?= htmlspecialchars($incident['code'], ENT_QUOTES, 'UTF-8') ?> (okr. <?= (int) $incident['lap'] ?>)" readonly>
            </div>
            <div class="form-field">
                <label for="driver">Ukarany kierowca</label>
                <input type="text" id="driver" value="Bolid <?= (int) $incident['accused']['number'] ?> — <?= htmlspecialchars($incident['accused']['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($incident['accused']['team'], ENT_QUOTES, 'UTF-8') ?>)" readonly>
            </div>
        </div>

        <div class="form-field">
            <label for="article_id">Przepis / artykuł naruszenia <span class="required">wymagane</span></label>
            <select id="article_id" name="article_id" required>
                <?php foreach ($articles as $article): ?>
                    <option><?= htmlspecialchars($article, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <p class="form-field__hint">Główny artykuł określający naruszenie standardów jazdy.</p>
        </div>

        <fieldset class="fieldset">
            <legend>Sankcja <span class="required">wybierz jedną</span></legend>
            <div class="radio-cards">
                <?php foreach ($sanctions as $index => $sanction): ?>
                    <label class="radio-card">
                        <input type="radio" name="sanction" value="<?= htmlspecialchars($sanction['value'], ENT_QUOTES, 'UTF-8') ?>"<?= $index === 0 ? ' checked' : '' ?>>
                        <span><strong><?= htmlspecialchars($sanction['title'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($sanction['text'], ENT_QUOTES, 'UTF-8') ?></small></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="form-field">
            <label for="points">Punkty karne do naliczenia (0–3)</label>
            <div class="points-row">
                <input type="number" id="points" name="points" min="0" max="3" value="<?= (int) $defaultPoints ?>" inputmode="numeric">
                <p class="text-muted">Maks. 12 punktów w ciągu 12 miesięcy oznacza automatyczne zawieszenie na 1 wyścig.<br>
                    <span class="accent-text">Obecny bilans kierowcy: <?= (int) $currentPoints ?> pkt → prognozowany: <?= (int) $projectedPoints ?> pkt</span></p>
            </div>
        </div>

        <div class="form-field">
            <label for="reason">Pisemne uzasadnienie sędziów (trafi do oficjalnego biuletynu FIA)</label>
            <textarea id="reason" name="reason" rows="6" maxlength="1000" required>Sędziowie przeanalizowali materiał wideo i dane z kamer pokładowych. Bolid nr 1 nie pozostawił bolidowi nr 44 wystarczającej szerokości toru na wyjściu, powodując kontakt kołami i wypchnięcie z toru. Sędziowie nakładają karę czasową 5 sekund oraz 2 punkty Superlicencji.</textarea>
        </div>

        <label class="check-card">
            <input type="checkbox" name="notify_team" value="1" checked>
            <span><strong>Powiadom szefów zespołów (<?= htmlspecialchars($incident['accused']['team'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($incident['lodgedBy'], ENT_QUOTES, 'UTF-8') ?>) natychmiast</strong>
                <small>Wysyła e-mail z treścią orzeczenia do obu zespołów.</small></span>
        </label>

        <div class="form-actions">
            <a class="button button--secondary" href="<?= htmlspecialchars($incident['url'], ENT_QUOTES, 'UTF-8') ?>">← Anuluj / wróć do sprawy</a>
            <button class="button" type="submit">Zapisz i zastosuj karę</button>
        </div>
    </form>
</section>
