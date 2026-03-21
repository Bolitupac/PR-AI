import { mapRenderedFiles, findAnchorCell } from './anchor-map';
import { createCommentThreadElement } from './thread-renderer';

function clearInlineComments(overlay) {
    overlay.innerHTML = '';
}

function isAnchorVisible(anchorCell) {
    if (!anchorCell || anchorCell.offsetParent === null) {
        return false;
    }

    const rect = anchorCell.getBoundingClientRect();
    return rect.width > 0 && rect.height > 0;
}

function collectOpenThreadKeys(overlay) {
    return new Set(
        Array.from(overlay.querySelectorAll('.diff-inline-thread[open]'))
            .map((thread) => thread.dataset.threadKey)
            .filter(Boolean)
    );
}

function groupAnchoredComments(comments) {
    const groups = new Map();

    comments
        .filter((comment) => comment?.path && Number.isInteger(comment?.line))
        .forEach((comment) => {
            const side = (comment?.side || 'RIGHT').toUpperCase();
            const source = comment?.source === 'ai' ? 'ai' : 'github';
            const key = `${comment.path}:${comment.line}:${side}:${source}`;
            const current = groups.get(key) || [];
            current.push(comment);
            groups.set(key, current);
        });

    return groups;
}

export function createInlineDiffCommentsController(container) {
    const root = container;
    const overlay = document.getElementById('diff-comments-overlay');
    const getThreads = () => (overlay ? Array.from(overlay.querySelectorAll('.diff-inline-thread')) : []);
    let lastRenderArgs = null;
    let forcedOpen = false;

    const clear = () => {
        if (!overlay) return;
        clearInlineComments(overlay);
    };

    const render = ({ diffText, comments = [], outputFormat = 'side-by-side' }) => {
        if (!overlay) return;

        lastRenderArgs = { diffText, comments, outputFormat };
        const openThreadKeys = forcedOpen ? null : collectOpenThreadKeys(overlay);
        clear();

        if (!diffText || !comments.length) {
            return;
        }

        const wrapperMap = mapRenderedFiles(root, diffText);
        const groups = groupAnchoredComments(comments);
        if (groups.size === 0) {
            return;
        }

        const overlayRect = overlay.getBoundingClientRect();
        const overlayWidth = overlay.clientWidth;
        groups.forEach((groupComments, key) => {
            const keyParts = key.split(':');
            const source = keyParts.pop();
            const side = keyParts.pop() || 'RIGHT';
            const lineText = keyParts.pop();
            const path = keyParts.join(':');
            const lineNumber = Number.parseInt(lineText, 10);
            const anchorCell = findAnchorCell(wrapperMap, path, lineNumber, outputFormat, side);
            if (!isAnchorVisible(anchorCell)) return;

            const anchorRect = anchorCell.getBoundingClientRect();
            const thread = createCommentThreadElement(path, lineNumber, groupComments, side);
            const expandedWidth = Math.min(220, Math.max(160, overlayWidth - 24));
            const collapsedWidth = 36;
            thread.dataset.threadKey = `${path}:${lineText}:${side}:${source}`;
            thread.style.setProperty('--thread-width', `${expandedWidth}px`);
            thread.style.setProperty('--thread-collapsed-size', `${collapsedWidth}px`);
            overlay.appendChild(thread);

            const threadTop = Math.max(0, anchorRect.top - overlayRect.top + 2);
            thread.style.top = `${threadTop}px`;
            const naturalLeft = side === 'LEFT'
                ? anchorRect.left - overlayRect.left - collapsedWidth - 12
                : anchorRect.right - overlayRect.left + 12;
            const minLeft = 12;
            const maxLeft = Math.max(minLeft, overlayWidth - collapsedWidth - 12);
            thread.style.left = `${Math.max(minLeft, Math.min(naturalLeft, maxLeft))}px`;

            if (forcedOpen || openThreadKeys?.has(key)) {
                thread.open = true;
            }
        });
    };

    const refresh = () => {
        if (!lastRenderArgs) return;
        render(lastRenderArgs);
    };

    const expandAll = () => {
        forcedOpen = true;
        getThreads().forEach((thread) => {
            thread.open = true;
        });
    };

    const collapseAll = () => {
        forcedOpen = false;
        getThreads().forEach((thread) => {
            thread.open = false;
        });
    };

    const getThreadCount = () => getThreads().length;

    return { clear, render, refresh, expandAll, collapseAll, getThreadCount };
}
