import { escapeHtml, formatRelativeTime } from './utils';

let activeThreadZIndex = 20;
const commentIcon = `
    <svg class="diff-inline-comment-icon" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
        <path d="M2.5 3.75A2.25 2.25 0 0 1 4.75 1.5h6.5a2.25 2.25 0 0 1 2.25 2.25v4.5a2.25 2.25 0 0 1-2.25 2.25H8.4l-2.723 2.178A.75.75 0 0 1 4.5 12.1v-1.6h-.75A2.25 2.25 0 0 1 1.5 8.25v-4.5Z" fill="currentColor"/>
    </svg>
`;
const aiIcon = `
    <svg class="diff-inline-comment-icon" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
        <path d="M8 1.2 9.36 4.64 12.8 6 9.36 7.36 8 10.8 6.64 7.36 3.2 6 6.64 4.64 8 1.2Z" fill="currentColor"/>
        <path d="M12.4 9.1 13.05 10.75 14.7 11.4 13.05 12.05 12.4 13.7 11.75 12.05 10.1 11.4 11.75 10.75 12.4 9.1Z" fill="currentColor"/>
    </svg>
`;

function getThreadSource(comments) {
    return comments.every((comment) => comment?.source === 'ai') ? 'ai' : 'github';
}

function getThreadIcon(source) {
    return source === 'ai' ? aiIcon : commentIcon;
}

function buildSummary(comments, path, line) {
    if (comments.length === 1) {
        const comment = comments[0];
        return `@${comment.author || 'unknown'} • ${path}:${line} • ${formatRelativeTime(comment.updated_at)}`;
    }

    return `${comments.length} comments • ${path}:${line}`;
}

function buildCommentBody(comment, { showMeta = true } = {}) {
    const source = comment?.source === 'ai' ? 'ai' : 'github';
    const icon = getThreadIcon(source);
    return `
        <article class="diff-inline-comment ${source === 'ai' ? 'is-ai-comment' : ''}">
            ${showMeta ? `
            <div class="diff-inline-comment-meta">
                ${icon}
                <span>@${escapeHtml(comment.author || 'unknown')}</span>
                <span>${escapeHtml(formatRelativeTime(comment.updated_at))}</span>
            </div>
            ` : ''}
            <div class="diff-inline-comment-body">${escapeHtml(comment.body || '').replaceAll('\n', '<br>')}</div>
        </article>
    `;
}

export function createCommentThreadElement(path, line, comments, side = 'RIGHT') {
    const summaryText = buildSummary(comments, path, line);
    const source = getThreadSource(comments);
    const icon = getThreadIcon(source);
    const thread = document.createElement('details');
    thread.className = `diff-inline-thread${source === 'ai' ? ' is-ai-comment' : ''}`;
    thread.dataset.side = side;
    thread.innerHTML = `
        <summary class="diff-inline-thread-summary" title="${escapeHtml(summaryText)}" aria-label="${escapeHtml(summaryText)}">
            ${icon}
            <span>${escapeHtml(summaryText)}</span>
        </summary>
        <div class="diff-inline-thread-body">
            ${comments.map((comment) => buildCommentBody(comment, {
                showMeta: comments.length > 1,
            })).join('')}
        </div>
    `;

    const bringToFront = () => {
        activeThreadZIndex += 1;
        thread.style.zIndex = String(activeThreadZIndex);
    };

    thread.addEventListener('toggle', bringToFront);
    thread.addEventListener('click', bringToFront);
    thread.addEventListener('focusin', bringToFront);

    return thread;
}
