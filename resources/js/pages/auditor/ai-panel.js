import { runPrAudit, runPrChat } from './ai-api';
import { renderMarkdown } from './ai-markdown';

// Controls AI audit + chat rendering using the current diff context.
export function initAiPanel() {
    const panel = document.getElementById('ai-panel');
    const runAuditBtn = document.getElementById('run-ai-audit-btn');
    const responseArea = document.getElementById('ai-response-area');
    const promptInput = document.getElementById('user-prompt');
    const sendBtn = document.getElementById('send-btn');
    if (!panel || !runAuditBtn || !responseArea || !promptInput || !sendBtn) return;

    const auditUrl = panel.dataset.aiAuditUrl;
    const chatUrl = panel.dataset.aiChatUrl;
    const isAuthenticated = panel.dataset.authenticated === '1';
    let currentContext = { repo: null, pr_number: null, diff_text: '' };
    let busy = false;

    const setBusy = (state) => {
        busy = state;
        runAuditBtn.disabled = state || !isAuthenticated;
        sendBtn.disabled = state || !isAuthenticated;
        promptInput.disabled = state || !isAuthenticated;
    };

    const appendAiMessage = (html) => {
        const node = document.createElement('div');
        node.className = 'msg ai';
        node.innerHTML = html;
        responseArea.appendChild(node);
        responseArea.scrollTop = responseArea.scrollHeight;
    };

    const appendUserMessage = (text) => {
        const node = document.createElement('div');
        node.className = 'msg user';
        node.textContent = text;
        responseArea.appendChild(node);
        responseArea.scrollTop = responseArea.scrollHeight;
    };

    const addStatus = (text, tone = 'info') => {
        if (tone === 'loading') {
            responseArea.querySelectorAll('.ai-status.is-loading').forEach((node) => node.remove());
        }
        const node = document.createElement('div');
        node.className = `msg ai ai-status is-${tone}`;
        node.textContent = text;
        responseArea.appendChild(node);
        responseArea.scrollTop = responseArea.scrollHeight;
    };

    const renderResult = (result) => {
        const errors = Array.isArray(result?.errors) ? result.errors : [];
        if (errors.length) {
            appendAiMessage(`<span class="ai-error-text">${errors.join(' ')}</span>`);
            return;
        }

        const counts = result?.severity_counts || {};
        const countText = `Critical ${counts.critical || 0}, High ${counts.high || 0}, Medium ${counts.medium || 0}, Low ${counts.low || 0}, Info ${counts.info || 0}`;
        appendAiMessage(`<strong>Risk Summary:</strong> ${countText}`);

        if (result?.summary_md) {
            appendAiMessage(renderMarkdown(result.summary_md));
        }

        if (Array.isArray(result?.findings) && result.findings.length) {
            result.findings.forEach((finding, index) => {
                const lineText = finding.line ? `:${finding.line}` : '';
                appendAiMessage(
                    `<div class="ai-finding">
                        <strong>${index + 1}. [${(finding.severity || 'info').toUpperCase()}] ${escapeText(finding.title || 'Untitled')}</strong>
                        <div>${escapeText(finding.file || 'unknown file')}${lineText}</div>
                        <div>${renderMarkdown(finding.risk_md || '')}</div>
                        <div>${renderMarkdown(finding.fix_md || '')}</div>
                    </div>`
                );
            });
        }

        if (result?.recommendations_md) {
            appendAiMessage(renderMarkdown(result.recommendations_md));
        }

        if (result?.answer_md) {
            appendAiMessage(renderMarkdown(result.answer_md));
        }
    };

    const buildPayload = () => ({
        repo: currentContext.repo || null,
        pr_number: currentContext.pr_number || null,
        diff_text: currentContext.diff_text || '',
    });

    const runAudit = async () => {
        if (!isAuthenticated) {
            addStatus('Connect GitHub to use AI audit.', 'error');
            return;
        }

        if (!currentContext.diff_text || !currentContext.diff_text.trim()) {
            addStatus('Load or paste a diff before running AI audit.', 'error');
            return;
        }

        if (!auditUrl) {
            addStatus('Audit endpoint is missing.', 'error');
            return;
        }

        setBusy(true);
        addStatus('Running AI audit...', 'loading');

        try {
            const response = await runPrAudit(auditUrl, buildPayload());
            renderResult(response?.result || {});
        } catch (error) {
            addStatus(error.message || 'Audit failed.', 'error');
        } finally {
            setBusy(false);
        }
    };

    const runChat = async (question) => {
        if (!isAuthenticated) {
            addStatus('Connect GitHub to use AI chat.', 'error');
            return;
        }

        if (!currentContext.diff_text || !currentContext.diff_text.trim()) {
            addStatus('Load or paste a diff before asking questions.', 'error');
            return;
        }

        if (!chatUrl) {
            addStatus('Chat endpoint is missing.', 'error');
            return;
        }

        setBusy(true);
        appendUserMessage(question);
        addStatus('Thinking...', 'loading');

        try {
            const response = await runPrChat(chatUrl, {
                ...buildPayload(),
                question,
            });
            renderResult(response?.result || {});
        } catch (error) {
            addStatus(error.message || 'Chat failed.', 'error');
        } finally {
            setBusy(false);
        }
    };

    document.addEventListener('auditor:diff-selected', function (event) {
        const detail = event?.detail || {};
        currentContext = {
            repo: detail.repo || null,
            pr_number: detail.prNumber || null,
            diff_text: detail.diffText || '',
        };
    });

    runAuditBtn.addEventListener('click', function () {
        runAudit();
    });

    sendBtn.addEventListener('click', function () {
        const question = promptInput.value.trim();
        if (!question) return;
        promptInput.value = '';
        runChat(question);
    });

    promptInput.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        const question = promptInput.value.trim();
        if (!question) return;
        promptInput.value = '';
        runChat(question);
    });

    if (!isAuthenticated) {
        setBusy(true);
        addStatus('Connect GitHub to enable AI audit and chat.', 'info');
    }
}

function escapeText(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');
}
