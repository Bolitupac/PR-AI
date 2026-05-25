function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

export function renderAgentPromptBox(container, promptText, title = 'Agent fix prompt') {
    if (!container) return null;

    const safePrompt = String(promptText || '').trim();
    if (!safePrompt) return null;

    const box = document.createElement('div');
    box.className = 'agent-prompt-box';
    box.innerHTML = `
        <div class="agent-prompt-box__head">
            <strong>${escapeHtml(title)}</strong>
            <button type="button" class="agent-prompt-box__copy">Copy</button>
        </div>
        <pre class="agent-prompt-box__body">${escapeHtml(safePrompt)}</pre>
    `;

    const copyBtn = box.querySelector('.agent-prompt-box__copy');
    copyBtn?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(safePrompt);
            copyBtn.textContent = 'Copied';
            setTimeout(() => {
                copyBtn.textContent = 'Copy';
            }, 2000);
        } catch (err) {
            console.error('Copy failed', err);
            copyBtn.textContent = 'Copy failed';
        }
    });

    container.appendChild(box);
    return box;
}

export function stripAgentFixPromptBlock(text) {
    return String(text || '').replace(/\[AGENT_FIX_PROMPT\][\s\S]*?\[\/AGENT_FIX_PROMPT\]/gi, '').trim();
}

export function extractAgentFixPrompt(replyText) {
    const match = String(replyText || '').match(/\[AGENT_FIX_PROMPT\]([\s\S]*?)\[\/AGENT_FIX_PROMPT\]/i);
    if (!match) {
        return { visibleText: replyText, prompt: '', title: 'Agent fix prompt' };
    }

    const visibleText = replyText.replace(match[0], '').trim();
    const raw = match[1].trim();

    try {
        const parsed = JSON.parse(raw);
        return {
            visibleText,
            prompt: String(parsed.prompt || parsed.text || raw),
            title: String(parsed.title || 'Agent fix prompt'),
        };
    } catch {
        return { visibleText, prompt: raw, title: 'Agent fix prompt' };
    }
}
