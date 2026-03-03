// Toggles a consistent loading spinner state for action buttons.
export function setButtonLoading(button, isLoading, text = 'Loading') {
    if (!button) return;

    if (isLoading) {
        if (!button.dataset.loadingOriginalHtml) {
            button.dataset.loadingOriginalHtml = button.innerHTML;
        }
        button.classList.add('is-btn-loading');
        button.disabled = true;
        button.innerHTML = `<span class="btn-spinner" aria-hidden="true"></span><span>${text}</span>`;
        return;
    }

    button.classList.remove('is-btn-loading');
    button.disabled = false;
    if (button.dataset.loadingOriginalHtml) {
        button.innerHTML = button.dataset.loadingOriginalHtml;
        delete button.dataset.loadingOriginalHtml;
    }
}

