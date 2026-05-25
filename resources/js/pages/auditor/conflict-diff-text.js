export function isMetadataOnlyConflict(conflictData) {
    if (!conflictData || typeof conflictData !== 'object') {
        return false;
    }

    if (conflictData.has_hunks === true) {
        return false;
    }

    if (conflictData.conflict_source === 'github_metadata_only') {
        return true;
    }

    const files = conflictData.files;
    return !Array.isArray(files) || files.length === 0;
}

export function conflictMetadataToAuditText(conflictData) {
    const repo = conflictData?.repo || 'unknown-repo';
    const prNumber = conflictData?.pr_number || conflictData?.prNumber || '?';
    const title = conflictData?.title || 'Merge conflict';
    const baseRef = conflictData?.base_ref || 'main';
    const headRef = conflictData?.head_ref || 'feature-branch';
    const mergeableState = conflictData?.mergeable_state || 'unknown';
    const message = conflictData?.message || '';
    const commands = (conflictData?.suggested_git_commands || []).join('\n');

    return [
        `MERGE CONFLICT AUDIT (metadata only)`,
        `Repository: ${repo}`,
        `Pull/MR: #${prNumber} — ${title}`,
        `Base branch: ${baseRef}`,
        `Head branch: ${headRef}`,
        `Provider mergeable_state: ${mergeableState}`,
        `Conflict source: ${conflictData?.conflict_source || 'metadata_only'}`,
        '',
        'DATA LIMITATION:',
        message || 'The provider reports this change as conflicted but did not return per-file conflict marker hunks via API.',
        'Do not assume specific file paths or line-level markers unless the user resolves locally and supplies a diff.',
        '',
        'SUGGESTED GIT COMMANDS:',
        commands || [
            'git fetch origin',
            `git checkout ${headRef}`,
            `git merge origin/${baseRef}`,
        ].join('\n'),
    ].join('\n');
}

export function conflictPayloadToDiffText(conflictData) {
    if (isMetadataOnlyConflict(conflictData)) {
        return conflictMetadataToAuditText(conflictData);
    }

    const files = conflictData?.files || [];
    if (!Array.isArray(files) || files.length === 0) {
        return '';
    }

    return files.map((file) => {
        const path = file.path || 'unknown';
        const body = (file.hunks || []).map((h) => h.raw_marker_block || '').join('\n\n');

        return `diff --git a/${path} b/${path}\n--- a/${path}\n+++ b/${path}\n${body}`;
    }).join('\n\n');
}
