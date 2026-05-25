<section class="diff-section" id="diff-section">
    <div class="diff-wrap">
        <div class="diff-head">
            <h2>Diff Viewer</h2>
            <div class="diff-controls">
                <button type="button" id="diff-comments-expand-btn" class="diff-action-btn">Expand comments</button>
                <button type="button" id="diff-comments-collapse-btn" class="diff-action-btn">Minimize comments</button>
                <select id="diff-format-select" class="diff-format-select" aria-label="Diff format">
                    <option value="side-by-side" selected>Side by side</option>
                    <option value="line-by-line">Top / Bottom</option>
                </select>
                <span class="badge-total" id="diff-mode-badge" hidden>Conflict view</span>
                <span class="badge-total" id="diff-count-badge">0 Differences</span>
                <span class="badge-total" id="diff-comment-badge">0 Comments</span>
            </div>
        </div>
        <div id="conflict-viewer-container"></div>
        <div id="diff2html-container"></div>
        <div class="diff-comments-overlay" id="diff-comments-overlay" aria-hidden="true"></div>
    </div>

    <footer class="site-credit" aria-label="Copyright">
        <div class="site-credit-line"><span class="site-credit-by">by</span> <span class="site-credit-brand">BOLITUPAC</span></div>
        <div class="site-credit-line">&copy;2026</div>
    </footer>
</section>
