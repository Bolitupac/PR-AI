const SIDEBAR_KEY = 'auditor-sidebar-expanded';

// Controls sidebar collapse/expand state with smooth label reveal.
export function initSidebar() {
    const shell = document.querySelector('.app-shell');
    const toggle = document.getElementById('sidebar-toggle-btn');
    if (!shell || !toggle) return;

    const label = toggle.querySelector('.sidebar-label');

    const apply = (expanded) => {
        shell.classList.toggle('sidebar-expanded', expanded);
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        toggle.setAttribute('aria-label', expanded ? 'Collapse sidebar' : 'Expand sidebar');
        if (label) {
            label.textContent = expanded ? 'Collapse bar' : 'Expand bar';
        }
    };

    const saved = localStorage.getItem(SIDEBAR_KEY);
    apply(saved === '1');

    toggle.addEventListener('click', () => {
        const expanded = !shell.classList.contains('sidebar-expanded');
        apply(expanded);
        localStorage.setItem(SIDEBAR_KEY, expanded ? '1' : '0');
    });

    // Profile button — open settings modal to profile section
    const profileBtn = document.getElementById('open-profile-btn');
    if (profileBtn) {
        profileBtn.addEventListener('click', (e) => {
            e.preventDefault();
            document.dispatchEvent(new CustomEvent('auditor:open-settings', {
                detail: { tab: 'profile' }
            }));
        });
    }

    // VCS sidebar buttons — open settings modal to VCS section instantly
    document.querySelectorAll('[data-vcs-sidebar-btn]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const provider = btn.dataset.vcsProvider;
            // Bitbucket and Azure are disabled / coming soon — do nothing
            if (provider === 'bitbucket' || provider === 'azure') return;
            document.dispatchEvent(new CustomEvent('auditor:open-settings', {
                detail: { tab: 'vcs' }
            }));
        });
    });
}
