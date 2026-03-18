import * as API from './api';
import { buildPullRequestAuditPayload, startAuditSession } from './audit-session';

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function getStatusLabel(pullRequest) {
    if (pullRequest.merged_at) return 'merged';
    if (pullRequest.draft) return 'draft';

    const state = String(pullRequest.state || '').toLowerCase();
    if (state === 'open') return 'open';
    if (state === 'closed') return 'closed';

    return 'unknown';
}

function formatRelativeTime(dateString) {
    if (!dateString) return 'unknown';

    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (Number.isNaN(diffInSeconds)) return 'unknown';
    if (diffInSeconds < 60) return 'just now';

    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) return `${diffInMinutes} min ago`;

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} hr${diffInHours > 1 ? 's' : ''} ago`;

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 30) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;

    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function renderEmptyState(panel, message) {
    panel.innerHTML = `
        <li class="imports-history-item" style="color: var(--text-soft); font-size: 12px;">
            ${escapeHtml(message)}
        </li>
    `;
}

function renderPullRequests(panel, pullRequests) {
    if (!Array.isArray(pullRequests) || pullRequests.length === 0) {
        renderEmptyState(panel, 'No recent pull requests available.');
        return;
    }

    panel.innerHTML = pullRequests.map((pullRequest) => {
        const status = getStatusLabel(pullRequest);

        return `
            <li class="imports-history-item imports-activity-item">
                <div class="imports-activity-flex">
                    <div class="imports-activity-main">
                        <span class="imports-activity-badge">PR</span>
                        <span class="imports-activity-title">${escapeHtml(pullRequest.title || 'Untitled pull request')}</span>
                        <span class="imports-activity-status imports-activity-status--${escapeHtml(status)}">
                            ${escapeHtml(status.charAt(0).toUpperCase() + status.slice(1))}
                        </span>
                    </div>
                    <div class="imports-activity-meta">
                        <span class="imports-activity-pr-number">#${escapeHtml(pullRequest.number ?? '—')}</span>
                        <span class="imports-activity-repo">${escapeHtml(pullRequest.repo || '')}</span>
                        <span class="imports-activity-author">${escapeHtml(pullRequest.author || '')}</span>
                        <span class="imports-activity-time">${escapeHtml(formatRelativeTime(pullRequest.updated_at))}</span>
                    </div>
                    <button
                        class="imports-activity-action-btn imports-recent-pr-audit-btn"
                        type="button"
                        data-repo="${escapeHtml(pullRequest.repo || '')}"
                        data-pr="${escapeHtml(pullRequest.number || '')}"
                        data-status="${escapeHtml(status)}"
                        data-title="${escapeHtml(pullRequest.title || '')}"
                        aria-label="Audit pull request"
                    >
                        Audit
                    </button>
                </div>
            </li>
        `;
    }).join('');
}

export async function initRecentPullRequestsPanel(setImportStatus) {
    const panel = document.getElementById('recent-pull-requests-list');
    if (!panel) return;

    renderEmptyState(panel, 'Loading recent pull requests...');

    try {
        const pullRequests = await API.fetchRecentPullRequests();
        renderPullRequests(panel, pullRequests);
    } catch (error) {
        if (error?.status === 401) {
            renderEmptyState(panel, 'Connect GitHub to load your recent pull requests.');
        } else {
            renderEmptyState(panel, 'Failed to load recent pull requests.');
        }
    }

    panel.addEventListener('click', (event) => {
        const button = event.target.closest('.imports-recent-pr-audit-btn');
        if (!button) return;

        const repo = button.dataset.repo;
        const prNumber = button.dataset.pr;
        const auditStatus = button.dataset.status || null;
        const title = button.dataset.title || 'Untitled pull request';

        if (!repo || !prNumber) return;

        button.classList.add('is-loading');
        setImportStatus?.('Preparing audit in Auditor...', true);

        startAuditSession(buildPullRequestAuditPayload({
            repo,
            prNumber,
            title,
            auditStatus,
        }));
    });
}
