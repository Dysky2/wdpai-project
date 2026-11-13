<main class="auth-page">
    <section class="auth-card" aria-labelledby="login-heading">
        <div class="auth-intro">
            <a class="brand brand--auth" href="/" aria-label="F1 Stewards, strona główna">
                <span class="brand-mark">F1</span>
                <span>
                    <strong>STEWARDS</strong>
                    <small>CONTROL ROOM</small>
                </span>
            </a>
            <div class="auth-intro-copy">
                <p class="eyebrow eyebrow--light">FIA / RACE CONTROL</p>
                <h1>Decyzje, które<br><em>kształtują</em> wyścig.</h1>
                <p>Uzyskaj dostęp do centrum obsługi incydentów i orzeczeń sędziowskich.</p>
            </div>
            <span class="auth-watermark" aria-hidden="true">01</span>
        </div>

        <div class="auth-form-panel">
            <p class="eyebrow">WITAJ PONOWNIE</p>
            <h2 id="login-heading">Zaloguj się do panelu</h2>
            <p class="auth-lead">Wprowadź dane, aby kontynuować pracę.</p>

            <form class="auth-form" action="/login" method="post">
                <div class="form-field">
                    <label for="username">Nazwa użytkownika</label>
                    <input type="text" id="username" name="username" autocomplete="username" placeholder="np. m.kowalska" required>
                </div>

                <div class="form-field">
                    <div class="form-label-row">
                        <label for="password">Hasło</label>
                        <a class="text-link" href="/register">Nie masz konta?</a>
                    </div>
                    <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Wprowadź hasło" required>
                </div>

                <button class="auth-submit" type="submit">Zaloguj się <span aria-hidden="true">→</span></button>
            </form>

            <p class="auth-switch">Nowy użytkownik? <a class="text-link" href="/register">Utwórz konto</a></p>
        </div>
    </section>
</main>
