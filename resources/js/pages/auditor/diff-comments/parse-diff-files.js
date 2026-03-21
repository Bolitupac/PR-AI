import { normalizePath } from './utils';

function normalizeDiffPath(rawPath) {
    const path = String(rawPath || '').trim();
    if (!path || path === '/dev/null') return '';
    if (path.startsWith('a/') || path.startsWith('b/')) {
        return normalizePath(path.slice(2));
    }

    return normalizePath(path);
}

export function parseDiffFiles(diffText) {
    const lines = String(diffText || '').split('\n');
    const files = [];
    let current = null;

    const pushCurrent = () => {
        if (!current) return;

        const canonicalPath = current.newPath || current.oldPath || '';
        if (canonicalPath) {
            files.push({ ...current, canonicalPath });
        }
    };

    for (const line of lines) {
        if (line.startsWith('diff --git ')) {
            pushCurrent();
            current = { oldPath: '', newPath: '' };
            continue;
        }

        if (!current) continue;

        if (line.startsWith('--- ')) {
            current.oldPath = normalizeDiffPath(line.slice(4));
            continue;
        }

        if (line.startsWith('+++ ')) {
            current.newPath = normalizeDiffPath(line.slice(4));
        }
    }

    pushCurrent();
    return files;
}
