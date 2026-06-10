// Controls opening and closing of the centered profile modal.
export function initProfileModal() {
    const openBtn = document.getElementById('open-profile-btn');
    const modal = document.getElementById('profile-modal');
    const logoutForm = document.getElementById('profile-logout-form');

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

    logoutForm?.addEventListener('submit', (event) => {
        const proceed = window.confirm('Are you sure you want to log out?');
        if (!proceed) {
            event.preventDefault();
        }
    });

    // Delete account handler — supports both the standalone profile modal and the settings modal
    const initDeleteButton = (btnId) => {
        const deleteBtn = document.getElementById(btnId);
        if (!deleteBtn) return;

        deleteBtn.addEventListener('click', async () => {
            const confirmed = window.confirm(
                'Are you sure you want to permanently delete your profile?\n\n' +
                'This will delete all your data, including chat history and API keys. This action cannot be undone.'
            );
            if (!confirmed) return;

            const doubleConfirmed = window.confirm(
                'This is irreversible. Are you absolutely sure you want to delete your account?'
            );
            if (!doubleConfirmed) return;

            const url = deleteBtn.getAttribute('data-delete-url');
            if (!url) return;

            deleteBtn.disabled = true;
            deleteBtn.textContent = 'Deleting...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (response.ok && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Something went wrong. Please try again.');
                    deleteBtn.disabled = false;
                    deleteBtn.textContent = 'Delete Profile';
                }
            } catch (err) {
                console.error('Account deletion failed:', err);
                alert('Something went wrong. Please try again.');
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'Delete Profile';
            }
        });
    };

    initDeleteButton('profile-delete-btn');
    initDeleteButton('settings-profile-delete-btn');
}
