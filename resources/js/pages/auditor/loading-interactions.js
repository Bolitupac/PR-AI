import { setButtonLoading } from './button-loading';

// Adds loading states to form submits and navigation links.
export function initLoadingInteractions() {
    document.querySelectorAll('form[data-loading-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
            if (!submitter) return;
            const text = submitter.dataset.loadingText || 'Loading';
            setButtonLoading(submitter, true, text);
        });
    });

    document.querySelectorAll('a[data-loading-link]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (event.defaultPrevented) return;
            if (event.button !== 0) return;
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#')) return;

            event.preventDefault();
            const text = link.dataset.loadingText || 'Loading';
            setButtonLoading(link, true, text);
            window.location.href = href;
        });
    });
}
