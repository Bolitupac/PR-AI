const THEME_KEY = 'auditor-theme';

// Handles dark/light mode toggle and persists user choice.
export function initThemeToggle() {
    const root = document.documentElement;
    const btn = document.getElementById('theme-toggle-btn');
    if (!btn) return;
    let switchTimer = null;
    let logoSpinTimer = null;
    let logoSpinReverseTimer = null;

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
        if (current === 'dark' && next === 'light') {
            root.classList.add('logo-spin-on-switch');
            if (logoSpinTimer) {
                clearTimeout(logoSpinTimer);
            }
            logoSpinTimer = setTimeout(() => {
                root.classList.remove('logo-spin-on-switch');
                logoSpinTimer = null;
            }, 560);
        }
        if (current === 'light' && next === 'dark') {
            root.classList.add('logo-spin-on-switch-reverse');
            if (logoSpinReverseTimer) {
                clearTimeout(logoSpinReverseTimer);
            }
            logoSpinReverseTimer = setTimeout(() => {
                root.classList.remove('logo-spin-on-switch-reverse');
                logoSpinReverseTimer = null;
            }, 560);
        }
        applyTheme(next);
        localStorage.setItem(THEME_KEY, next);
    });
}
