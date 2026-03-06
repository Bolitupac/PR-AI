// Counts changed lines to drive the differences badge.
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
    const formatSelect = document.getElementById('diff-format-select');
    if (!container || !badge || !formatSelect) return;

    let currentDiffText = '';
    let currentOutputFormat = formatSelect.value || 'side-by-side';

    const renderCurrentDiff = () => {
        drawDiff(container, currentDiffText, currentOutputFormat);
    };

    renderCurrentDiff();

    formatSelect.addEventListener('change', function () {
        currentOutputFormat = formatSelect.value || 'side-by-side';
        renderCurrentDiff();
    });

    document.addEventListener('auditor:diff-selected', function (event) {
        currentDiffText = event?.detail?.diffText || '';
        const diffCount = countDifferences(currentDiffText);
        badge.textContent = `${diffCount} Differences`;
        renderCurrentDiff();
    });

    document.addEventListener('auditor:theme-changed', () => {
        renderCurrentDiff();
    });
}
