<div class="repo-modal" id="repo-modal" aria-hidden="true">
    <div class="repo-modal-backdrop" data-close="repo-modal"></div>
    <div class="repo-modal-card" role="dialog" aria-label="Import from GitHub">
        <button class="repo-modal-close" type="button" aria-label="Close" data-close="repo-modal">×</button>

        <div class="repo-import-head">
            <div class="repo-import-title">Import from GitHub</div>
            <div class="repo-import-sub">Choose a repository to load into the auditor.</div>
        </div>

        <label class="repo-import-label" for="repo-import-select">Repository</label>
        <select class="repo-import-select" id="repo-import-select">
            <option selected disabled>Loading repositories...</option>
        </select>

        <button class="repo-import-action" type="button">Load repo</button>
    </div>
</div>
