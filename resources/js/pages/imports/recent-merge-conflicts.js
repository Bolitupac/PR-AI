import * as API from './api';
import { buildMergeConflictAuditPayload, startAuditSession } from './audit-session';
import { setButtonLoading } from '../auditor/button-loading';

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function renderEmptyState(panel, message) {
    panel.innerHTML = `
        <li class="imports-history-item" style="color: var(--text-soft); font-size: 12px;">
            ${escapeHtml(message)}
        </li>
    `;
}

function renderConflicts(panel, conflicts, provider) {
    if (!Array.isArray(conflicts) || conflicts.length === 0) {
        renderEmptyState(panel, 'No open merge conflicts found.');
        return;
    }

    panel.innerHTML = conflicts.map((item) => `
        <li class="imports-history-item imports-activity-item imports-conflict-item">
            <div class="imports-activity-flex">
                <div class="imports-activity-main">
                    <span class="imports-activity-badge imports-conflict-badge">Conflict</span>
                    <span class="imports-activity-title">${escapeHtml(item.title || 'Merge conflict')}</span>
                </div>
                <div class="imports-activity-meta">
                    <span class="imports-activity-pr-number">#${escapeHtml(item.number ?? '—')}</span>
                    <span class="imports-activity-repo">${escapeHtml(item.repo || '')}</span>
                    <span class="imports-activity-time">${escapeHtml(item.base_ref || '')} ← ${escapeHtml(item.head_ref || '')}</span>
                </div>
                <button class="imports-activity-action-btn imports-conflict-import-btn" type="button"
                    data-repo="${escapeHtml(item.repo || '')}"
                    data-repo-id="${escapeHtml(item.repo_id || '')}"
                    data-pr="${escapeHtml(item.number || '')}"
                    data-title="${escapeHtml(item.title || '')}"
                    data-base="${escapeHtml(item.base_ref || '')}"
                    data-head="${escapeHtml(item.head_ref || '')}"
                    data-provider="${escapeHtml(provider || 'github')}"
                    aria-label="Import merge conflict">
                    Import
                </button>
            </div>
        </li>
    `).join('');
}

export async function initRecentMergeConflictsPanel(apiBase, provider, setImportStatus) {
    const panel = document.getElementById('recent-merge-conflicts-list');
    if (!panel) return;

    renderEmptyState(panel, 'Loading merge conflicts...');

    try {
        const conflicts = await API.fetchRecentMergeConflicts(apiBase, provider);
        renderConflicts(panel, conflicts, provider);
    } catch (error) {
        if (error?.status === 401) {
            renderEmptyState(panel, `Connect ${provider} to load merge conflicts.`);
        } else if (error?.status === 501) {
            renderEmptyState(panel, error.message || 'Merge conflicts are not supported for this provider yet.');
        } else {
            renderEmptyState(panel, 'Failed to load merge conflicts.');
        }
    }

    if (panel.dataset.boundClick === 'true') {
        return;
    }

    panel.dataset.boundClick = 'true';
    panel.addEventListener('click', (event) => {
        const button = event.target.closest('.imports-conflict-import-btn');
        if (!button) return;

        event.preventDefault();
        event.stopPropagation();

        const repo = button.dataset.repo || '';
        const prNumber = button.dataset.pr || '';
        if (!repo || !prNumber) return;

        setButtonLoading(button, true, 'Opening');
        setImportStatus?.('Opening merge conflict in Auditor...', true);

        try {
            startAuditSession(buildMergeConflictAuditPayload({
                provider: button.dataset.provider || 'github',
                repo,
                repoId: button.dataset.repoId || null,
                prNumber,
                title: button.dataset.title || 'Merge conflict',
                baseBranch: button.dataset.base || 'main',
                headBranch: button.dataset.head || '',
            }));
        } catch (err) {
            console.error('Conflict import failed:', err);
            alert(`Conflict import failed: ${err.message || 'Check console for details'}`);
            setButtonLoading(button, false);
            setImportStatus?.('', false);
        }
    });
}
