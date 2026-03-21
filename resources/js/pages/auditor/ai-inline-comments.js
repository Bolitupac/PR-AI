function extractBlock(text, tagName) {
    const pattern = new RegExp(`\\[${tagName}\\]([\\s\\S]*?)\\[\\/${tagName}\\]`, 'i');
    const match = String(text || '').match(pattern);
    return match ? match[1].trim() : '';
}

function extractTrailingJsonArray(text) {
    const value = String(text || '');
    const candidateStarts = [];

    for (let index = value.lastIndexOf('['); index !== -1; index = value.lastIndexOf('[', index - 1)) {
        candidateStarts.push(index);
    }

    for (const start of candidateStarts) {
        const candidate = value.slice(start).trim();
        if (!candidate.startsWith('[') || !candidate.endsWith(']')) continue;

        try {
            const parsed = JSON.parse(candidate);
            if (Array.isArray(parsed)) {
                return candidate;
            }
        } catch {
            continue;
        }
    }

    return '';
}

function stripTrailingJsonIntro(text) {
    return String(text || '')
        .replace(/\n?\s*Here(?:'s| is)?\s+the\s+inline\s+comments(?:\s+in\s+the\s+required\s+format)?\:?\s*$/i, '')
        .replace(/\n?\s*Here(?:'s| is)?\s+the\s+hidden\s+JSON\s+block(?:\s+for\s+inline\s+comments)?\:?\s*$/i, '')
        .trimEnd();
}

function normalizeInlineComments(raw) {
    try {
        const parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) return [];

        return parsed
            .map((comment) => {
                const path = typeof comment?.path === 'string' ? comment.path.trim() : '';
                const line = Number.parseInt(comment?.line, 10);
                const body = typeof comment?.body === 'string' ? comment.body.trim() : '';
                const side = String(comment?.side || 'RIGHT').toUpperCase() === 'LEFT' ? 'LEFT' : 'RIGHT';

                if (!path || !Number.isInteger(line) || !body) {
                    return null;
                }

                return {
                    path,
                    line,
                    side,
                    body,
                    author: 'AI',
                    kind: 'ai',
                    source: 'ai',
                    updated_at: new Date().toISOString(),
                };
            })
            .filter(Boolean)
            .slice(0, 12);
    } catch {
        return [];
    }
}

export function stripInlineCommentsBlock(text) {
    const wrappedStripped = String(text || '').replace(/\[INLINE_COMMENTS\][\s\S]*?\[\/INLINE_COMMENTS\]/gi, '').trim();
    const trailingJson = extractTrailingJsonArray(wrappedStripped);

    if (!trailingJson) {
        return wrappedStripped;
    }

    const beforeJson = wrappedStripped.slice(0, wrappedStripped.lastIndexOf(trailingJson)).trimEnd();
    return stripTrailingJsonIntro(beforeJson).trim();
}

export function extractInlineComments(text) {
    const raw = extractBlock(text, 'INLINE_COMMENTS') || extractTrailingJsonArray(text);
    if (!raw) return [];

    return normalizeInlineComments(raw);
}
