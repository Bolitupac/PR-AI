// Counts changed lines to drive the differences badge.
function countDifferences(diffText) {
    if (!diffText) return 0;
    return diffText.split('\n').reduce((count, line) => {
        if (line.startsWith('+++') || line.startsWith('---')) return count;
        if (line.startsWith('+') || line.startsWith('-')) return count + 1;
        return count;
    }, 0);
}

// Renders diff text with diff2html or an empty placeholder.
function drawDiff(container, diffText) {
    container.innerHTML = '';

    if (!diffText || !diffText.trim()) {
        container.innerHTML = '<div class="diff-empty">No differences to display.</div>';
        return;
    }

    const diffUi = new window.Diff2HtmlUI(container, diffText, {
        drawFileList: true,
        outputFormat: 'side-by-side',
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
    if (!container || !badge) return;

    drawDiff(container, '');

    document.addEventListener('auditor:diff-selected', function (event) {
        const diffText = event?.detail?.diffText || '';
        const diffCount = countDifferences(diffText);
        badge.textContent = `${diffCount} Differences`;
        drawDiff(container, diffText);
    });
}
