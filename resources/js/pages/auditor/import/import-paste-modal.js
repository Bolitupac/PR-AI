import { validateDiffText } from '../diff-validator';

export function initImportPasteModal() {
    const modal = document.getElementById('import-paste-modal');
    const input = document.getElementById('import-paste-input');
    const state = document.getElementById('import-paste-state');
    const action = document.getElementById('import-paste-action');
    const closeNodes = document.querySelectorAll('[data-close="import-paste-modal"]');

    if (!modal || !input || !state || !action) return;

    const setState = (text, tone = 'info') => {
        state.textContent = text;
        state.classList.remove('is-info', 'is-success', 'is-error');
        state.classList.add(`is-${tone}`);
    };

    const openModal = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    action.addEventListener('click', () => {
        const diffText = input.value || '';
        const validation = validateDiffText(diffText);
        if (!validation.valid) {
            setState(validation.reason, 'error');
            return;
        }

        document.dispatchEvent(new CustomEvent('auditor:diff-selected', {
            detail: { source: 'paste', diffText },
        }));
        setState('Diff rendered. Auto audit started.', 'success');
        setTimeout(() => closeModal(), 420);
    });

    closeNodes.forEach((node) => node.addEventListener('click', closeModal));

    document.addEventListener('auditor:open-import-paste-modal', () => {
        openModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
}

