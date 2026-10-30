<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>
<body>
    <main>
        <h1>Widok profilu usera o id: <?= $profilId ?></h1>

         <section class="user-details">
            <p><strong>Nazwa użytkownika:</strong> <?= htmlspecialchars($profilName) ?></p>
            <p><strong>Adres E-mail:</strong> <?= htmlspecialchars($profilEmail) ?></p>
            <p><strong>Rola:</strong> <?= htmlspecialchars($profilRole) ?></p>
        </section>
    </main>
</body>
</html>
