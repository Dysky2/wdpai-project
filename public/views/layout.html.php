<?php
$navItems ??= [];
$bodyClasses = trim(($bodyClass ?? '') . ($navItems !== [] ? ' has-tabbar' : ''));
?>
<!doctype html>
<html lang="<?= $view->e($view->lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $view->e($view->csrfToken()) ?>">
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
    <link rel="stylesheet" href="<?= $view->e($view->asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= $view->e($view->asset('css/screens.css')) ?>">
</head>
<body<?= $bodyClasses !== '' ? ' class="' . htmlspecialchars($bodyClasses, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
    <a class="skip-link" href="#main"><?= $view->e($view->t('a11y.skip_to_content')) ?></a>

    <?php require __DIR__ . '/partials/header.html.php'; ?>
    <?php require __DIR__ . '/partials/flash.html.php'; ?>

    <main class="page<?= !empty($pageClass) ? ' ' . htmlspecialchars((string) $pageClass, ENT_QUOTES, 'UTF-8') : '' ?>" id="main" tabindex="-1">
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/partials/footer.html.php'; ?>
    <?php require __DIR__ . '/partials/tabbar.html.php'; ?>

    <script src="<?= $view->e($view->asset('js/theme.js')) ?>" defer></script>
    <script type="module" src="<?= $view->e($view->asset('js/app.js')) ?>" defer></script>
</body>
</html>
