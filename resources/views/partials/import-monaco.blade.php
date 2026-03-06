<div class="import-monaco-modal" id="import-monaco-modal" aria-hidden="true">
    <div class="import-monaco-backdrop" data-close="import-monaco-modal"></div>
    <div class="import-monaco-card" role="dialog" aria-label="Import diff with editor">
        <button class="import-monaco-close" type="button" aria-label="Close" data-close="import-monaco-modal">&times;</button>

        <div class="import-monaco-head">
            <div class="import-monaco-title">Paste diff/code</div>
            <div class="import-monaco-sub">Paste unified git diff in Monaco, then render.</div>
        </div>

        <div class="import-monaco-status is-info" id="import-monaco-status">Paste unified git diff, then render.</div>
        <div id="import-monaco-editor"></div>
        <button class="import-monaco-action" id="import-monaco-render-btn" type="button">Render diff</button>
    </div>
</div>
