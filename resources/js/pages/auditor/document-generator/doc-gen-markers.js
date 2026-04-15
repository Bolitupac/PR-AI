const TAGS = {
    preview: { open: '[DOC_PREVIEW]', close: '[/DOC_PREVIEW]' },
    question: { open: '[DOC_QUESTION]', close: '[/DOC_QUESTION]' },
    formats: { open: '[DOC_FORMATS]', close: '[/DOC_FORMATS]' },
};

function safeJsonParse(value) {
    try {
        return JSON.parse(value);
    } catch {
        return null;
    }
}

function normalizeQuestion(entry, index) {
    if (!entry || typeof entry !== 'object') return null;
    const question = String(entry.question || '').trim();
    const options = Array.isArray(entry.options)
        ? entry.options.map(option => String(option || '').trim()).filter(Boolean)
        : [];

    if (!question || options.length === 0) return null;

    return {
        id: String(entry.id || `docgen-question-${index + 1}`),
        question,
        options,
    };
}

function normalizeFormats(entry) {
    if (Array.isArray(entry)) {
        const allowed = entry.map(item => String(item || '').trim().toLowerCase()).filter(Boolean);
        return {
            allowed,
            default: allowed[0] || 'pdf',
        };
    }

    if (!entry || typeof entry !== 'object') {
        return {
            allowed: ['pdf'],
            default: 'pdf',
        };
    }

    const allowed = Array.isArray(entry.allowed)
        ? entry.allowed.map(item => String(item || '').trim().toLowerCase()).filter(Boolean)
        : [];
    const defaultFormat = String(entry.default || allowed[0] || 'pdf').trim().toLowerCase();

    return {
        allowed: allowed.length > 0 ? allowed : [defaultFormat || 'pdf'],
        default: defaultFormat || 'pdf',
    };
}

function findNextTag(text, startIndex) {
    const candidates = Object.entries(TAGS)
        .map(([kind, tag]) => ({ kind, index: text.indexOf(tag.open, startIndex) }))
        .filter(candidate => candidate.index !== -1)
        .sort((a, b) => a.index - b.index);

    return candidates[0] || null;
}

function stripDanglingDocPrefix(text) {
    return String(text || '').replace(/\[DOC(?:_[A-Z]*)?$/i, '').trim();
}

export function parseDocGenMarkers(text) {
    const raw = String(text || '');
    const questions = [];
    let previewMarkdown = '';
    let previewStreaming = false;
    let formats = { allowed: ['pdf'], default: 'pdf' };
    let cursor = 0;
    let visibleText = '';

    while (cursor < raw.length) {
        const nextTag = findNextTag(raw, cursor);
        if (!nextTag) {
            visibleText += raw.slice(cursor);
            break;
        }

        visibleText += raw.slice(cursor, nextTag.index);
        const tag = TAGS[nextTag.kind];
        const contentStart = nextTag.index + tag.open.length;
        const closeIndex = raw.indexOf(tag.close, contentStart);

        if (closeIndex === -1) {
            const partialContent = raw.slice(contentStart);
            if (nextTag.kind === 'preview') {
                previewMarkdown = partialContent.trim();
                previewStreaming = true;
            }
            break;
        }

        const blockContent = raw.slice(contentStart, closeIndex).trim();
        if (nextTag.kind === 'preview') {
            previewMarkdown = blockContent;
        } else if (nextTag.kind === 'question') {
            const parsed = safeJsonParse(blockContent);
            const normalized = normalizeQuestion(parsed, questions.length);
            if (normalized) {
                questions.push(normalized);
            }
        } else if (nextTag.kind === 'formats') {
            formats = normalizeFormats(safeJsonParse(blockContent));
        }

        cursor = closeIndex + tag.close.length;
    }

    const cleanedVisibleText = stripDanglingDocPrefix(
        visibleText
            .replace(/\[DOC_READY\]/gi, '')
            .replace(/\[DOC_AUTO_TRIGGER\]/gi, '')
            .replace(/\[DOC_FORMATS\][\s\S]*?\[\/DOC_FORMATS\]/gi, '')
            .replace(/\[DOC_QUESTION\][\s\S]*?\[\/DOC_QUESTION\]/gi, '')
            .replace(/\[DOC_PREVIEW\][\s\S]*?\[\/DOC_PREVIEW\]/gi, '')
            .replace(/\[DOC_[A-Z_]+\]/gi, '')
            .replace(/\[\/DOC_[A-Z_]+\]/gi, '')
    );

    return {
        autoTrigger: /\[DOC_AUTO_TRIGGER\]/i.test(raw),
        ready: /\[DOC_READY\]/i.test(raw),
        previewMarkdown,
        previewStreaming,
        questions,
        formats,
        visibleText: cleanedVisibleText,
    };
}

export function stripDocGenMarkers(text) {
    return parseDocGenMarkers(text).visibleText;
}
