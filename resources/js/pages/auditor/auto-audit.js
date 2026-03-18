import { createChatStatus } from './chat-status';
import { renderChatMarkdown } from './chat-markdown';
import { renderMermaidIn } from './mermaid';
import { chatContextStore } from './chat-context-store';
import { buildAuditMetadata, stripLeadingAuditTitle } from './audit-metadata';

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
            renderMermaidIn(message);
        } else {
            message.textContent = text;
        }
        responseArea.appendChild(message);
        responseArea.scrollTop = responseArea.scrollHeight;
        return message;
    };

    const appendAuditHeader = (auditMeta) => {
        if (!auditMeta?.auditTitle) return null;
        const node = document.createElement('div');
        node.className = 'msg ai audit-run-header';
        const title = document.createElement('h3');
        title.textContent = auditMeta.auditTitle;
        node.appendChild(title);

        if (auditMeta.subtitle) {
            const subtitle = document.createElement('p');
            subtitle.textContent = auditMeta.subtitle;
            node.appendChild(subtitle);
        }

        responseArea.appendChild(node);
        responseArea.scrollTop = responseArea.scrollHeight;
        return node;
    };

    const appendScoreCard = (meta = null, auditMeta = null) => {
        if (!meta) return;
        const scoreToRiskClass = (score) => {
            if (!Number.isInteger(score)) return 'is-risk';
            if (score >= 80) return 'is-risk-critical';
            if (score >= 60) return 'is-risk-high';
            if (score >= 35) return 'is-risk-medium';
            return 'is-risk-low';
        };

        const changeType = String(meta.change_type || 'neutral').toLowerCase();
        const riskLevel = String(meta.risk_level || 'medium').toLowerCase();
        const riskScore = Number.isInteger(meta.risk_score) ? Math.max(0, Math.min(100, meta.risk_score)) : null;
        const securityScore = Number.isInteger(meta.security_score) ? Math.max(0, Math.min(100, meta.security_score)) : null;
        const scalabilityScore = Number.isInteger(meta.scalability_score) ? Math.max(0, Math.min(100, meta.scalability_score)) : null;
        const reliabilityScore = Number.isInteger(meta.reliability_score) ? Math.max(0, Math.min(100, meta.reliability_score)) : null;
        const suggestion = String(meta.suggestion || 'review_then_merge').toLowerCase();
        const auditKind = String(meta.audit_kind || auditMeta?.auditKind || '').toLowerCase();
        const auditStatus = String(meta.audit_status || auditMeta?.auditStatus || '').toLowerCase();
        const toneClass = changeType === 'upgrade' ? 'is-upgrade' : (changeType === 'downgrade' ? 'is-downgrade' : 'is-neutral');
        const riskClass = ['low', 'medium', 'high', 'critical'].includes(riskLevel) ? `is-risk-${riskLevel}` : 'is-risk-medium';
        const isHistoricalAudit = auditKind === 'commit_audit' || auditStatus === 'merged';
        const isBranchAudit = auditKind === 'branch_audit';
        const suggestionLabel = isHistoricalAudit ? 'Assessment' : (isBranchAudit ? 'Recommendation' : 'Suggestion');
        const suggestionText = isHistoricalAudit
            ? (suggestion === 'merge' ? 'Stable after review' : (suggestion === 'dont_merge' ? 'Follow-up required' : 'Monitor and review'))
            : isBranchAudit
                ? (suggestion === 'merge' ? 'Ready for merge' : (suggestion === 'dont_merge' ? 'Revise before merge' : 'Review before merge'))
                : (suggestion === 'merge' ? 'Merge' : (suggestion === 'dont_merge' ? "Don\'t merge" : 'Review then merge'));
        const scoreWidth = riskScore === null ? 0 : riskScore;
        const securityClass = scoreToRiskClass(securityScore);
        const scalabilityClass = scoreToRiskClass(scalabilityScore);
        const reliabilityClass = scoreToRiskClass(reliabilityScore);

        const card = document.createElement('div');
        card.className = 'msg ai audit-scorecard';
        card.innerHTML = `
            <div class="audit-scorecard-header">
                <span class="audit-chip ${toneClass}">Change: ${changeType}</span>
                <span class="audit-chip is-suggestion">${suggestionLabel}: ${suggestionText}</span>
            </div>
            <table class="audit-score-table">
                <tbody>
                    <tr>
                        <th>Security</th>
                        <td><span class="audit-pill ${securityClass}">${securityScore !== null ? `${securityScore}/100` : 'N/A'}</span></td>
                    </tr>
                    <tr>
                        <th>Scalability</th>
                        <td><span class="audit-pill ${scalabilityClass}">${scalabilityScore !== null ? `${scalabilityScore}/100` : 'N/A'}</span></td>
                    </tr>
                    <tr>
                        <th>Reliability</th>
                        <td><span class="audit-pill ${reliabilityClass}">${reliabilityScore !== null ? `${reliabilityScore}/100` : 'N/A'}</span></td>
                    </tr>
                </tbody>
            </table>
            <div class="audit-risk-footer ${riskClass}">
                Risk: ${riskLevel}${riskScore !== null ? ` (${riskScore}/100)` : ''}
            </div>
        `;
        responseArea.appendChild(card);
        responseArea.scrollTop = responseArea.scrollHeight;
    };

    const stripAuditMeta = (text) => {
        if (!text) return '';
        return String(text).replace(/\[AUDIT_META\][\s\S]*?\[\/AUDIT_META\]/gi, '').trim();
    };

    const parseSseBlock = (blockText) => {
        const lines = String(blockText || '').split('\n');
        let eventName = 'message';
        const dataParts = [];

        for (const line of lines) {
            if (line.startsWith('event:')) {
                eventName = line.slice(6).trim() || 'message';
                continue;
            }
            if (line.startsWith('data:')) {
                dataParts.push(line.slice(5).trim());
            }
        }

        const payloadRaw = dataParts.join('\n');
        let payload = {};
        try {
            payload = payloadRaw ? JSON.parse(payloadRaw) : {};
        } catch {
            payload = {};
        }

        return { eventName, payload, payloadRaw };
    };

    document.addEventListener('auditor:diff-selected', async (event) => {
        const detail = event?.detail || {};
        const diffText = detail.diffText || '';
        if (!diffText.trim()) return;

        hideEmptyState();

        const source = detail.source || 'upload';
        const auditMeta = buildAuditMetadata(detail);
        const model = modelSelect?.value || '';
        const anchor = appendMessage(`Auto-auditing ${source} diff...`, 'user');
        const status = createChatStatus({ container: responseArea, anchorNode: anchor });

        status.set('Preparing audit payload...');
        status.set('Saving debug snapshot...');
        status.startDots('Sending audit request to backend');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/api/ai/audit-diff-stream', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'text/event-stream',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    source,
                    repo: detail.repo || null,
                    pr_number: detail.prNumber || null,
                    compare_type: detail.compareType || null,
                    base_branch: detail.baseBranch || null,
                    head_branch: detail.headBranch || null,
                    pr_title: detail.prTitle || auditMeta.prTitle || null,
                    audit_title: auditMeta.auditTitle || null,
                    audit_kind: auditMeta.auditKind || null,
                    audit_status: auditMeta.auditStatus || null,
                    file_name: detail.name || null,
                    diff_text: diffText,
                    model: model || undefined,
                }),
            });

            status.set('Backend responded.');
            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                status.markError('Audit failed.');
                appendMessage(data?.message || 'Audit request failed.', 'ai');
                return;
            }

            status.set('Rendering AI audit...');
            appendAuditHeader(auditMeta);
            const reader = res.body?.getReader?.();
            if (!reader) {
                status.markError('Audit failed.');
                appendMessage('Could not read audit stream.', 'ai');
                return;
            }

            const replyNode = appendMessage('', 'ai');
            const decoder = new TextDecoder('utf-8');
            let fullReply = '';
            let doneMeta = null;
            let buffer = '';
            let lastMermaidRender = 0;

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                buffer += decoder.decode(value, { stream: true });

                let splitPos = buffer.search(/\r?\n\r?\n/);
                while (splitPos !== -1) {
                    const block = buffer.slice(0, splitPos);
                    const sepMatch = buffer.match(/\r?\n\r?\n/);
                    const sepLen = sepMatch ? sepMatch[0].length : 2;
                    buffer = buffer.slice(splitPos + sepLen);

                    const { eventName, payload, payloadRaw } = parseSseBlock(block);
                    if (payloadRaw === '[DONE]') {
                        break;
                    }

                    if (eventName === 'token' || eventName === 'message') {
                        const token = String(payload?.text ?? payload?.choices?.[0]?.delta?.content ?? '');
                        if (token) {
                            fullReply += token;
                            replyNode.innerHTML = renderChatMarkdown(
                                stripLeadingAuditTitle(stripAuditMeta(fullReply), auditMeta.auditTitle)
                            );
                            
                            const now = Date.now();
                            if (fullReply.includes('```mermaid') && (now - lastMermaidRender > 800)) {
                                renderMermaidIn(replyNode);
                                lastMermaidRender = now;
                            }
                            
                            responseArea.scrollTop = responseArea.scrollHeight;
                        }
                    } else if (eventName === 'done') {
                        doneMeta = payload?.meta || null;
                    } else if (eventName === 'error') {
                        status.markError('Audit failed.');
                        replyNode.innerHTML = renderChatMarkdown(String(payload?.message || 'Audit request failed.'));
                        renderMermaidIn(replyNode);
                        return;
                    }

                    splitPos = buffer.search(/\r?\n\r?\n/);
                }
            }

            const cleanReply = stripLeadingAuditTitle(
                stripAuditMeta(fullReply || 'No audit response from AI.'),
                auditMeta.auditTitle
            );
            replyNode.innerHTML = renderChatMarkdown(cleanReply);
            renderMermaidIn(replyNode);
            chatContextStore.push('assistant', `Audit summary:\n${cleanReply}`);
            appendScoreCard(doneMeta, auditMeta);
            status.markSuccess('Audit complete.');
            status.remove(700);
        } catch (error) {
            status.markError('Audit failed.');
            appendMessage('Could not reach audit service.', 'ai');
        }
    });
}
