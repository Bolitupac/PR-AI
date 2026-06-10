<!-- Mobile hamburger — visible only on small screens -->
<button class="mobile-hamburger" id="mobile-hamburger-btn" type="button" aria-label="Open sidebar" aria-expanded="false">
    <span class="hamburger-line"></span>
    <span class="hamburger-line"></span>
    <span class="hamburger-line"></span>
</button>

<style>
    .mobile-hamburger {
        display: none;
        position: fixed;
        top: 12px;
        left: 12px;
        z-index: 9998;
        width: 40px;
        height: 40px;
        padding: 8px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 10px;
        background: rgba(255,255,255,0.88);
        backdrop-filter: blur(12px);
        cursor: pointer;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .hamburger-line {
        display: block;
        width: 18px;
        height: 2px;
        background: #12141c;
        border-radius: 2px;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .mobile-hamburger.is-open .hamburger-line:nth-child(1) {
        transform: translateY(6px) rotate(45deg);
    }
    .mobile-hamburger.is-open .hamburger-line:nth-child(2) {
        opacity: 0;
    }
    .mobile-hamburger.is-open .hamburger-line:nth-child(3) {
        transform: translateY(-6px) rotate(-45deg);
    }

    /* Mobile sidebar overlay */
    .sidebar-mobile-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9996;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(2px);
    }

    @media (max-width: 768px) {
        .mobile-hamburger {
            display: flex;
        }

        .app-shell .sidebar-bg {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 9997;
            transform: translateX(-100%);
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 280px;
            max-width: 85vw;
            box-shadow: 4px 0 24px rgba(0,0,0,0.15);
        }

        .app-shell.sidebar-mobile-open .sidebar-bg {
            transform: translateX(0);
        }

        .app-shell.sidebar-mobile-open .sidebar-mobile-overlay {
            display: block;
        }

        .app-shell .main-workspace {
            padding-left: 8px !important;
        }

        .app-shell .ai-head {
            padding-left: 56px !important;
        }

        /* Diff viewer to top-bottom layout on mobile */
        .diff-viewer-layout {
            flex-direction: column !important;
        }

        .diff-viewer-layout .diff-tree-panel {
            width: 100% !important;
            max-height: 200px !important;
        }
    }

    html[data-theme="dark"] .mobile-hamburger {
        background: rgba(30,34,50,0.88);
        border-color: rgba(255,255,255,0.1);
    }
    html[data-theme="dark"] .hamburger-line {
        background: #e2e6f0;
    }
</style>

<script>
    (function() {
        const btn = document.getElementById('mobile-hamburger-btn');
        const shell = document.querySelector('.app-shell');
        if (!btn || !shell) return;

        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-mobile-overlay';
        overlay.setAttribute('aria-hidden', 'true');
        shell.appendChild(overlay);

        const open = () => {
            shell.classList.add('sidebar-mobile-open');
            btn.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true');
            btn.setAttribute('aria-label', 'Close sidebar');
            overlay.setAttribute('aria-hidden', 'false');
        };

        const close = () => {
            shell.classList.remove('sidebar-mobile-open');
            btn.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false');
            btn.setAttribute('aria-label', 'Open sidebar');
            overlay.setAttribute('aria-hidden', 'true');
        };

        btn.addEventListener('click', () => {
            if (shell.classList.contains('sidebar-mobile-open')) {
                close();
            } else {
                open();
            }
        });

        overlay.addEventListener('click', close);

        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && shell.classList.contains('sidebar-mobile-open')) {
                close();
            }
        });
    })();
</script>
