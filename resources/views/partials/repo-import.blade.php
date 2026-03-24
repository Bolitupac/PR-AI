<div class="repo-modal" id="repo-modal" aria-hidden="true"
    data-repos-url="{{ route('github.repos') }}"
    data-pulls-url="{{ route('github.pulls') }}"
    data-pull-diff-url="{{ route('github.pull-diff') }}"
    data-connect-url="{{ route('github.redirect') }}">
    <div class="repo-modal-backdrop" data-close="repo-modal"></div>
    <div class="repo-modal-card" role="dialog" aria-label="Import from GitHub">
        <button class="repo-modal-close" type="button" aria-label="Close" data-close="repo-modal">&times;</button>

        <div class="repo-import-head">
            <div class="repo-import-title">Import from GitHub</div>
            <div class="repo-import-sub">Choose a repository to load into the auditor.</div>
        </div>

        <label class="repo-import-label" for="repo-import-select">Repository</label>
        <select class="repo-import-select" id="repo-import-select">
            <option selected disabled>Loading repositories...</option>
        </select>
        <div class="repo-import-help" id="repo-import-help"></div>
        <div class="repo-import-actions-row" id="repo-import-actions-row" hidden>
            <a class="repo-import-secondary-action" id="repo-connect-github-btn" href="{{ route('github.redirect') }}">
                Connect GitHub
            </a>
            <button class="repo-import-secondary-action" id="repo-retry-btn" type="button">Retry</button>
        </div>

        <div class="repo-pr-box" id="repo-pr-box">
            <div class="repo-pr-state" id="repo-pr-state">Select a repository to view pull requests.</div>
            <div class="repo-pr-list" id="repo-pr-list"></div>
        </div>

        <div class="repo-load-cue" id="repo-load-cue" aria-live="polite">
            <span class="repo-load-cue-dot" aria-hidden="true"></span>
            Repo loaded successfully
        </div>

        <button class="repo-import-action" id="load-repo-btn" type="button" disabled>Load repo</button>
    </div>
</div>
