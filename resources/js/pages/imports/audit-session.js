export function startAuditSession(payload) {
    sessionStorage.setItem('pending_audit', JSON.stringify(payload));
    window.location.href = '/auditor';
}

export function buildPullRequestAuditPayload({ repo, prNumber, title, auditStatus = null }) {
    const safeTitle = String(title || '').trim();
    return {
        source: 'github',
        repo,
        prNumber,
        prTitle: safeTitle,
        auditStatus: auditStatus || null,
        name: `${repo} PR#${prNumber}: ${safeTitle}`,
        auditTitle: `${repo} pull request audit ${safeTitle || `#${prNumber}`}`.trim(),
        auditKind: 'pull_request_audit',
        branch: null,
        base: null,
        compareType: 'pull_request',
    };
}

export function buildBranchAuditPayload({ repo, branchName, baseBranch }) {
    const safeBranch = String(branchName || '').trim();
    return {
        source: 'upload',
        repo,
        prNumber: null,
        prTitle: null,
        auditStatus: 'active',
        name: `${repo} (${safeBranch})`,
        auditTitle: `${repo} branch audit ${safeBranch}`.trim(),
        auditKind: 'branch_audit',
        branch: safeBranch,
        base: baseBranch,
        compareType: 'branch_vs_main',
    };
}

export function buildCommitAuditPayload({ repo, commitHash, title }) {
    const safeTitle = String(title || '').trim();
    return {
        source: 'import',
        repo,
        prNumber: null,
        prTitle: null,
        auditStatus: 'historical',
        commitHash,
        name: `${repo} commit ${commitHash}: ${safeTitle}`.trim(),
        auditTitle: `${repo} commit audit ${commitHash}${safeTitle ? ` ${safeTitle}` : ''}`.trim(),
        auditKind: 'commit_audit',
        branch: null,
        base: null,
        compareType: 'commit',
    };
}
