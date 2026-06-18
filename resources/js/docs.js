const THEME_KEY = 'auditor-theme';
let transitionTimer = null;

function runThemeTransition() {
    const root = document.documentElement;
    root.classList.add('docs-theme-transitioning');
    if (transitionTimer) clearTimeout(transitionTimer);
    transitionTimer = setTimeout(() => {
        root.classList.remove('docs-theme-transitioning');
        transitionTimer = null;
    }, 420);
}

function applyTheme(theme) {
    const root = document.documentElement;
    const next = theme === 'dark' ? 'dark' : 'light';
    root.setAttribute('data-theme', next);

    const icon = document.querySelector('[data-docs-theme-icon]');
    const btn = document.getElementById('docs-theme-toggle');
    if (btn) {
        const switchTo = next === 'dark' ? 'light' : 'dark';
        btn.setAttribute('aria-label', `Switch to ${switchTo} mode`);
        btn.title = `Switch to ${switchTo} mode`;
    }
    if (icon) icon.innerHTML = next === 'dark'
        ? '<path d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.071 7.071-1.414-1.414M5.343 5.343 3.929 3.929m14.142 0-1.414 1.414M5.343 18.657l-1.414 1.414" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'
        : '<path d="M20.5 14.5A8.5 8.5 0 1 1 9.5 3.5a7 7 0 0 0 11 11Z" fill="currentColor"/>';
}

function initDocsSearch() {
    const input = document.getElementById('docs-search');
    const cards = Array.from(document.querySelectorAll('.docs-card, .docs-section, .docs-note, .docs-faq-item'));

    if (!input || cards.length === 0) return;

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();
        cards.forEach((card) => {
            const text = card.textContent.toLowerCase();
            const match = query === '' || text.includes(query);
            card.hidden = !match;
        });
    });
}

function initDocsThemeToggle() {
    const btn = document.getElementById('docs-theme-toggle');
    if (!btn) return;

    const saved = localStorage.getItem(THEME_KEY) || 'light';
    applyTheme(saved);

    btn.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        runThemeTransition();
        applyTheme(next);
        localStorage.setItem(THEME_KEY, next);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDocsThemeToggle();
    initDocsSearch();
});
