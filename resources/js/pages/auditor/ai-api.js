// Sends audit request with repo/PR or raw diff context.
export async function runPrAudit(auditUrl, payload) {
    return postJson(auditUrl, payload);
}

// Sends chat request using the same context and user question.
export async function runPrChat(chatUrl, payload) {
    return postJson(chatUrl, payload);
}

// Runs POST JSON with CSRF for Laravel web middleware.
async function postJson(url, payload) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const message = data?.result?.errors?.[0] || data?.message || 'AI request failed';
        throw new Error(message);
    }

    return data;
}

