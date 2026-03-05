<div class="import-hover-menu">
    <button class="import-hover-item" type="button" data-import-choice="repo">
        <span class="import-hover-label">Import from GitHub repo</span>
        <span class="import-hover-icon" aria-hidden="true">
            <img src="{{ asset('images/github.png') }}" alt="">
        </span>
    </button>
    <button class="import-hover-item" type="button" data-import-choice="upload">
        <span class="import-hover-label">Upload diff file</span>
        <span class="import-hover-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false">
                <path d="M7 3h7l5 5v12a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                <path d="M14 3v5h5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
            </svg>
        </span>
    </button>
    <button class="import-hover-item" type="button" data-import-choice="paste">
        <span class="import-hover-label">Paste diff/code</span>
        <span class="import-hover-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false">
                <path d="M8 6 3.5 12 8 18M16 6 20.5 12 16 18M14 4 10 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
    </button>
</div>
