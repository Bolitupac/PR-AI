<!-- Mobile redirect overlay — dark blurred backdrop on small screens -->
<div class="mobile-redirect-overlay" id="mobile-redirect-overlay">
    <div class="mobile-redirect-card">
        <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo" class="mobile-redirect-logo">
        <span class="mobile-redirect-tag">Desktop Only</span>
        <h2>We're still working on mobile</h2>
        <p>
            PR ai's mobile responsive mode is still being developed and currently works best on desktop and laptop screens.
        </p>
        <div class="mobile-redirect-actions">
            <a href="/" class="mobile-redirect-btn mobile-redirect-btn--primary">Back to home page</a>
            <button class="mobile-redirect-btn mobile-redirect-btn--ghost" id="mobile-dismiss-btn" type="button">Continue anyway</button>
            <span class="mobile-redirect-hint">If this is an error, click Continue to view the page.</span>
        </div>
    </div>
</div>

<style>
    .mobile-redirect-overlay {
        display: none;
    }

    @media (max-width: 768px) {
        .mobile-redirect-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: 'Geologica', ui-sans-serif, system-ui, sans-serif;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .mobile-redirect-overlay.is-dismissed {
            display: none;
        }

        .mobile-redirect-card {
            text-align: center;
            max-width: 400px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            padding: 36px 28px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.3);
        }
    }

    .mobile-redirect-logo {
        width: 72px;
        height: 72px;
        margin: 0 auto 20px;
        display: block;
        filter: brightness(0);
        opacity: 0.85;
    }

    .mobile-redirect-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border: 1px solid rgba(73,101,255,0.16);
        border-radius: 999px;
        background: rgba(238,242,255,0.92);
        color: #304cff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 16px;
    }

    .mobile-redirect-card h2 {
        margin: 0 0 12px;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.04em;
        color: #12141c;
    }

    .mobile-redirect-card p {
        color: #5e6475;
        font-size: 14px;
        line-height: 1.7;
        margin-bottom: 24px;
    }

    .mobile-redirect-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .mobile-redirect-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        padding: 0 24px;
        border-radius: 999px;
        font-weight: 700;
        font-family: inherit;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        width: 100%;
    }

    .mobile-redirect-btn:hover { transform: translateY(-1px); }

    .mobile-redirect-btn--primary {
        color: white;
        background: linear-gradient(135deg, #4965ff, #304cff);
        box-shadow: 0 16px 32px rgba(73,101,255,0.22);
        border: none;
    }

    .mobile-redirect-btn--ghost {
        background: rgba(255,255,255,0.9);
        color: #5e6475;
        border: 1px solid rgba(207,208,214,0.9);
        font-size: 13px;
        min-height: 40px;
    }

    .mobile-redirect-hint {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 6px;
    }
</style>

<script>
    (function() {
        const MOBILE_BREAKPOINT = 768;
        const DISMISS_KEY = 'prai-mobile-dismissed';

        function isMobile() {
            return window.innerWidth <= MOBILE_BREAKPOINT;
        }

        function showOverlay() {
            const overlay = document.getElementById('mobile-redirect-overlay');
            if (overlay) overlay.style.display = 'flex';
        }

        function hideOverlay() {
            const overlay = document.getElementById('mobile-redirect-overlay');
            if (overlay) {
                overlay.style.display = 'none';
                overlay.classList.add('is-dismissed');
            }
        }

        if (isMobile() && !sessionStorage.getItem(DISMISS_KEY)) {
            document.addEventListener('DOMContentLoaded', function() {
                showOverlay();

                const dismissBtn = document.getElementById('mobile-dismiss-btn');
                if (dismissBtn) {
                    dismissBtn.addEventListener('click', function() {
                        hideOverlay();
                        sessionStorage.setItem(DISMISS_KEY, '1');
                    });
                }
            });
        }

        window.addEventListener('resize', function() {
            if (!isMobile()) {
                hideOverlay();
            }
        });
    })();
</script>
