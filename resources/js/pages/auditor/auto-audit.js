import { createChatStatus } from './chat-status';
import { renderChatMarkdown } from './chat-markdown';
import { renderMermaidIn } from './mermaid';
import { chatContextStore } from './chat-context-store';
import { buildAuditMetadata, stripLeadingAuditTitle } from './audit-metadata';
import { extractInlineComments, stripInlineCommentsBlock } from './ai-inline-comments';
import { extractAgentFixPrompt, renderAgentPromptBox, stripAgentFixPromptBlock } from './agent-prompt-box';
import { attachFollowUpSuggestions, clearFollowUpSuggestions, fetchFollowUpSuggestions } from './chat-followups';
import { sendTextToChat } from './chat-input';

// Auto-runs AI audit whenever a diff is selected from any source.
export function initAutoAudit() {
    const responseArea = document.getElementById('ai-response-area');
    const emptyState = document.getElementById('chat-empty-state');
    const modelSelect = document.getElementById('chat-model-select');
    if (!responseArea) return;

    const hideEmptyState = () => emptyState?.classList.add('is-hidden');
    let followUpRequestId = 0;

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
        if (!meta) return null;
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

        // OWASP Top 10 grid
        const owaspCategories = [
            { key: 'owasp_broken_access_control',     label: 'A01', title: 'Broken Access Control' },
            { key: 'owasp_cryptographic_failures',    label: 'A02', title: 'Cryptographic Failures' },
            { key: 'owasp_injection',                 label: 'A03', title: 'Injection' },
            { key: 'owasp_insecure_design',           label: 'A04', title: 'Insecure Design' },
            { key: 'owasp_security_misconfiguration', label: 'A05', title: 'Security Misconfiguration' },
            { key: 'owasp_vulnerable_components',     label: 'A06', title: 'Vulnerable Components' },
            { key: 'owasp_auth_failures',             label: 'A07', title: 'Auth Failures' },
            { key: 'owasp_integrity_failures',        label: 'A08', title: 'Integrity Failures' },
            { key: 'owasp_logging_failures',          label: 'A09', title: 'Logging Failures' },
            { key: 'owasp_ssrf',                      label: 'A10', title: 'SSRF' },
        ];
        const owaspStatusClass = (s) => ({ pass: 'owasp-pass', review: 'owasp-review', fail: 'owasp-fail' })[String(s||'na').toLowerCase()] || 'owasp-na';
        const owaspStatusIcon  = (s) => ({ pass: '\u2705', review: '\u26a0\ufe0f', fail: '\ud83d\udd34' })[String(s||'na').toLowerCase()] || '\u2796';
        const owaspGridHtml = owaspCategories.map(c => {
            const st = String(meta[c.key] || 'na').toLowerCase();
            return `<span class="owasp-pill ${owaspStatusClass(st)}" title="${c.title}">${owaspStatusIcon(st)} ${c.label}</span>`;
        }).join('');

        // VAPT severity counts
        const vaptCritical = Number.isInteger(meta.vapt_critical_count) ? meta.vapt_critical_count : 0;
        const vaptHigh     = Number.isInteger(meta.vapt_high_count)     ? meta.vapt_high_count     : 0;
        const vaptMedium   = Number.isInteger(meta.vapt_medium_count)   ? meta.vapt_medium_count   : 0;
        const vaptLow      = Number.isInteger(meta.vapt_low_count)      ? meta.vapt_low_count      : 0;
        const vaptInfo     = Number.isInteger(meta.vapt_info_count)     ? meta.vapt_info_count     : 0;


        const card = document.createElement('div');
        card.className = 'msg ai audit-scorecard';
        card.innerHTML = `
            <div class="audit-scorecard-header">
                <span class="audit-chip ${toneClass}">Change: ${changeType}</span>
                <span class="audit-chip is-suggestion">${suggestionLabel}: ${suggestionText}</span>
            </div>
            <div class="audit-scorecard-section-label">OWASP Top 10 (2021)</div>
            <div class="owasp-grid">${owaspGridHtml}</div>
            <div class="audit-scorecard-section-label">VAPT Findings</div>
            <div class="vapt-counts">
                <span class="vapt-badge vapt-critical" title="Critical findings">\ud83d\udd34 ${vaptCritical} Critical</span>
                <span class="vapt-badge vapt-high"     title="High findings">\ud83d\udfe0 ${vaptHigh} High</span>
                <span class="vapt-badge vapt-medium"   title="Medium findings">\ud83d\udfe1 ${vaptMedium} Medium</span>
                <span class="vapt-badge vapt-low"      title="Low findings">\ud83d\udfe4 ${vaptLow} Low</span>
                <span class="vapt-badge vapt-info"     title="Informational findings">\u26aa ${vaptInfo} Info</span>
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
        return card;
    };

    const stripAuditMeta = (text) => {
        if (!text) return '';
        return String(text).replace(/\[AUDIT_META\][\s\S]*?\[\/AUDIT_META\]/gi, '').trim();
    };

    const stripHiddenBlocks = (text) => stripAgentFixPromptBlock(stripInlineCommentsBlock(stripAuditMeta(text)));

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

    const runAutoAudit = async (detail = {}) => {
        const diffText = detail.diffText || '';
        if (!diffText.trim()) return;

        hideEmptyState();
        clearFollowUpSuggestions(responseArea);
        followUpRequestId += 1;

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
                    repo_id: detail.repoId || null,
                    project: detail.project || null,
                    workspace: detail.workspace || null,
                    organization: detail.organization || null,
                    repo_slug: detail.repoSlug || null,
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
                    conflict_payload: detail.conflictData || undefined,
                    model: model || undefined,
                }),
            });

            status.set('Backend responded.');
            if (!res.ok) {
                if (res.status === 419) {
                    status.markError('Session expired.');
                    appendMessage('Your session has expired. Please refresh the page and try again.', 'ai');
                    return;
                }
                if (res.status === 413) {
                    status.markError('Diff too large.');
                    appendMessage('⚠️ This diff is too large to process. Try auditing a specific pull request or a smaller branch instead.', 'ai');
                    return;
                }
                const contentType = res.headers.get('content-type') || '';
                let errorMessage = 'Audit request failed.';
                if (contentType.includes('application/json')) {
                    const data = await res.json().catch(() => ({}));
                    errorMessage = data?.message || errorMessage;
                }
                status.markError('Audit failed.');
                appendMessage(errorMessage, 'ai');
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
                                stripLeadingAuditTitle(stripHiddenBlocks(fullReply), auditMeta.auditTitle)
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

            const aiInlineComments = extractInlineComments(fullReply);
            const agentFix = extractAgentFixPrompt(fullReply);
            const cleanReply = stripLeadingAuditTitle(
                stripHiddenBlocks(agentFix.visibleText || fullReply || 'No audit response from AI.'),
                auditMeta.auditTitle
            );
            replyNode.innerHTML = renderChatMarkdown(cleanReply);
            renderMermaidIn(replyNode);
            if (agentFix.prompt) {
                renderAgentPromptBox(responseArea, agentFix.prompt, agentFix.title);
            }
            chatContextStore.push('assistant', `Audit summary:\n${cleanReply}`);
            const scoreCardNode = appendScoreCard(doneMeta, auditMeta);
            const requestId = ++followUpRequestId;
            const suggestions = await fetchFollowUpSuggestions({
                assistantText: cleanReply,
                userText: `Audit this ${source} diff`,
                model,
            }).catch(() => []);

            if (requestId === followUpRequestId) {
                attachFollowUpSuggestions({
                    responseArea,
                    messageNode: scoreCardNode || replyNode,
                    suggestions,
                    onSelect: (suggestion) => {
                        sendTextToChat(suggestion, { source: 'suggestion' });
                    },
                });
            }
            document.dispatchEvent(new CustomEvent('auditor:ai-comments-updated', {
                detail: { comments: aiInlineComments },
            }));
            status.markSuccess('Audit complete.');
            status.remove(700);
        } catch (error) {
            status.markError('Audit failed.');
            appendMessage('Could not reach audit service.', 'ai');
        }
    };

    document.addEventListener('auditor:diff-selected', (event) => {
        if (event?.detail?.auditKind === 'merge_conflict_audit') {
            return;
        }
        runAutoAudit(event?.detail || {});
    });

    document.addEventListener('auditor:conflicts-selected', (event) => {
        runAutoAudit(event?.detail || {});
    });
}
