function normalizeText(value) {
    return String(value || '').trim();
}

export function buildAuditMetadata(detail = {}) {
    const repo = normalizeText(detail.repo);
    const prNumber = detail.prNumber ?? detail.pr_number ?? null;
    const prTitle = normalizeText(detail.prTitle ?? detail.pr_title);
    const compareType = normalizeText(detail.compareType ?? detail.compare_type);
    const headBranch = normalizeText(detail.headBranch ?? detail.head_branch ?? detail.branch);
    const baseBranch = normalizeText(detail.baseBranch ?? detail.base_branch ?? detail.base);
    const auditStatus = normalizeText(detail.auditStatus ?? detail.audit_status);
    const fileName = normalizeText(detail.name ?? detail.file_name);
    const source = normalizeText(detail.source) || 'upload';

    let auditKind = normalizeText(detail.auditKind ?? detail.audit_kind);
    if (!auditKind) {
        if (normalizeText(detail.compareType) === 'merge_conflict') {
            auditKind = 'merge_conflict_audit';
        } else if (prNumber) {
            auditKind = 'pull_request_audit';
        } else if (compareType === 'branch_vs_main' || (headBranch && baseBranch)) {
            auditKind = 'branch_audit';
        } else if (source === 'editor') {
            auditKind = 'editor_diff_audit';
        } else if (source === 'paste') {
            auditKind = 'pasted_diff_audit';
        } else {
            auditKind = 'diff_audit';
        }
    }

    let auditTitle = normalizeText(detail.auditTitle ?? detail.audit_title);
    if (!auditTitle) {
        if (auditKind === 'pull_request_audit') {
            auditTitle = `${repo} pull request audit ${prTitle || `#${prNumber}`}`.trim();
        } else if (auditKind === 'branch_audit') {
            auditTitle = `${repo} branch audit ${headBranch}`.trim();
        } else if (auditKind === 'merge_conflict_audit') {
            auditTitle = `${repo} merge conflict ${prTitle || `#${prNumber}`}`.trim();
        } else if (fileName) {
            auditTitle = `${fileName} ${auditKind.replaceAll('_', ' ')}`.trim();
        } else {
            auditTitle = `${source} audit`.trim();
        }
    }

    const subtitleParts = [];
    if (repo) subtitleParts.push(repo);
    if (prNumber) subtitleParts.push(`PR #${prNumber}`);
    if (headBranch) subtitleParts.push(`head ${headBranch}`);
    if (baseBranch) subtitleParts.push(`base ${baseBranch}`);

    return {
        auditTitle,
        auditKind,
        prTitle,
        repo,
        prNumber,
        compareType,
        auditStatus,
        headBranch,
        baseBranch,
        fileName,
        subtitle: subtitleParts.join(' • '),
    };
}

export function stripLeadingAuditTitle(text, auditTitle) {
    const cleanText = String(text || '');
    const cleanTitle = normalizeText(auditTitle);
    if (!cleanTitle) return cleanText;

    const escapedTitle = cleanTitle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const pattern = new RegExp(`^(?:#\\s*)?${escapedTitle}\\s*`, 'i');
    return cleanText.replace(pattern, '').trimStart();
}
