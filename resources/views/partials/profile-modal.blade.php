<div class="profile-modal" id="profile-modal" aria-hidden="true">
    <div class="profile-modal-backdrop" data-close="profile-modal"></div>
    <div class="profile-modal-card" role="dialog" aria-label="GitHub Profile">
        <button class="profile-modal-close" type="button" aria-label="Close" data-close="profile-modal">&times;</button>

        @auth
            <div class="profile-modal-head">
                <div class="profile-modal-title">GitHub Profile</div>
                <div class="profile-modal-sub">Connected account details</div>
            </div>

            <div class="profile-modal-user">
                <img class="profile-modal-avatar" src="https://github.com/{{ auth()->user()->github_username }}.png"
                    alt="GitHub avatar">
                <div class="profile-modal-meta">
                    <div class="profile-modal-name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="profile-modal-handle">&#64;{{ auth()->user()->github_username ?? 'github-user' }}</div>
                    <div class="profile-modal-email">{{ auth()->user()->email ?? 'no-email' }}</div>
                </div>
            </div>

                <div class="profile-modal-grid">
                    <div class="profile-modal-box">
                        <div class="profile-modal-label">Current Plan</div>
                        <div class="profile-modal-value">
                            <span class="profile-plan-pill">{{ auth()->user()->plan_name ?? 'Free' }}</span>
                        </div>
                    </div>

                    <div class="profile-modal-box" id="profile-ai-key-box" data-status-url="{{ route('profile.ai-key.status') }}"
                        data-save-url="{{ route('profile.ai-key.save') }}" data-remove-url="{{ route('profile.ai-key.remove') }}"
                        data-mode-url="{{ route('profile.ai-key.mode') }}">
                        <div class="profile-modal-label">OpenAI API Key</div>

                        <label class="profile-mode-label" for="profile-ai-key-mode">Key source</label>
                        <select class="profile-mode-select" id="profile-ai-key-mode" aria-label="API key source">
                            <option value="developer">Use system key (10 free requests)</option>
                            <option value="personal">Use my key</option>
                        </select>

                        <input class="profile-api-input" id="profile-api-input" type="password"
                            placeholder="sk-...">

                        <div class="profile-api-actions">
                            <button class="profile-modal-action profile-api-save" id="profile-api-save-btn" type="button"
                                data-loading-btn data-loading-text="Saving">
                                Save key
                            </button>
                            <button class="profile-api-remove-btn" id="profile-api-remove-btn" type="button"
                                data-loading-btn data-loading-text="Removing">
                                Remove key
                            </button>
                        </div>

                        <div class="profile-api-sub" id="profile-ai-key-hint">Choose which key source this account uses for
                            AI chat.</div>
                        <div class="profile-api-state" id="profile-ai-key-state"></div>
                    </div>
                </div>

            <div class="profile-modal-actions">
                <a class="profile-modal-action" href="https://github.com/{{ auth()->user()->github_username }}"
                    target="_blank" rel="noopener noreferrer">
                    View on GitHub
                </a>
                <form id="profile-logout-form" action="{{ route('logout') }}" method="POST" data-loading-form>
                    @csrf
                    <button class="profile-logout-btn" type="submit" data-loading-text="Logging out">Log out</button>
                </form>
                <button class="profile-delete-btn" id="profile-delete-btn" type="button"
                    data-delete-url="{{ route('account.delete') }}">
                    Delete Profile
                </button>
            </div>
        @endauth

        @guest
            <div class="profile-modal-head">
                <div class="profile-modal-title">GitHub Profile</div>
                <div class="profile-modal-sub">You are not logged in yet.</div>
            </div>

            <div class="profile-modal-box">
                <div class="profile-modal-label">Status</div>
                <div class="profile-modal-value">Guest</div>
                <div class="profile-api-sub">Login with GitHub to load repos, pull requests, and your profile plan details.
                </div>
            </div>

            <div class="profile-modal-actions">
                <a class="profile-modal-action" href="{{ route('github.redirect') }}" data-loading-link
                    data-loading-text="Redirecting">
                    Login with GitHub
                </a>
            </div>
        @endguest
    </div>
</div>
