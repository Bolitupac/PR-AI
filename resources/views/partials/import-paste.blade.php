<div class="import-paste-modal" id="import-paste-modal" aria-hidden="true">
    <div class="import-paste-backdrop" data-close="import-paste-modal"></div>
    <div class="import-paste-card" role="dialog" aria-label="Paste diff">
        <button class="import-paste-close" type="button" aria-label="Close" data-close="import-paste-modal">&times;</button>

        <div class="import-paste-head">
            <div class="import-paste-title">Paste diff/code</div>
            <div class="import-paste-sub">Paste a unified diff and load it into the diff viewer.</div>
        </div>

        <textarea class="import-paste-input" id="import-paste-input" placeholder="diff --git a/file.js b/file.js"></textarea>
        <div class="import-paste-state is-info" id="import-paste-state">Waiting for pasted diff.</div>
        <button class="import-paste-action" id="import-paste-action" type="button">Use this diff</button>
    </div>
</div>

