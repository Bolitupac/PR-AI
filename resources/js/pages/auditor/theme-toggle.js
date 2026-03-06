const THEME_KEY = 'auditor-theme';

// Handles dark/light mode toggle and persists user choice.
export function initThemeToggle() {
    const root = document.documentElement;
    const btn = document.getElementById('theme-toggle-btn');
    if (!btn) return;
    let switchTimer = null;

    const runThemeTransition = () => {
        root.classList.add('theme-switching');
        if (switchTimer) {
            clearTimeout(switchTimer);
        }
        switchTimer = setTimeout(() => {
            root.classList.remove('theme-switching');
            switchTimer = null;
        }, 420);
    };

    const applyTheme = (theme) => {
        const next = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', next);
        btn.setAttribute('aria-label', next === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        document.dispatchEvent(new CustomEvent('auditor:theme-changed', { detail: { theme: next } }));
    };

    const saved = localStorage.getItem(THEME_KEY);
    applyTheme(saved || 'light');

    btn.addEventListener('click', () => {
        const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        runThemeTransition();
        applyTheme(next);
        localStorage.setItem(THEME_KEY, next);
    });
}
