const storageKey = 'theme';
const themeToggle = document.querySelector('.theme-toggle');
const themeIcon = themeToggle?.querySelector('i') ?? null;
const colorSchemeQuery = window.matchMedia('(prefers-color-scheme: dark)');

function getCurrentTheme() {
    return document.documentElement.dataset.theme === 'dark'
        ? 'dark'
        : 'light';
}
 
function getSavedTheme() {
    try {
        const theme = localStorage.getItem(storageKey);
        return theme === 'light' || theme === 'dark' ? theme : null;
    } catch (error) {
        return null;
    }
}

function saveTheme(theme) {
    try {
        localStorage.setItem(storageKey, theme);
    } catch (error) {
        // Motyw nadal działa w bieżącym dokumencie.
    }
}

function syncThemeToggle() {
    if (!themeToggle) {
        return;
    }

    const isDark = getCurrentTheme() === 'dark';
    themeToggle.setAttribute('aria-pressed', String(isDark));
    themeToggle.setAttribute(
        'aria-label',
        isDark ? 'Włącz jasny motyw' : 'Włącz ciemny motyw'
    );

    if (themeIcon) {
        themeIcon.textContent = isDark ? '☀' : '☾';
    }
}

function setTheme(theme, remember = true) {
    document.documentElement.dataset.theme = theme;

    if (remember) {
        saveTheme(theme);
    }

    syncThemeToggle();
}

themeToggle?.addEventListener('click', () => {
    const nextTheme = getCurrentTheme() === 'dark' ? 'light' : 'dark';
    setTheme(nextTheme);
});

colorSchemeQuery.addEventListener('change', (event) => {
    if (getSavedTheme() !== null) {
        return;
    }

    setTheme(event.matches ? 'dark' : 'light', false);
});

syncThemeToggle();
