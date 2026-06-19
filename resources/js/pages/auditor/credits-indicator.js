/**
 * Credits indicator — inline button before the Import button
 * that shows remaining Developer Key requests or ∞ for personal keys.
 * Clicking opens Settings to the API Keys tab.
 *
 * Credits are preloaded on init and updated after each AI call.
 */

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

function renderText(data, textEl) {
    if (!data) {
        textEl.textContent = '—';
        textEl.removeAttribute('data-unlimited');
        textEl.removeAttribute('data-zero');
        return;
    }

    if (data.unlimited) {
        textEl.textContent = '∞ calls left';
        textEl.setAttribute('data-unlimited', '');
        textEl.removeAttribute('data-zero');
    } else {
        const n = data.credits_remaining ?? 0;
        textEl.textContent = n + ' call' + (n !== 1 ? 's' : '') + ' left';
        textEl.removeAttribute('data-unlimited');
        if (n === 0) {
            textEl.setAttribute('data-zero', '');
        } else {
            textEl.removeAttribute('data-zero');
        }
    }
}

async function preload(creditsUrl) {
    const provider = getCurrentProvider();
    const url = creditsUrl + '?provider=' + encodeURIComponent(provider);
    cachedData = await fetchCredits(url);
}

/**
 * Call this after each AI chat completes to refresh the credits count.
 */
export async function refreshCredits() {
    const btn = document.getElementById('ai-calls-left-btn');
    if (!btn) return;
    const creditsUrl = btn.dataset.creditsUrl;
    if (!creditsUrl) return;

    const provider = getCurrentProvider();
    const url = creditsUrl + '?provider=' + encodeURIComponent(provider);
    cachedData = await fetchCredits(url);

    const textEl = document.getElementById('ai-calls-left-text');
    if (textEl) renderText(cachedData, textEl);
}

export function initCreditsIndicator() {
    const btn = document.getElementById('ai-calls-left-btn');
    const textEl = document.getElementById('ai-calls-left-text');

    if (!btn || !textEl) return;

    const creditsUrl = btn.dataset.creditsUrl;
    if (!creditsUrl) return;

    // Preload immediately so data is ready on render
    preload(creditsUrl).then(() => {
        renderText(cachedData, textEl);
    });

    // Click opens Settings → API Keys
    btn.addEventListener('click', () => {
        document.dispatchEvent(new CustomEvent('auditor:open-settings', {
            detail: { tab: 'api-keys' },
        }));
    });

    // Refresh after provider switch
    const providerSelect = document.getElementById('chat-provider-select');
    if (providerSelect) {
        providerSelect.addEventListener('change', () => {
            preload(creditsUrl).then(() => {
                renderText(cachedData, textEl);
            });
        });
    }
}
