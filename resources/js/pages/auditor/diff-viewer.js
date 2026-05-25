import { createInlineDiffCommentsController } from './diff-comments';
import { initConflictViewer } from './conflict-viewer';

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

function setDiffMode(mode) {
    const conflictContainer = document.getElementById('conflict-viewer-container');
    const diffContainer = document.getElementById('diff2html-container');
    const modeBadge = document.getElementById('diff-mode-badge');
    const formatSelect = document.getElementById('diff-format-select');

    const isConflict = mode === 'conflict';
    if (conflictContainer) {
        conflictContainer.classList.toggle('is-active', isConflict);
    }
    if (diffContainer) {
        diffContainer.classList.toggle('is-hidden', isConflict);
    }
    if (modeBadge) {
        modeBadge.hidden = !isConflict;
    }
    if (formatSelect) {
        formatSelect.disabled = isConflict;
    }
}

// Initializes the bottom diff viewer and listens for diff selection events.
export function initDiffViewer() {
    const container = document.getElementById('diff2html-container');
    const conflictContainer = document.getElementById('conflict-viewer-container');
    const badge = document.getElementById('diff-count-badge');
    const commentBadge = document.getElementById('diff-comment-badge');
    const formatSelect = document.getElementById('diff-format-select');
    const expandButton = document.getElementById('diff-comments-expand-btn');
    const collapseButton = document.getElementById('diff-comments-collapse-btn');
    if (!container || !badge || !commentBadge || !formatSelect || !expandButton || !collapseButton) return;

    const conflictViewer = initConflictViewer(conflictContainer);

    let currentDiffText = '';
    let currentOutputFormat = formatSelect.value || 'side-by-side';
    let currentComments = [];
    let currentAiComments = [];
    let currentMode = 'standard';
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
        const diffCount = currentMode === 'conflict'
            ? (currentDiffText.match(/<<<<<<<|=======|>>>>>>>/g) || []).length
            : countDifferences(currentDiffText);
        const allComments = [...currentComments, ...currentAiComments];
        const commentCount = allComments.filter((comment) => comment?.path && Number.isInteger(comment?.line)).length;
        badge.textContent = currentMode === 'conflict' ? `${diffCount} Conflict markers` : `${diffCount} Differences`;
        commentBadge.textContent = `${commentCount} Comments`;
        const hasComments = commentsController.getThreadCount() > 0;
        expandButton.disabled = !hasComments || currentMode === 'conflict';
        collapseButton.disabled = !hasComments || currentMode === 'conflict';
    };

    const renderCurrentDiff = () => {
        if (currentMode === 'conflict') {
            setDiffMode('conflict');
            updateCommentUi();
            return;
        }

        setDiffMode('standard');
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
        currentMode = 'standard';
        currentDiffText = event?.detail?.diffText || '';
        currentComments = Array.isArray(event?.detail?.comments) ? event.detail.comments : [];
        currentAiComments = [];
        conflictViewer.clear();
        renderCurrentDiff();

        const scrollBtn = document.getElementById('diff-ready-scroll-btn');
        if (scrollBtn && currentDiffText) {
            scrollBtn.style.display = 'block';
            setTimeout(() => scrollBtn.style.opacity = '1', 10);

            scrollBtn.onclick = () => {
                const diffSection = document.getElementById('diff2html-container');
                if (diffSection) {
                    diffSection.scrollIntoView({ behavior: 'smooth' });
                }
                scrollBtn.style.opacity = '0';
                setTimeout(() => scrollBtn.style.display = 'none', 200);
            };
        }
    });

    document.addEventListener('auditor:conflicts-selected', function (event) {
        currentMode = 'conflict';
        currentDiffText = event?.detail?.diffText || '';
        currentComments = [];
        currentAiComments = [];
        conflictViewer.render(event?.detail?.conflictData || {});
        setDiffMode('conflict');
        updateCommentUi();

        const scrollBtn = document.getElementById('diff-ready-scroll-btn');
        if (scrollBtn) {
            scrollBtn.style.display = 'block';
            setTimeout(() => scrollBtn.style.opacity = '1', 10);
            scrollBtn.onclick = () => {
                conflictContainer?.scrollIntoView({ behavior: 'smooth' });
                scrollBtn.style.opacity = '0';
                setTimeout(() => scrollBtn.style.display = 'none', 200);
            };
        }
    });

    document.addEventListener('auditor:ai-comments-updated', function (event) {
        currentAiComments = Array.isArray(event?.detail?.comments) ? event.detail.comments : [];
        if (currentMode === 'standard') {
            renderCurrentDiff();
        }
    });

    document.addEventListener('auditor:theme-changed', () => {
        renderCurrentDiff();
    });

    const observer = new MutationObserver(() => {
        if (currentMode === 'standard') {
            scheduleCommentRefresh();
        }
    });

    observer.observe(container, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style', 'open'],
    });

    window.addEventListener('resize', scheduleCommentRefresh);
}
