<header class="page-header">
    <div>
        <p class="eyebrow">Protokół orzecznictwa FIA • ISC art. 13</p>
        <h1>Zgłoszenie incydentu / protest zespołu</h1>
        <p class="page-header__lead">Oficjalny portal dla szefów zespołów i dyrektorów sportowych do zgłaszania naruszeń na torze bezpośrednio do kierownictwa wyścigu i sędziów.</p>
    </div>
</header>

<section class="card form-card" aria-labelledby="report-heading">
    <header class="form-card__head">
        <div>
            <p class="eyebrow">Oficjalny dokument zgłoszeniowy</p>
            <h2 id="report-heading" class="card__title">Formularz zgłoszenia incydentu przez zespół</h2>
        </div>
        <span class="doc-tag">DOC REF: FIA-PR-2024-MCO-084</span>
    </header>

    <div class="notice" role="note">
        Protesty należy składać w ciągu 30 minut od zakończenia sesji lub zdarzenia (FIA ISC art. 13). Zgłoszenia po terminie są nieważne zgodnie z przepisami sportowymi.
    </div>

    <form class="form form-card__body" action="/incidents" method="post" enctype="multipart/form-data">
        <div class="form__row">
            <div class="form-field">
                <label for="grand_prix_id">Sesja Grand Prix <span class="required">*</span></label>
                <select id="grand_prix_id" name="grand_prix_id" required>
                    <option><?= htmlspecialchars($form['grandPrix'], ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>
            <div class="form-field">
                <label for="lap_number">Numer okrążenia <span class="required">*</span></label>
                <input type="number" id="lap_number" name="lap_number" min="1" max="<?= (int) $form['totalLaps'] ?>" value="24" inputmode="numeric" required aria-describedby="lap-hint">
                <p class="form-field__hint" id="lap-hint">maks. <?= (int) $form['totalLaps'] ?></p>
            </div>
        </div>

        <div class="form__row">
            <div class="form-field">
                <label for="team">Zespół składający zgłoszenie</label>
                <input type="text" id="team" value="<?= htmlspecialchars($form['team'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                <p class="form-field__hint"><?= htmlspecialchars($form['teamLicence'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="form-field">
                <label for="accused_driver_id">Oskarżony kierowca <span class="required">*</span></label>
                <select id="accused_driver_id" name="accused_driver_id" required>
                    <?php foreach ($form['accusedDrivers'] as $driver): ?>
                        <option value="<?= htmlspecialchars($driver['value'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($driver['label'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__row">
            <div class="form-field">
                <label for="affected_driver_id">Twój kierowca zaangażowany w zdarzenie</label>
                <select id="affected_driver_id" name="affected_driver_id">
                    <option value="">— brak —</option>
                    <?php foreach ($form['ownDrivers'] as $driver): ?>
                        <option value="<?= htmlspecialchars($driver['value'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($driver['label'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label for="infringement_article_id">Rodzaj zarzucanego naruszenia <span class="required">*</span></label>
                <select id="infringement_article_id" name="infringement_article_id" required>
                    <?php foreach ($form['articles'] as $article): ?>
                        <option><?= htmlspecialchars($article, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-field">
            <label for="description">Opis zdarzenia (z perspektywy zespołu) <span class="required">*</span></label>
            <textarea id="description" name="description" rows="5" minlength="20" maxlength="500" required aria-describedby="description-hint">Bolid nr 44 miał nałożenie przedniej osi na wejściu w zakręt 1. Bolid nr 1 skręcił agresywnie, nie zostawiając wymaganego miejsca.</textarea>
            <p class="form-field__hint" id="description-hint">20–500 znaków. Podaj sektor, prędkość na wierzchołku lub znaczniki czasu radia z telemetrii.</p>
        </div>

        <div class="form-field">
            <label for="evidence">Dołącz zdjęcie / zrzut ekranu jako dowód</label>
            <label class="dropzone" for="evidence">
                <span class="dropzone__title">Wybierz plik obrazu (PNG, JPG do 5 MB)</span>
                <span class="dropzone__text">Przeciągnij klatkę z kamery, zrzut telemetrii lub zdjęcie ze ściany boksu</span>
            </label>
            <input class="visually-hidden-input" type="file" id="evidence" name="evidence" accept="image/png,image/jpeg">
        </div>

        <div class="form-field">
            <label for="representative">Upoważniony przedstawiciel zespołu</label>
            <input type="text" id="representative" value="<?= htmlspecialchars($form['representative'], ENT_QUOTES, 'UTF-8') ?>" readonly aria-describedby="representative-hint">
            <p class="form-field__hint" id="representative-hint">Składając zgłoszenie, potwierdzasz, że protest jest zgłaszany w dobrej wierze zgodnie z Międzynarodowym Kodeksem Sportowym FIA.</p>
        </div>

        <div class="form-actions form-actions--end">
            <button class="button" type="submit">Wyślij oficjalny protest do sędziów <span aria-hidden="true">→</span></button>
        </div>
    </form>
</section>
