<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>
<body>
    <main>
        <form action="/register" method="post">
            <h2>Rejestracja</h2>

            <label for="name">Imię i nazwa:</label>
            <input type="text" id="name" name="name" required>

            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Zarejestruj się</button>
    </main>
</body>
</html>
