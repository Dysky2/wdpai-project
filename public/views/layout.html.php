<?php
$navItems ??= [];
$bodyClasses = trim(($bodyClass ?? '') . ($navItems !== [] ? ' has-tabbar' : ''));
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) $pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>
        (function () {
            const storageKey = 'theme';
            let savedTheme = null;

            try {
                savedTheme = localStorage.getItem(storageKey);
            } catch (error) {
                savedTheme = null;
            }

            const hasSavedTheme = savedTheme === 'light' || savedTheme === 'dark';
            const systemPrefersDark = window.matchMedia(
                '(prefers-color-scheme: dark)'
            ).matches;

            document.documentElement.dataset.theme = hasSavedTheme
                ? savedTheme
                : (systemPrefersDark ? 'dark' : 'light');
        })();
    </script>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/screens.css">
</head>
<body<?= $bodyClasses !== '' ? ' class="' . htmlspecialchars($bodyClasses, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
    <a class="skip-link" href="#main">Przejdź do treści</a>

    <?php require __DIR__ . '/partials/header.html.php'; ?>
    <?php require __DIR__ . '/partials/flash.html.php'; ?>

    <main class="page<?= !empty($pageClass) ? ' ' . htmlspecialchars((string) $pageClass, ENT_QUOTES, 'UTF-8') : '' ?>" id="main">
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/partials/footer.html.php'; ?>
    <?php require __DIR__ . '/partials/tabbar.html.php'; ?>

    <script src="/js/theme.js" defer></script>
</body>
</html>
