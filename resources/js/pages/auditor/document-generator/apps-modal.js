export function initAppsModal() {
    const openBtn = document.getElementById('apps-trigger-btn');
    const modal = document.getElementById('apps-modal');
    const docGenBtn = document.getElementById('doc-gen-toggle-btn');

    if (!openBtn || !modal) return;

    const closeNodes = modal.querySelectorAll('[data-close="apps-modal"]');

    const openModal = () => {
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
            closeModal();
            document.dispatchEvent(new CustomEvent('auditor:doc-gen-activated'));
        });
    }
}
