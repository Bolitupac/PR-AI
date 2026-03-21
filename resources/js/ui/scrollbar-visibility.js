const SCROLL_IDLE_DELAY_MS = 2500;

const SCROLLABLE_SELECTORS = [
    '.chat-demo-list',
    '.imports-history-list',
    '.imports-repo-list',
    '.repo-pr-list',
    '.d2h-file-list-wrapper',
    '.d2h-files-diff',
];

export function initScrollbarVisibility() {
    const seen = new WeakSet();

    const attach = (element) => {
        if (!element || seen.has(element)) return;
        seen.add(element);
        element.classList.add('auto-hide-scrollbar');

        let hideTimer = null;

        const showScrollbar = () => {
            element.classList.add('is-scrolling');
            if (hideTimer) {
                window.clearTimeout(hideTimer);
            }
            hideTimer = window.setTimeout(() => {
                element.classList.remove('is-scrolling');
            }, SCROLL_IDLE_DELAY_MS);
        };

        element.addEventListener('scroll', showScrollbar, { passive: true });
        element.addEventListener('wheel', showScrollbar, { passive: true });
        element.addEventListener('touchmove', showScrollbar, { passive: true });
    };

    SCROLLABLE_SELECTORS.forEach((selector) => {
        document.querySelectorAll(selector).forEach(attach);
    });

    const observer = new MutationObserver(() => {
        SCROLLABLE_SELECTORS.forEach((selector) => {
            document.querySelectorAll(selector).forEach(attach);
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}
