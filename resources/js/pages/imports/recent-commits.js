import * as API from './api';
import { buildCommitAuditPayload, startAuditSession } from './audit-session';
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

function renderCommits(panel, commits, provider) {
    if (!Array.isArray(commits) || commits.length === 0) {
        renderEmptyState(panel, 'No recent commits available.');
        return;
    }

    panel.innerHTML = commits.map((commit) => {
        return `
            <li class="imports-history-item imports-activity-item imports-commit-item">
                <div class="imports-activity-flex imports-commit-flex">
                    <div class="imports-activity-main imports-commit-main">
                        <code class="imports-activity-badge imports-commit-hash">${escapeHtml((commit.hash || '').slice(0, 7) || '—')}</code>
                        <span class="imports-activity-title imports-commit-msg">${escapeHtml(commit.message || '')}</span>
                    </div>
                    <div class="imports-activity-meta imports-commit-meta">
                        <span class="imports-commit-author">${escapeHtml(commit.author || '')}</span>
                        <span class="imports-commit-repo">${escapeHtml(commit.repo || '')}</span>
                        <span class="imports-activity-time imports-commit-time">${escapeHtml(commit.time || '')}</span>
                    </div>
                    <button class="imports-activity-action-btn imports-commit-import-btn" type="button"
                        data-commit="${escapeHtml(commit.hash || '')}"
                        data-title="${escapeHtml(commit.message || '')}"
                        data-repo="${escapeHtml(commit.repo || '')}"
                        data-repo-id="${escapeHtml(commit.repo_id || '')}"
                        data-provider="${escapeHtml(provider || 'github')}"
                        aria-label="Audit commit">
                        Audit
                    </button>
                </div>
            </li>
        `;
    }).join('');
}

export async function initRecentCommitsPanel(apiBase, provider, setImportStatus) {
    const panel = document.getElementById('recent-commits-list');
    if (!panel) return;

    renderEmptyState(panel, 'Loading recent commits...');

    try {
        const commits = await API.fetchRecentCommits(apiBase, provider);
        renderCommits(panel, commits, provider);
    } catch (error) {
        if (error?.status === 401) {
            renderEmptyState(panel, `Connect ${provider} to load recent commits.`);
        } else if (error?.status === 501) {
            renderEmptyState(panel, error.message || `Recent commits are not supported for this provider.`);
        } else {
            renderEmptyState(panel, 'Failed to load recent commits.');
        }
    }

    if (panel.dataset.boundClick === 'true') {
        return;
    }

    panel.dataset.boundClick = 'true';
    panel.addEventListener('click', (event) => {
        const button = event.target.closest('.imports-commit-import-btn');
        if (!button) return;

        event.preventDefault();
        event.stopPropagation();

        const commitHash = button.dataset.commit;
        const title = button.dataset.title || 'Commit audit';
        const repo = button.dataset.repo || 'repo';
        const repoId = button.dataset.repoId || null;
        const providerStr = button.dataset.provider || 'github';

        if (!commitHash) return;

        setButtonLoading(button, true, 'Opening');
        setImportStatus?.('Preparing audit in Auditor...', true);

        try {
            startAuditSession(buildCommitAuditPayload({
                provider: providerStr,
                repo,
                repoId,
                commitHash,
                title,
            }));
        } catch (err) {
            console.error('Commit import failed:', err);
            alert(`Commit import failed: ${err.message || 'Check console for details'}`);
            setButtonLoading(button, false);
            setImportStatus?.('', false);
        }
    });
}
