export function conflictPayloadToDiffText(conflictData) {
    const files = conflictData?.files || [];
    if (!Array.isArray(files) || files.length === 0) {
        return '';
    }

    return files.map((file) => {
        const path = file.path || 'unknown';
        const body = file.merged_with_markers
            || (file.hunks || []).map((h) => h.raw_marker_block || '').join('\n\n');

        return `diff --git a/${path} b/${path}\n--- a/${path}\n+++ b/${path}\n${body}`;
    }).join('\n\n');
}
