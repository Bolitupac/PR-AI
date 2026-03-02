import { createChatStatus } from './chat-status';
import { renderChatMarkdown } from './chat-markdown';
import { chatContextStore } from './chat-context-store';

// Auto-runs AI audit whenever a diff is selected from any source.
export function initAutoAudit() {
    const responseArea = document.getElementById('ai-response-area');
    const emptyState = document.getElementById('chat-empty-state');
    const modelSelect = document.getElementById('chat-model-select');
    if (!responseArea) return;

    const hideEmptyState = () => emptyState?.classList.add('is-hidden');

    const appendMessage = (text, role) => {
        const message = document.createElement('div');
        message.className = `msg ${role}`;
        if (role === 'ai') {
            message.innerHTML = renderChatMarkdown(text);
        } else {
            message.textContent = text;
        }
        responseArea.appendChild(message);
        responseArea.scrollTop = responseArea.scrollHeight;
        return message;
    };

    const appendScoreCard = (meta = null) => {
        if (!meta) return;
        const changeType = String(meta.change_type || 'neutral').toLowerCase();
        const riskLevel = String(meta.risk_level || 'medium').toLowerCase();
        const riskScore = Number.isInteger(meta.risk_score) ? Math.max(0, Math.min(100, meta.risk_score)) : null;
        const suggestion = String(meta.suggestion || 'review_then_merge').toLowerCase();
        const toneClass = changeType === 'upgrade' ? 'is-upgrade' : (changeType === 'downgrade' ? 'is-downgrade' : 'is-neutral');
        const riskClass = ['low', 'medium', 'high', 'critical'].includes(riskLevel) ? `is-risk-${riskLevel}` : 'is-risk-medium';
        const suggestionText = suggestion === 'merge'
            ? 'Merge'
            : (suggestion === 'dont_merge' ? "Don't merge" : 'Review then merge');
        const scoreWidth = riskScore === null ? 0 : riskScore;

        const card = document.createElement('div');
        card.className = 'msg ai audit-scorecard';
        card.innerHTML = `
            <div class="audit-scorecard-top">
                <span class="meta-pill ${toneClass}">Change: ${changeType}</span>
                <span class="meta-pill ${riskClass}">Risk: ${riskLevel}${riskScore !== null ? ` (${riskScore}/100)` : ''}</span>
                <span class="meta-pill is-suggestion">Suggestion: ${suggestionText}</span>
            </div>
            <div class="audit-score-track">
                <div class="audit-score-fill ${riskClass}" style="width: ${scoreWidth}%"></div>
            </div>
            <div class="audit-score-caption">Risk score ${riskScore !== null ? `${riskScore}/100` : 'N/A'}</div>
        `;
        responseArea.appendChild(card);
        responseArea.scrollTop = responseArea.scrollHeight;
    };

    const stripAuditMeta = (text) => {
        if (!text) return '';
        return String(text).replace(/\[AUDIT_META\][\s\S]*?\[\/AUDIT_META\]/gi, '').trim();
    };

    document.addEventListener('auditor:diff-selected', async (event) => {
        const detail = event?.detail || {};
        const diffText = detail.diffText || '';
        if (!diffText.trim()) return;

        hideEmptyState();

        const source = detail.source || 'upload';
        const model = modelSelect?.value || '';
        const anchor = appendMessage(`Auto-auditing ${source} diff...`, 'user');
        const status = createChatStatus({ container: responseArea, anchorNode: anchor });

        status.set('Preparing audit payload...');
        status.set('Saving debug snapshot...');
        status.startDots('Sending audit request to backend');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/api/ai/audit-diff', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    source,
                    repo: detail.repo || null,
                    pr_number: detail.prNumber || null,
                    file_name: detail.name || null,
                    diff_text: diffText,
                    model: model || undefined,
                }),
            });

            status.set('Backend responded.');
            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                status.markError('Audit failed.');
                appendMessage(data?.message || 'Audit request failed.', 'ai');
                return;
            }

            status.set('Rendering AI audit...');
            const cleanReply = stripAuditMeta(data?.reply || 'No audit response from AI.');
            appendMessage(cleanReply, 'ai');
            chatContextStore.push('assistant', `Audit summary:\n${cleanReply}`);
            appendScoreCard(data?.meta || null);
            status.markSuccess('Audit complete.');
            status.remove(700);
        } catch (error) {
            status.markError('Audit failed.');
            appendMessage('Could not reach audit service.', 'ai');
        }
    });
}
