<!-- Mobile redirect overlay — shown only on small screens -->
<div class="mobile-redirect-overlay" id="mobile-redirect-overlay" style="display:none;">
    <div class="mobile-redirect-card">
        <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo" class="mobile-redirect-logo">
        <span class="mobile-redirect-tag">Mobile</span>
        <h2>We're working on it</h2>
        <p>
            Sorry! The mobile responsive mode for this page is still being designed.
            PR ai is best experienced on a desktop or laptop screen right now.
        </p>
        <div class="mobile-redirect-actions">
            <a href="/" class="mobile-redirect-btn mobile-redirect-btn--primary">Return to home page</a>
            <button class="mobile-redirect-btn mobile-redirect-btn--ghost" id="mobile-dismiss-btn" type="button">Continue anyway</button>
        </div>
    </div>
</div>

<style>
    .mobile-redirect-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: linear-gradient(180deg, #f0ede8 0%, #efebe5 44%, #f6f3ef 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        font-family: 'Geologica', ui-sans-serif, system-ui, sans-serif;
    }

    .mobile-redirect-card {
        text-align: center;
        max-width: 420px;
        width: 100%;
    }

    .mobile-redirect-logo {
        width: 80px;
        height: 80px;
        margin: 0 auto 24px;
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
        font-size: clamp(1.6rem, 4vw, 2rem);
        font-weight: 700;
        letter-spacing: -0.04em;
        color: #12141c;
    }

    .mobile-redirect-card p {
        color: #5e6475;
        font-size: 15px;
        line-height: 1.7;
        margin-bottom: 28px;
    }

    .mobile-redirect-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .mobile-redirect-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        padding: 0 22px;
        border-radius: 999px;
        font-weight: 700;
        font-family: inherit;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .mobile-redirect-btn:hover { transform: translateY(-1px); }

    .mobile-redirect-btn--primary {
        color: white;
        background: linear-gradient(135deg, #4965ff, #304cff);
        box-shadow: 0 16px 32px rgba(73,101,255,0.22);
        border: none;
    }

    .mobile-redirect-btn--ghost {
        background: rgba(255,255,255,0.8);
        color: #5e6475;
        border: 1px solid rgba(207,208,214,0.9);
        font-size: 13px;
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
            if (overlay) overlay.style.display = 'none';
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

        // Handle resize
        window.addEventListener('resize', function() {
            if (!isMobile()) {
                hideOverlay();
            }
        });
    })();
</script>
