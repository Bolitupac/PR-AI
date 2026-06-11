/**
 * Credits indicator — three-dots button in the chat toolbar
 * that shows remaining Developer Key requests or ∞ for personal keys.
 */

let popoverVisible = false;

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function fetchCredits(url) {
    try {
        const resp = await fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!resp.ok) return null;
        return await resp.json();
    } catch {
        return null;
    }
}

function getCurrentProvider() {
    const providerSelect = document.getElementById('chat-provider-select');
    return providerSelect?.value || 'openai';
}

function renderPopoverContent(data, textEl) {
    if (!data) {
        textEl.innerHTML = '—';
        return;
    }

    if (data.unlimited) {
        textEl.innerHTML = '<span class="infinity-symbol">∞</span> Unlimited';
    } else {
        const n = data.credits_remaining ?? 0;
        textEl.innerHTML = n === 0
            ? '<span style="color:#ef4444;">0</span> left'
            : `<strong>${n}</strong> left`;
    }
}

function hidePopover(popover, btn) {
    popover.classList.remove('is-visible');
    popover.setAttribute('aria-hidden', 'true');
    btn.classList.remove('is-active');
    popoverVisible = false;
}

function showPopover(popover, btn) {
    popover.classList.add('is-visible');
    popover.setAttribute('aria-hidden', 'false');
    btn.classList.add('is-active');
    popoverVisible = true;
}

export function initCreditsIndicator() {
    const btn = document.getElementById('credits-dots-btn');
    const popover = document.getElementById('credits-popover');
    const textEl = document.getElementById('credits-popover-text');

    if (!btn || !popover || !textEl) return;

    const creditsUrl = btn.dataset.creditsUrl;
    if (!creditsUrl) return;

    let loading = false;

    const toggle = async () => {
        if (popoverVisible) {
            hidePopover(popover, btn);
            return;
        }

        // Show loading state
        showPopover(popover, btn);
        textEl.innerHTML = '...';

        if (loading) return;
        loading = true;

        const provider = getCurrentProvider();
        const url = creditsUrl + '?provider=' + encodeURIComponent(provider);
        const data = await fetchCredits(url);
        renderPopoverContent(data, textEl);
        loading = false;
    };

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggle();
    });

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
}
