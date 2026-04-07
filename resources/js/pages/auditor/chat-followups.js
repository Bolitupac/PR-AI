function normalizeText(value) {
    return String(value || '').trim();
}

function shouldSuppressSuggestions(text) {
    const normalized = normalizeText(text).toLowerCase();
    if (!normalized) return true;

    return [
        'could not',
        'failed',
        'request failed',
        'no response from ai',
        'response stopped',
        'audit failed',
        'unknown error',
    ].some((phrase) => normalized.includes(phrase));
}

function sanitizeSuggestions(items) {
    if (!Array.isArray(items)) return [];

    const suggestions = [];
    for (const item of items) {
        const text = normalizeText(item);
        if (!text || text.length > 80) continue;
        if (!suggestions.includes(text)) {
            suggestions.push(text);
        }
        if (suggestions.length >= 3) break;
    }

    return suggestions;
}

export function clearFollowUpSuggestions(container) {
    container?.querySelectorAll('.msg-followups').forEach((node) => node.remove());
}

export async function fetchFollowUpSuggestions({
    assistantText,
    userText = '',
    model = '',
}) {
    if (shouldSuppressSuggestions(assistantText)) {
        return [];
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const response = await fetch('/api/ai/followups', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            user_message: userText,
            assistant_reply: assistantText,
            model: model || undefined,
        }),
        credentials: 'same-origin',
    });

    if (!response.ok) {
        return [];
    }

    const data = await response.json().catch(() => ({}));
    return sanitizeSuggestions(data?.suggestions || []);
}

export function attachFollowUpSuggestions({
    responseArea,
    messageNode,
    suggestions,
    onSelect,
}) {
    if (!responseArea || !messageNode || typeof onSelect !== 'function') return null;
    if (!Array.isArray(suggestions) || suggestions.length === 0) return null;

    const wrapper = document.createElement('div');
    wrapper.className = 'msg-followups';

    const label = document.createElement('div');
    label.className = 'msg-followups-label';
    label.textContent = 'Suggested next';
    wrapper.appendChild(label);

    const chips = document.createElement('div');
    chips.className = 'msg-followups-list';

    suggestions.forEach((suggestion) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'msg-followup-chip';
        button.textContent = suggestion;
        button.addEventListener('click', () => onSelect(suggestion));
        chips.appendChild(button);
    });

    wrapper.appendChild(chips);
    messageNode.insertAdjacentElement('afterend', wrapper);
    responseArea.scrollTop = responseArea.scrollHeight;
    return wrapper;
}
