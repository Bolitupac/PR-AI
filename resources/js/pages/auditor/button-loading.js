// Toggles a consistent loading spinner state for action buttons.
export function setButtonLoading(button, isLoading, text = 'Loading') {
    if (!button) return;
    const isLink = button.tagName === 'A';

    if (isLoading) {
        if (!button.dataset.loadingOriginalHtml) {
            button.dataset.loadingOriginalHtml = button.innerHTML;
        }
        if (!isLink) {
            button.dataset.loadingWasDisabled = String(Boolean(button.disabled));
        }
        button.classList.add('is-btn-loading');
        if (!isLink) {
            button.disabled = true;
        } else {
            button.setAttribute('aria-disabled', 'true');
            button.style.pointerEvents = 'none';
        }
        button.innerHTML = `<span class="btn-spinner" aria-hidden="true"></span><span>${text}</span>`;
        return;
    }

    button.classList.remove('is-btn-loading');
    if (!isLink) {
        button.disabled = button.dataset.loadingWasDisabled === 'true';
        delete button.dataset.loadingWasDisabled;
    } else {
        button.removeAttribute('aria-disabled');
        button.style.pointerEvents = '';
    }
    if (button.dataset.loadingOriginalHtml) {
        button.innerHTML = button.dataset.loadingOriginalHtml;
        delete button.dataset.loadingOriginalHtml;
    }
}
