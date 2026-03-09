/**
 * UI Rendering Service for Import Components
 */

const LANGUAGE_COLORS = {
    'JavaScript': '#f1e05a',
    'TypeScript': '#3178c6',
    'PHP': '#4F5D95',
    'Python': '#3572A5',
    'HTML': '#e34c26',
    'CSS': '#563d7c',
    'Java': '#b07219',
    'Go': '#00ADD8',
    'Ruby': '#701516',
    'C++': '#f34b7d',
    'C#': '#178600',
    'Shell': '#89e051',
    'Vue': '#41b883',
    'React': '#61dafb',
    'Dart': '#00B4AB',
    'Swift': '#ffac45',
    'Kotlin': '#A97BFF',
    'Rust': '#dea584'
};

const SPINNER_SVG = `<svg style="animation:spin 0.8s linear infinite;" viewBox="0 0 16 16" width="12" height="12"><circle cx="8" cy="8" r="6" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="20" stroke-dashoffset="5" opacity="0.4"/></svg>`;

/**
 * Formats a date string into a relative time like "5 min ago" or "2 days ago"
 * Matches GitHub's behavior: shows absolute date if it's very old.
 */
function formatRelativeTime(dateString) {
    if (!dateString) return 'unknown';
    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'just now';

    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) return `${diffInMinutes} min ago`;

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} hr${diffInHours > 1 ? 's' : ''} ago`;

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 30) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;

    if (diffInDays < 365) {
        const diffInMonths = Math.floor(diffInDays / 30);
        return `${diffInMonths} mo${diffInMonths > 1 ? 's' : ''} ago`;
    }

    // Older than a year, show absolute date
    return 'on ' + date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

export function createRepoItem(repo) {
    const li = document.createElement('li');
    li.className = 'imports-repo-item';
    const visibility = repo.private ? 'Private' : 'Public';
    const updatedAt = formatRelativeTime(repo.updated_at);
    const languageName = repo.language && repo.language !== 'Unknown' ? repo.language : 'Plain Text';
    const languageColor = LANGUAGE_COLORS[languageName] || '#8b949e';

    // open_issues_count is total (Issues + PRs)
    // We'll update the specific PR count in the background
    const totalIssues = repo.open_issues_count || 0;

    li.innerHTML = `
        <details class="imports-repo-details" data-repo="${repo.full_name}">
            <summary class="imports-repo-summary">
                <div class="imports-repo-main">
                    <svg class="repo-icon" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" title="Repository"><path fill-rule="evenodd" d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 110-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1V9h-8c-.356 0-.694.074-1 .208V2.5a1 1 0 011-1h8zM5 12.25v3.25a.25.25 0 00.4.2l1.45-1.087a.25.25 0 01.3 0L8.6 15.7a.25.25 0 00.4-.2v-3.25a.25.25 0 00-.25-.25h-3.5a.25.25 0 00-.25.25z" fill="currentColor"></path></svg>
                    <h4>${repo.full_name}</h4>
                    <span class="imports-repo-badge">${visibility}</span>
                </div>
                <div class="imports-repo-meta" style="display: flex; align-items: center; gap: 15px;">
                    <span style="display: flex; align-items: center; gap: 6px; margin-left: 5px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: ${languageColor};"></span>
                        ${languageName}
                    </span>
                    <span title="Open Issues" style="display: flex; align-items: center; gap: 4px; cursor: help;">
                        <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor" style="color: var(--text-soft);"><path d="M8 9.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path><path fill-rule="evenodd" d="M8 0a8 8 0 100 16A8 8 0 008 0zM1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z"></path></svg>
                        <span class="issue-count-placeholder" style="display:inline-flex;align-items:center;">${totalIssues}</span>
                    </span>
                    <span title="Open Pull Requests" style="display: flex; align-items: center; gap: 4px; cursor: help;">
                        <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor" style="color: #1a7f37;"><path fill-rule="evenodd" d="M7.177 3.073L9.573.677A.25.25 0 0110 .854v4.792a.25.25 0 01-.427.177L7.177 3.427a.25.25 0 010-.354zM3.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122v5.256a2.251 2.251 0 11-1.5 0V5.372A2.25 2.25 0 011.5 3.25zM11 2.5h-1V4h1a1 1 0 011 1v5.628a2.251 2.251 0 101.5 0V5A2.5 2.5 0 0011 2.5zm1 10.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0zM3.75 12a.75.75 0 100 1.5.75.75 0 000-1.5z"></path></svg>
                        <span class="pull-count-placeholder" style="display:inline-flex;align-items:center;">${SPINNER_SVG}</span>
                    </span>
                    <span title="Number of branches" style="display: flex; align-items: center; gap: 4px; cursor: help;">
                        <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor" style="color: var(--text-soft);"><path fill-rule="evenodd" d="M11.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122V6A2.5 2.5 0 0110 8.5H6a1 1 0 00-1 1v1.128a2.251 2.251 0 11-1.5 0V5.372a2.25 2.25 0 111.5 0v1.836A2.492 2.492 0 016 7h4a1 1 0 001-1v-.628A2.25 2.25 0 019.5 3.25zM4.25 12a.75.75 0 100 1.5.75.75 0 000-1.5zM3.5 3.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0z"></path></svg>
                        <span class="branch-count-placeholder" style="display:inline-flex;align-items:center;">${SPINNER_SVG}</span>
                    </span>
                    <span style="color: var(--text-soft);">Updated ${updatedAt}</span>
                </div>
                <span class="imports-chevron" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </summary>
            <div class="imports-repo-content">
                <div class="repo-loading-indicator" style="padding: 20px; text-align: center; color: var(--text-soft); font-size: 12px;">
                    <div class="skeleton-item" style="height: 20px; width: 100%; margin-bottom: 10px;"></div>
                    <div class="skeleton-item" style="height: 20px; width: 80%;"></div>
                </div>
                <ul class="imports-branches-list"></ul>
            </div>
        </details>
    `;
    return li;
}

export function createBranchItem(branch, pullRequests) {
    // Group PRs that belong to this branch (either head or base)
    const branchPrs = pullRequests.filter(pr => pr.head_ref === branch.name);

    // Use the branch's own commit date, falling back to 'unknown'
    const latestUpdate = branch.updated_at
        ? formatRelativeTime(branch.updated_at)
        : (branchPrs.length > 0 ? formatRelativeTime(branchPrs[0].updated_at) : 'unknown');

    const li = document.createElement('li');
    li.className = 'imports-branch-item';
    li.innerHTML = `
        <details class="imports-branch-details">
            <summary class="imports-branch-summary">
                <div class="imports-branch-main">
                    <svg class="branch-icon" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" title="Branch"><path fill-rule="evenodd" d="M11.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122V6A2.5 2.5 0 0110 8.5H6a1 1 0 00-1 1v1.128a2.251 2.251 0 11-1.5 0V5.372a2.25 2.25 0 111.5 0v1.836A2.492 2.492 0 016 7h4a1 1 0 001-1v-.628A2.25 2.25 0 019.5 3.25zM4.25 12a.75.75 0 100 1.5.75.75 0 000-1.5zM3.5 3.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0z" fill="currentColor"></path></svg>
                    <strong>${branch.name}</strong>
                </div>
                <div class="imports-branch-meta" style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 11px; color: var(--text-soft);">Updated ${latestUpdate}</span>
                    <span class="imports-tag" title="Pull requests in this branch" style="display: flex; align-items: center; gap: 4px; padding: 2px 10px; cursor: help;">
                        <svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor" style="color: #1a7f37;"><path fill-rule="evenodd" d="M7.177 3.073L9.573.677A.25.25 0 0110 .854v4.792a.25.25 0 01-.427.177L7.177 3.427a.25.25 0 010-.354zM3.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122v5.256a2.251 2.251 0 11-1.5 0V5.372A2.25 2.25 0 011.5 3.25zM11 2.5h-1V4h1a1 1 0 011 1v5.628a2.251 2.251 0 101.5 0V5A2.5 2.5 0 0011 2.5zm1 10.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0zM3.75 12a.75.75 0 100 1.5.75.75 0 000-1.5z"></path></svg>
                        ${branchPrs.length}
                    </span>
                </div>
                <span class="imports-chevron" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </summary>
            <div class="imports-branch-content">
                <ul class="imports-pr-list"></ul>
            </div>
        </details>
    `;

    const prList = li.querySelector('.imports-pr-list');
    if (branchPrs.length === 0) {
        prList.innerHTML = '<li style="padding: 10px 64px; color: var(--text-soft); font-size: 12px;">No active pull requests for this branch.</li>';
    } else {
        branchPrs.forEach(pr => {
            prList.appendChild(createPrItem(pr));
        });
    }

    return li;
}

export function createPrItem(pr) {
    const li = document.createElement('li');
    li.className = 'imports-pr-item';
    const timeAgo = formatRelativeTime(pr.updated_at);
    const statusColor = pr.draft ? '#5d6475' : '#1a7f37';

    // Total comments = conversation comments + review (code) comments
    const totalComments = (pr.comments || 0) + (pr.review_comments || 0);

    // Create labels HTML
    const labelsHtml = (pr.labels || []).map(label => `
        <span class="pr-label" title="Label: ${label.name}" style="background-color: #${label.color}; padding: 0 7px; border-radius: 2em; font-size: 11px; font-weight: 500; border: 1px solid rgba(0,0,0,0.1); color: ${parseInt(label.color, 16) > 0xffffff / 2 ? '#333' : '#fff'}; cursor: help;">${label.name}</span>
    `).join('');

    li.innerHTML = `
        <div class="imports-pr-main" style="display: flex; align-items: flex-start; gap: 8px;">
            <svg class="pr-icon" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true" title="Open Pull Request" style="color: ${statusColor}; flex-shrink: 0; margin-top: 2px;">
                <path fill-rule="evenodd" d="M7.177 3.073L9.573.677A.25.25 0 0110 .854v4.792a.25.25 0 01-.427.177L7.177 3.427a.25.25 0 010-.354zM3.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122v5.256a2.251 2.251 0 11-1.5 0V5.372A2.25 2.25 0 011.5 3.25zM11 2.5h-1V4h1a1 1 0 011 1v5.628a2.251 2.251 0 101.5 0V5A2.5 2.5 0 0011 2.5zm1 10.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0zM3.75 12a.75.75 0 100 1.5.75.75 0 000-1.5z" fill="currentColor"></path>
            </svg>
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <h5 class="imports-pr-title" style="margin: 0; font-size: 13px; font-weight: 600; color: var(--text-main);">${pr.title}</h5>
                    <div style="display: flex; gap: 4px;">${labelsHtml}</div>
                </div>
                <div class="imports-pr-meta" style="font-size: 11px; color: var(--text-soft); margin-top: 1px; display: flex; align-items: center; gap: 12px;">
                    <span title="Pull Request ID">#${pr.number}</span>
                    <span title="Last updated ${timeAgo}">${timeAgo} by ${pr.author}</span>
                    <span title="Conversation and Review comments" style="display: flex; align-items: center; gap: 4px; cursor: help;">
                        <svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor"><path fill-rule="evenodd" d="M2.75 2.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h2a.75.75 0 01.75.75v2.19l2.72-2.72a.75.75 0 01.53-.22h4.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25H2.75zM1 2.75C1 1.784 1.784 1 2.75 1h10.5c.966 0 1.75.784 1.75 1.75v7.5A1.75 1.75 0 0113.25 12H9.06l-2.573 2.573A1.457 1.457 0 014 13.543V12H2.75A1.75 1.75 0 011 10.25v-7.5z"></path></svg>
                        ${totalComments}
                    </span>
                </div>
            </div>
        </div>
    `;
    return li;
}
