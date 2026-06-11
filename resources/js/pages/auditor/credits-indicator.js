/**
 * Credits indicator — three-dots button in the top-right header
 * that shows remaining Developer Key requests or ∞ for personal keys.
 *
 * Credits are preloaded on init and updated after each AI call.
 */

let popoverVisible = false;
let cachedData = null;

function fetchCredits(url) {
    return fetch(url, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    })
        .then(r => (r.ok ? r.json() : null))
        .catch(() => null);
}

function getCurrentProvider() {
    const providerSelect = document.getElementById('chat-provider-select');
    return providerSelect?.value || 'openai';
}

function renderPopoverContent(data, textEl) {
    if (!data) {
        textEl.innerHTML = '<span style="color:var(--text-soft)">AI calls left: —</span>';
        return;
    }

    if (data.unlimited) {
        textEl.innerHTML = 'AI calls left: <span class="infinity-symbol">∞</span>';
    } else {
        const n = data.credits_remaining ?? 0;
        if (n === 0) {
            textEl.innerHTML = 'AI calls left: <span style="color:#ef4444;font-weight:700">0</span>';
        } else {
            textEl.innerHTML = 'AI calls left: <strong>' + n + '</strong>';
        }
    }
}

function positionPopover(popover, btn) {
    const rect = btn.getBoundingClientRect();
    popover.style.position = 'fixed';
    popover.style.top = (rect.bottom + 8) + 'px';
    popover.style.left = (rect.left + rect.width / 2) + 'px';
    popover.style.transform = 'translateX(-50%) translateY(-4px)';
    popover.style.bottom = 'auto';
}

function hidePopover(popover, btn) {
    popover.classList.remove('is-visible');
    popover.setAttribute('aria-hidden', 'true');
    btn.classList.remove('is-active');
    popoverVisible = false;
}

function showPopover(popover, btn, textEl) {
    positionPopover(popover, btn);
    renderPopoverContent(cachedData, textEl);
    popover.classList.add('is-visible');
    popover.setAttribute('aria-hidden', 'false');
    btn.classList.add('is-active');
    popoverVisible = true;
}

async function preload(creditsUrl) {
    const provider = getCurrentProvider();
    const url = creditsUrl + '?provider=' + encodeURIComponent(provider);
    cachedData = await fetchCredits(url);
}

/**
 * Call this after each AI chat completes to refresh the credits count.
 * Pass the provider used for the chat, or omit to detect from the page.
 */
export async function refreshCredits() {
    const btn = document.getElementById('credits-dots-btn');
    if (!btn) return;
    const creditsUrl = btn.dataset.creditsUrl;
    if (!creditsUrl) return;

    const provider = getCurrentProvider();
    const url = creditsUrl + '?provider=' + encodeURIComponent(provider);
    cachedData = await fetchCredits(url);

    // If popover is visible, update it live
    if (popoverVisible) {
        const textEl = document.getElementById('credits-popover-text');
        if (textEl) renderPopoverContent(cachedData, textEl);
    }
}

export function initCreditsIndicator() {
    const btn = document.getElementById('credits-dots-btn');
    const popover = document.getElementById('credits-popover');
    const textEl = document.getElementById('credits-popover-text');

    if (!btn || !popover || !textEl) return;

    // Move popover to body to avoid overflow clipping
    document.body.appendChild(popover);

    const creditsUrl = btn.dataset.creditsUrl;
    if (!creditsUrl) return;

    // Preload immediately so data is ready on first click
    preload(creditsUrl);

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (popoverVisible) {
            hidePopover(popover, btn);
        } else {
            showPopover(popover, btn, textEl);
        }
    });

    // Reposition on scroll/resize
    window.addEventListener('scroll', () => {
        if (popoverVisible) positionPopover(popover, btn);
    }, { passive: true });
    window.addEventListener('resize', () => {
        if (popoverVisible) positionPopover(popover, btn);
    }, { passive: true });

    // Dismiss on outside click
    document.addEventListener('click', (e) => {
        if (popoverVisible && !btn.contains(e.target) && !popover.contains(e.target)) {
            hidePopover(popover, btn);
        }
    });

    // Dismiss on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && popoverVisible) {
            hidePopover(popover, btn);
        }
    });

    // Refresh after provider switch
    const providerSelect = document.getElementById('chat-provider-select');
    if (providerSelect) {
        providerSelect.addEventListener('change', () => {
            preload(creditsUrl);
        });
    }
}
