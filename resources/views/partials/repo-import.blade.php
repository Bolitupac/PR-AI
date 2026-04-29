@php
    $repoModalProviders = collect($vcsProviders ?? [])->mapWithKeys(function ($provider) {
        return [
            $provider['key'] => [
                'label' => $provider['name'],
                'connected' => (bool) ($provider['connected'] ?? false),
            ],
        ];
    })->all();
@endphp

<div class="repo-modal" id="repo-modal" aria-hidden="true"
    data-vcs-api-base="{{ url('/api/vcs') }}"
    data-default-provider="{{ $defaultVcsProviderKey ?? 'github' }}"
    data-vcs-providers='@json($repoModalProviders)'>
    <div class="repo-modal-backdrop" data-close="repo-modal"></div>
    <div class="repo-modal-card" role="dialog" aria-label="Import from repository provider">
        <button class="repo-modal-close" type="button" aria-label="Close" data-close="repo-modal">&times;</button>

        <div class="repo-import-head">
            <div class="repo-import-title">Import from repository provider</div>
            <div class="repo-import-sub">Choose a repository to load into the auditor.</div>
        </div>

        <label class="repo-import-label" for="repo-provider-select">Provider</label>
        <select class="repo-import-select" id="repo-provider-select">
            @foreach (($vcsProviders ?? []) as $provider)
                <option value="{{ $provider['key'] }}" @selected(($defaultVcsProviderKey ?? 'github') === $provider['key'])>
                    {{ $provider['name'] }}{{ !empty($provider['connected']) ? '' : ' (not connected)' }}
                </option>
            @endforeach
        </select>

        <label class="repo-import-label" for="repo-import-select">Repository</label>
        <select class="repo-import-select" id="repo-import-select">
            <option selected disabled>Loading repositories...</option>
        </select>
        <div class="repo-import-help" id="repo-import-help"></div>
        <div class="repo-import-actions-row" id="repo-import-actions-row" hidden>
            <a class="repo-import-secondary-action" id="repo-connect-github-btn" href="#settings-vcs">
                Open provider settings
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
