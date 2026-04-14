import { isDocGenModeEnabled } from './doc-gen-mode';

export function initAppsModal() {
    const openBtn = document.getElementById('apps-trigger-btn');
    const modal = document.getElementById('apps-modal');
    const docGenBtn = document.getElementById('doc-gen-toggle-btn');
    const docGenPill = document.getElementById('doc-gen-modal-pill');
    const docGenIcon = document.getElementById('doc-gen-modal-icon');

    if (!openBtn || !modal) return;

    const closeNodes = modal.querySelectorAll('[data-close="apps-modal"]');

    const updateDocGenModalState = () => {
        const active = isDocGenModeEnabled();
        if (docGenPill) {
            docGenPill.textContent = active ? 'Disable' : 'Activate';
            docGenPill.style.background = active ? 'rgba(250, 204, 21, 0.2)' : '';
            docGenPill.style.color = active ? '#eab308' : '';
            docGenPill.style.borderColor = active ? 'rgba(250, 204, 21, 0.4)' : '';
        }
        if (docGenIcon) {
            docGenIcon.style.background = active ? 'rgba(250, 204, 21, 0.2)' : 'var(--panel-stroke)';
        }
    };

    const openModal = () => {
        updateDocGenModalState();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    openBtn.addEventListener('click', openModal);

    closeNodes.forEach((node) => {
        node.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    if (docGenBtn) {
        docGenBtn.addEventListener('click', () => {
            const active = isDocGenModeEnabled();
            closeModal();
            if (active) {
                document.dispatchEvent(new CustomEvent('auditor:doc-gen-deactivated'));
            } else {
                document.dispatchEvent(new CustomEvent('auditor:doc-gen-activated'));
            }
        });
    }

    document.addEventListener('auditor:doc-gen-activated', updateDocGenModalState);
    document.addEventListener('auditor:doc-gen-deactivated', updateDocGenModalState);
}
