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
            <div class="profile_modal_user">
                <p>this is where the ui will be </p>
            </div>

        </div>
    </div>
@endauth

