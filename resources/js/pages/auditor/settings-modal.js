// Controls opening, closing, and section switching for the settings modal.
export function initSettingsModal() {
    const openBtn = document.getElementById('sidebar-settings-btn');
    const modal = document.getElementById('settings-modal');
    const sidebarThemeBtn = document.getElementById('theme-toggle-btn');

    if (!openBtn || !modal) return;

    const closeNodes = modal.querySelectorAll('[data-close="settings-modal"]');
    const navButtons = modal.querySelectorAll('[data-settings-tab]');
    const panes = modal.querySelectorAll('[data-settings-pane]');
    const themeButtons = modal.querySelectorAll('[data-theme-select]');

    const setThemeButtonState = () => {
        const activeTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        themeButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.themeSelect === activeTheme);
        });
    };

    const applyThemeFromSettings = (target) => {
        if (target === 'toggle') {
            if (sidebarThemeBtn) {
                sidebarThemeBtn.click();
            }
            setThemeButtonState();
            return;
        }

        const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        if (target !== 'dark' && target !== 'light') return;
        if (target === current) {
            setThemeButtonState();
            return;
        }

        if (sidebarThemeBtn) {
            sidebarThemeBtn.click();
        } else {
            document.documentElement.setAttribute('data-theme', target);
            localStorage.setItem('auditor-theme', target);
            document.dispatchEvent(new CustomEvent('auditor:theme-changed', { detail: { theme: target } }));
        }

        setThemeButtonState();
    };

    const setActiveTab = (tabName) => {
        navButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.settingsTab === tabName);
        });

        panes.forEach((pane) => {
            pane.classList.toggle('is-active', pane.dataset.settingsPane === tabName);
        });
    };

    const openModal = (tabName = 'general') => {
        setActiveTab(tabName);
        setThemeButtonState();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    navButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setActiveTab(button.dataset.settingsTab);
        });
    });

    openBtn.addEventListener('click', () => {
        openModal('general');
    });

    themeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            applyThemeFromSettings(button.dataset.themeSelect);
        });
    });

    closeNodes.forEach((node) => {
        node.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    document.addEventListener('auditor:theme-changed', setThemeButtonState);

    document.addEventListener('auditor:open-settings', (event) => {
        openModal(event?.detail?.tab || 'general');
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href="#settings-vcs"]');
        if (!link) return;
        event.preventDefault();
        openModal('vcs');
    });
}
