@auth
    <div class="profile-modal" id="profile-modal" aria-hidden="true">
        <div class="profile-modal-backdrop" data-close="profile-modal"></div>
        <div class="profile-modal-card" role="dialog" aria-label="GitHub Profile">
            <button class="profile-modal-close" type="button" aria-label="Close" data-close="profile-modal">&times;</button>

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

                <div class="profile-modal-box">
                    <div class="profile-modal-label">API Key</div>
                    <input class="profile-api-input" type="password" placeholder="Enter API key for custom provider">
                    <div class="profile-api-sub">Stored securely when backend save is connected.</div>
                </div>
            </div>

            <div class="profile-modal-actions">
                <a class="profile-modal-action" href="https://github.com/{{ auth()->user()->github_username }}"
                    target="_blank" rel="noopener noreferrer">
                    View on GitHub
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="profile-logout-btn" type="submit">Log out</button>
                </form>
            </div>

        </div>
    </div>
@endauth
