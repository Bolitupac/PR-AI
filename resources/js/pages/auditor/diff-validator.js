// Validates that text looks like a unified git diff.
export function validateDiffText(text) {
    const value = (text || '').trim();
    if (!value) {
        return { valid: false, reason: 'Editor is empty. Paste a diff first.' };
    }

    const hasGitHeader = /(^|\n)diff --git\s+/m.test(value);
    const hasHunkHeader = /(^|\n)@@\s+-\d+(,\d+)?\s+\+\d+(,\d+)?\s+@@/m.test(value);
    const hasChangeLine = /(^|\n)(\+[^+].*|-[^-].*)/m.test(value);

    if ((!hasGitHeader && !hasHunkHeader) || !hasChangeLine) {
        return { valid: false, reason: 'Diff format is not valid. Paste a unified git diff.' };
    }

    return { valid: true, reason: '' };
}
