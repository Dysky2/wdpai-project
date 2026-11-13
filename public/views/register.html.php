<main class="auth-page">
    <section class="auth-card" aria-labelledby="register-heading">
        <div class="auth-intro">
            <a class="brand brand--auth" href="/" aria-label="F1 Stewards, strona główna">
                <span class="brand-mark">F1</span>
                <span>
                    <strong>STEWARDS</strong>
                    <small>CONTROL ROOM</small>
                </span>
            </a>
            <div class="auth-intro-copy">
                <p class="eyebrow eyebrow--light">DOŁĄCZ DO SYSTEMU</p>
                <h1>Porządek w<br><em>każdym</em> incydencie.</h1>
                <p>Stwórz konto i zarządzaj sprawami wyścigowymi z jednego miejsca.</p>
            </div>
            <span class="auth-watermark" aria-hidden="true">02</span>
        </div>

        <div class="auth-form-panel">
            <p class="eyebrow">NOWY PROFIL</p>
            <h2 id="register-heading">Utwórz konto</h2>
            <p class="auth-lead">Zacznij pracę w centrum dowodzenia FIA.</p>

            <form class="auth-form" action="/register" method="post">
                <div class="form-fields-inline">
                    <div class="form-field">
                        <label for="name">Imię</label>
                        <input type="text" id="name" name="name" autocomplete="given-name" placeholder="np. Maria" required>
                    </div>
                    <div class="form-field">
                        <label for="surname">Nazwisko</label>
                        <input type="text" id="surname" name="surname" autocomplete="family-name" placeholder="np. Kowalska" required>
                    </div>
                </div>

                <div class="form-field">
                    <label for="username">Nazwa użytkownika</label>
                    <input type="text" id="username" name="username" autocomplete="username" placeholder="np. m.kowalska" required>
                </div>

                <div class="form-field">
                    <label for="password">Hasło</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Minimum 8 znaków" minlength="8" required>
                </div>

                <button class="auth-submit" type="submit">Zarejestruj się <span aria-hidden="true">→</span></button>
            </form>

            <p class="auth-switch">Masz już konto? <a class="text-link" href="/login">Zaloguj się</a></p>
        </div>
    </section>
</main>
