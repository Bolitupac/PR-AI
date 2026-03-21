import { createInlineDiffCommentsController } from './diff-comments';

function countDifferences(diffText) {
    if (!diffText) return 0;
    return diffText.split('\n').reduce((count, line) => {
        if (line.startsWith('+++') || line.startsWith('---')) return count;
        if (line.startsWith('+') || line.startsWith('-')) return count + 1;
        return count;
    }, 0);
}

// Renders diff text with diff2html or an empty placeholder.
function drawDiff(container, diffText, outputFormat) {
    container.innerHTML = '';

    if (!diffText || !diffText.trim()) {
        container.innerHTML = '<div class="diff-empty">No differences to display.</div>';
        return;
    }

    const colorScheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';

    const diffUi = new window.Diff2HtmlUI(container, diffText, {
        drawFileList: true,
        fileListStartVisible: true,
        outputFormat,
        colorScheme,
        matching: 'none',
        synchronisedScroll: true,
        highlight: true,
        fileListToggle: true,
        fileContentToggle: true,
        stickyFileHeaders: true,
    });

    diffUi.draw();
    if (typeof diffUi.highlightCode === 'function') {
        diffUi.highlightCode();
    }
}

// Initializes the bottom diff viewer and listens for diff selection events.
export function initDiffViewer() {
    const container = document.getElementById('diff2html-container');
    const badge = document.getElementById('diff-count-badge');
    const commentBadge = document.getElementById('diff-comment-badge');
    const formatSelect = document.getElementById('diff-format-select');
    const expandButton = document.getElementById('diff-comments-expand-btn');
    const collapseButton = document.getElementById('diff-comments-collapse-btn');
    if (!container || !badge || !commentBadge || !formatSelect || !expandButton || !collapseButton) return;

    let currentDiffText = '';
    let currentOutputFormat = formatSelect.value || 'side-by-side';
    let currentComments = [];
    let currentAiComments = [];
    const commentsController = createInlineDiffCommentsController(container);
    let refreshFrame = null;

    const scheduleCommentRefresh = () => {
        if (refreshFrame !== null) {
            cancelAnimationFrame(refreshFrame);
        }

        refreshFrame = requestAnimationFrame(() => {
            refreshFrame = requestAnimationFrame(() => {
                commentsController.refresh();
                updateCommentUi();
                refreshFrame = null;
            });
        });
    };

    const updateCommentUi = () => {
        const diffCount = countDifferences(currentDiffText);
        const allComments = [...currentComments, ...currentAiComments];
        const commentCount = allComments.filter((comment) => comment?.path && Number.isInteger(comment?.line)).length;
        badge.textContent = `${diffCount} Differences`;
        commentBadge.textContent = `${commentCount} Comments`;
        const hasComments = commentsController.getThreadCount() > 0;
        expandButton.disabled = !hasComments;
        collapseButton.disabled = !hasComments;
    };

    const renderCurrentDiff = () => {
        drawDiff(container, currentDiffText, currentOutputFormat);
        commentsController.render({
            diffText: currentDiffText,
            comments: [...currentComments, ...currentAiComments],
            outputFormat: currentOutputFormat,
        });
        updateCommentUi();
    };

    renderCurrentDiff();

    expandButton.addEventListener('click', function () {
        commentsController.expandAll();
        scheduleCommentRefresh();
    });

    collapseButton.addEventListener('click', function () {
        commentsController.collapseAll();
        scheduleCommentRefresh();
    });

    formatSelect.addEventListener('change', function () {
        currentOutputFormat = formatSelect.value || 'side-by-side';
        renderCurrentDiff();
    });

    document.addEventListener('auditor:diff-selected', function (event) {
        currentDiffText = event?.detail?.diffText || '';
        currentComments = Array.isArray(event?.detail?.comments) ? event.detail.comments : [];
        currentAiComments = [];
        renderCurrentDiff();
    });

    document.addEventListener('auditor:ai-comments-updated', function (event) {
        currentAiComments = Array.isArray(event?.detail?.comments) ? event.detail.comments : [];
        renderCurrentDiff();
    });

    document.addEventListener('auditor:theme-changed', () => {
        renderCurrentDiff();
    });

    const observer = new MutationObserver(() => {
        scheduleCommentRefresh();
    });

    observer.observe(container, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style', 'open'],
    });

    window.addEventListener('resize', scheduleCommentRefresh);
}
