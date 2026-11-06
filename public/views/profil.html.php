<main>
    <h1>Widok profilu usera o id: <?= $profilId ?></h1>

    <section class="user-details">
        <p><strong>Nazwa użytkownika:</strong> <?= htmlspecialchars($profilName) ?></p>
        <p><strong>Adres E-mail:</strong> <?= htmlspecialchars($profilEmail) ?></p>
        <p><strong>Rola:</strong> <?= htmlspecialchars($profilRole) ?></p>
    </section>
</main>
