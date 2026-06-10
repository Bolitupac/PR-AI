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
        const currentPane = document.querySelector('.settings-pane.is-active');
        const targetPane = document.querySelector(`[data-settings-pane="${tabName}"]`);

        navButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.settingsTab === tabName);
        });

        // Smooth crossfade between panes
        if (currentPane && targetPane && currentPane !== targetPane) {
            currentPane.style.opacity = '1';
            currentPane.style.transition = 'opacity 120ms ease-out';
            currentPane.style.opacity = '0';

            setTimeout(() => {
                currentPane.classList.remove('is-active');
                currentPane.style.opacity = '';
                currentPane.style.transition = '';

                targetPane.classList.add('is-active');
                targetPane.style.opacity = '0';
                targetPane.style.transition = 'none';
                requestAnimationFrame(() => {
                    targetPane.style.transition = 'opacity 150ms ease-out';
                    targetPane.style.opacity = '1';
                    setTimeout(() => {
                        targetPane.style.opacity = '';
                        targetPane.style.transition = '';
                    }, 160);
                });
            }, 130);
        } else if (targetPane && !currentPane) {
            panes.forEach((pane) => {
                pane.classList.toggle('is-active', pane.dataset.settingsPane === tabName);
            });
        }

        const helpSubNav = document.getElementById('help-nav-sub');
        if (helpSubNav) {
            if (tabName.startsWith('help')) {
                helpSubNav.style.display = 'flex';
            } else {
                helpSubNav.style.display = 'none';
            }
        }
    };

    const openModal = (tabName = 'general') => {
        setActiveTab(tabName);
        setThemeButtonState();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
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

    // Clicking OpenAI/DeepSeek key status boxes navigates to API Keys tab
    document.addEventListener('click', (event) => {
        const keyBox = event.target.closest('.profile-key-status-box');
        if (!keyBox || !modal.contains(keyBox)) return;
        const tab = keyBox.getAttribute('data-nav-to');
        if (tab) {
            setActiveTab(tab);
        }
    });

    // ── Redeem code handler ──
    const redeemInput = document.getElementById('settings-redeem-input');
    const redeemBtn = document.getElementById('settings-redeem-btn');
    const redeemState = document.getElementById('settings-redeem-state');
    const redeemCredits = document.getElementById('settings-redeem-credits');

    redeemBtn?.addEventListener('click', async () => {
        const code = redeemInput?.value?.trim();
        if (!code) {
            if (redeemState) { redeemState.textContent = 'Enter a code first.'; redeemState.className = 'profile-api-state is-error'; }
            return;
        }

        redeemBtn.disabled = true;
        redeemBtn.textContent = 'Redeeming...';
        if (redeemState) { redeemState.textContent = ''; redeemState.className = 'profile-api-state'; }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/api/redeem', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ code }),
            });
            const data = await res.json();
            if (res.ok) {
                if (redeemState) { redeemState.textContent = data.message; redeemState.className = 'profile-api-state is-ok'; }
                if (redeemCredits) redeemCredits.innerHTML = 'Credits remaining: <strong>' + data.credits_remaining + '</strong>';
                if (redeemInput) redeemInput.value = '';
            } else {
                if (redeemState) { redeemState.textContent = data.message || 'Failed to redeem code.'; redeemState.className = 'profile-api-state is-error'; }
            }
        } catch {
            if (redeemState) { redeemState.textContent = 'Something went wrong. Try again.'; redeemState.className = 'profile-api-state is-error'; }
        }
        redeemBtn.disabled = false;
        redeemBtn.textContent = 'Redeem';
    });

    const requestedTab = new URLSearchParams(window.location.search).get('settings');
    if (requestedTab && [...panes].some((pane) => pane.dataset.settingsPane === requestedTab)) {
        openModal(requestedTab);
        const url = new URL(window.location.href);
        url.searchParams.delete('settings');
        window.history.replaceState({}, '', url.toString());
    }
}
