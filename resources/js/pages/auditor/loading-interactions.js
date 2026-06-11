import { setButtonLoading } from './button-loading';

// Adds loading states to form submits and navigation links.
export function initLoadingInteractions() {
    if (document.body.dataset.loadingInteractionsBound === 'true') return;
    document.body.dataset.loadingInteractionsBound = 'true';

    // Clear any stale loading spinners when the page is restored from
    // the browser's back-forward cache (bfcache).
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            document.querySelectorAll('.is-btn-loading').forEach(btn => {
                setButtonLoading(btn, false);
            });
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-loading-form]');
        if (!form) return;

        const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
        if (!submitter) return;

        const text = submitter.dataset.loadingText || 'Loading';
        setButtonLoading(submitter, true, text);
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-loading-link]');
        if (link) {
            if (event.defaultPrevented) return;
            if (event.button !== 0) return;
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

            const href = link.getAttribute('href');
            const target = link.getAttribute('target');
            if (!href || href.startsWith('#') || target === '_blank') return;

            event.preventDefault();
            setButtonLoading(link, true, link.dataset.loadingText || 'Loading');
            window.location.href = href;
            return;
        }

        const button = event.target.closest('button[data-page-loading-href]');
        if (!button) return;
        if (event.defaultPrevented) return;
        if (button.disabled) return;

        const href = button.dataset.pageLoadingHref;
        if (!href || href.startsWith('#')) return;

        event.preventDefault();
        setButtonLoading(button, true, button.dataset.loadingText || 'Loading');
        window.location.href = href;
    });
}
