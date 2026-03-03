// Controls opening and closing of the centered profile modal.
export function initProfileModal() {
    const openBtn = document.getElementById('open-profile-btn');
    const modal = document.getElementById('profile-modal');

    if (!openBtn || !modal) return;

    const closeNodes = document.querySelectorAll('[data-close="profile-modal"]');

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
}

