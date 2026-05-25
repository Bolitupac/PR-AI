@php
    $sidebarProfileHref = route('profile.show');
    $sidebarLoginHref = route('login');
    $sidebarVcsHref = auth()->check() ? $sidebarProfileHref : $sidebarLoginHref;
    $sidebarVcsLoadingText = auth()->check() ? 'Opening profile' : 'Opening login';
@endphp

<aside class="sidebar-bg" id="app-sidebar">
    <div class="sidebar-top">
        <button class="sidebar-item sidebar-toggle-btn" id="sidebar-toggle-btn" type="button"
            aria-label="Collapse sidebar" aria-expanded="true">
            <span class="sidebar-icon pr-logo-wrapper" aria-hidden="true">
                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo" class="pr-cat-logo">
                <svg viewBox="0 0 24 24" class="sidebar-collapse-svg">
                    <rect x="3.5" y="4.5" width="17" height="15" rx="2.2" fill="none" stroke="currentColor"
                        stroke-width="1.6" />
                    <path d="M9 4.5v15M6 9h0M6 12h0M6 15h0" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" />
                </svg>
            </span>
            <span class="sidebar-label">Collapse sidebar</span>
        </button>

        <hr class="sidebar-separator">

        <a class="sidebar-item {{ request()->is('auditor*') || request()->is('/') ? 'is-active' : '' }}"
            href="{{ url('/auditor') }}" aria-label="Auditor" data-loading-link data-loading-text="Opening">
            <span class="sidebar-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path
                        d="M6.5 4.5h8.8l3.2 3.2v11.8a1.8 1.8 0 0 1-1.8 1.8H6.5a1.8 1.8 0 0 1-1.8-1.8V6.3a1.8 1.8 0 0 1 1.8-1.8Z"
                        fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                    <path d="M15.3 4.5v3.2h3.2M8 15.6l4.8-4.8 1.8 1.8-4.8 4.8H8zM12.4 11.2l1.4-1.4 1.8 1.8-1.4 1.4"
                        fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </span>
            <span class="sidebar-label">Auditor</span>
        </a>

        <a class="sidebar-item {{ request()->routeIs('imports.*') ? 'is-active' : '' }}"
            href="{{ route('imports.index') }}" aria-label="Import" data-loading-link data-loading-text="Opening">
            <span class="sidebar-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M4 14v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4M12 4v11m0 0-3-3m3 3 3-3" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
            <span class="sidebar-label">Import</span>
        </a>

        {{-- Apps button — hidden on the imports page --}}
        @unless(request()->routeIs('imports.*'))
        <div class="sidebar-item-wrap install-apps-wrap" style="position: relative;">
            <button class="sidebar-item" id="apps-trigger-btn" type="button" aria-label="Apps">
                <span class="sidebar-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                        <path d="M3 16.5C3 14.567 4.567 13 6.5 13C8.433 13 10 14.567 10 16.5C10 18.433 8.433 20 6.5 20C4.567 20 3 18.433 3 16.5Z"></path>
                    </svg>
                </span>
                <span class="sidebar-label">Apps</span>
            </button>
        </div>
        @endunless

        {{-- VCS providers — always visible on imports page, and when set elsewhere --}}
        @if(request()->routeIs('imports.*'))
            @isset($vcsProviders)
            <div class="sidebar-vcs-group"
                style="margin-top: 12px; border-top: 1px solid var(--panel-stroke); padding-top: 12px; display:flex; flex-direction:column; gap:4px;">
                <span class="sidebar-label" style="opacity:1; max-width:none; font-size:10px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--text-soft); padding: 0 6px 4px;">VCS Providers</span>
                @foreach ($vcsProviders as $provider)
                    <button class="sidebar-item" type="button" aria-label="{{ $provider['name'] }}"
                        data-page-loading-href="{{ $sidebarVcsHref }}" data-loading-text="{{ $sidebarVcsLoadingText }}">
                        <span class="sidebar-icon" aria-hidden="true">
                            @if($provider['name'] === 'GitHub')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.161 22 16.416 22 12c0-5.523-4.477-10-10-10z"
                                        fill="currentColor" />
                                </svg>
                            @elseif($provider['name'] === 'GitLab')
                                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none">
                                    <path
                                        d="M23.955 10.37L21.316 2.246a.82.82 0 0 0-1.564 0l-2.07 6.386H6.315L4.246 2.246a.82.82 0 0 0-1.564 0L.044 10.37a.822.822 0 0 0 .296.907L12 20.59l11.66-9.311a.822.822 0 0 0 .295-.91z"
                                        fill="#FC6D26" />
                                    <path
                                        d="M12 20.59L.044 10.37a.822.822 0 0 1-.296-.906L2.68 1.34a.82.82 0 0 1 1.564 0l2.07 6.386H12v12.863z"
                                        fill="#E24329" />
                                    <path d="M12 20.59V8.632H6.315L12 20.59z" fill="#FCA326" />
                                    <path
                                        d="M12 20.59l11.956-10.22a.822.822 0 0 0 .295-.91L21.32 1.34a.82.82 0 0 0-1.564 0l-2.07 6.386H12v12.863z"
                                        fill="#E24329" />
                                    <path d="M12 20.59V8.632h5.685L12 20.59z" fill="#FCA326" />
                                </svg>
                            @elseif($provider['name'] === 'Bitbucket')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M1.082 3.6A1.666 1.666 0 0 1 2.748 2h18.52a1.666 1.666 0 0 1 1.644 1.889l-2.613 15.013A1.666 1.666 0 0 1 18.656 20H5.319a1.666 1.666 0 0 1-1.644-1.39l-2.593-15.01zm13.195 10.23L15.65 8H8.38l1.373 5.83h4.524z"
                                        fill="#0052CC" />
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="2" y="2" width="9" height="9" fill="#00A4EF" />
                                    <rect x="2" y="12" width="9" height="9" fill="#00A4EF" />
                                    <rect x="12" y="2" width="9" height="9" fill="#00A4EF" />
                                    <rect x="12" y="12" width="9" height="9" fill="#00A4EF" />
                                </svg>
                            @endif
                        </span>
                        <span class="sidebar-profile-meta">
                            <span class="sidebar-profile-name">{{ $provider['name'] }}</span>
                            <span class="sidebar-profile-plan">{{ $provider['state'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
            @endisset
        @else
            @isset($vcsProviders)
            <div class="sidebar-vcs-group"
                style="margin-top: 12px; border-top: 1px solid var(--panel-stroke); padding-top: 12px; display:flex; flex-direction:column; gap:4px;">
                @foreach ($vcsProviders as $provider)
                    <button class="sidebar-item" type="button" aria-label="{{ $provider['name'] }}"
                        data-page-loading-href="{{ $sidebarVcsHref }}" data-loading-text="{{ $sidebarVcsLoadingText }}">
                        <span class="sidebar-icon" aria-hidden="true">
                            @if($provider['name'] === 'GitHub')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.161 22 16.416 22 12c0-5.523-4.477-10-10-10z"
                                        fill="currentColor" />
                                </svg>
                            @elseif($provider['name'] === 'GitLab')
                                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none">
                                    <path d="M23.955 10.37L21.316 2.246a.82.82 0 0 0-1.564 0l-2.07 6.386H6.315L4.246 2.246a.82.82 0 0 0-1.564 0L.044 10.37a.822.822 0 0 0 .296.907L12 20.59l11.66-9.311a.822.822 0 0 0 .295-.91z" fill="#FC6D26"/>
                                    <path d="M12 20.59L.044 10.37a.822.822 0 0 1-.296-.906L2.68 1.34a.82.82 0 0 1 1.564 0l2.07 6.386H12v12.863z" fill="#E24329"/>
                                    <path d="M12 20.59V8.632H6.315L12 20.59z" fill="#FCA326"/>
                                    <path d="M12 20.59l11.956-10.22a.822.822 0 0 0 .295-.91L21.32 1.34a.82.82 0 0 0-1.564 0l-2.07 6.386H12v12.863z" fill="#E24329"/>
                                    <path d="M12 20.59V8.632h5.685L12 20.59z" fill="#FCA326"/>
                                </svg>
                            @elseif($provider['name'] === 'Bitbucket')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M1.082 3.6A1.666 1.666 0 0 1 2.748 2h18.52a1.666 1.666 0 0 1 1.644 1.889l-2.613 15.013A1.666 1.666 0 0 1 18.656 20H5.319a1.666 1.666 0 0 1-1.644-1.39l-2.593-15.01zm13.195 10.23L15.65 8H8.38l1.373 5.83h4.524z" fill="#0052CC"/>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="2" y="2" width="9" height="9" fill="#00A4EF" />
                                    <rect x="2" y="12" width="9" height="9" fill="#00A4EF" />
                                    <rect x="12" y="2" width="9" height="9" fill="#00A4EF" />
                                    <rect x="12" y="12" width="9" height="9" fill="#00A4EF" />
                                </svg>
                            @endif
                        </span>
                        <span class="sidebar-profile-meta">
                            <span class="sidebar-profile-name">{{ $provider['name'] }}</span>
                            <span class="sidebar-profile-plan">{{ $provider['state'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
            @endisset
        @endif
    </div>

    <div class="sidebar-bottom">
        {{-- Simplified theme toggle: single icon + label, no track/thumb --}}
        <button class="sidebar-item sidebar-theme-toggle" id="theme-toggle-btn" type="button" aria-label="Switch to dark mode">
            <span class="sidebar-icon" aria-hidden="true">
                <svg class="theme-toggle-icon theme-toggle-icon--sun" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="4.1" fill="currentColor" />
                    <path
                        d="M12 2.8v2.2m0 14v2.2m9.2-9.2h-2.2M5 12H2.8m15.7-6.7-1.6 1.6M7.1 16.9l-1.6 1.6m0-13.2 1.6 1.6m9.8 9.8 1.6 1.6"
                        fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
                <svg class="theme-toggle-icon theme-toggle-icon--moon" viewBox="0 0 24 24">
                    <path d="M14.5 3.5a8.5 8.5 0 1 0 6 14.5A9 9 0 1 1 14.5 3.5Z" fill="currentColor" />
                </svg>
            </span>
            <span class="sidebar-label" id="theme-toggle-label">Dark mode</span>
        </button>

        <button class="sidebar-item" id="sidebar-settings-btn" type="button" aria-label="Settings">
            <span class="sidebar-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="2.8" fill="none" stroke="currentColor" stroke-width="1.8" />
                    <path
                        d="m19 12 2-1.2-1.4-2.4-2.3.5a6.9 6.9 0 0 0-1.1-1.1l.5-2.3-2.4-1.4L12 5 10.8 3 8.4 4.4l.5 2.3a6.9 6.9 0 0 0-1.1 1.1l-2.3-.5L4.1 9.7 6 10.9 5 12l1 1.1-1.9 1.2 1.4 2.4 2.3-.5c.3.4.7.8 1.1 1.1l-.5 2.3 2.4 1.4L12 19l1.2 2 2.4-1.4-.5-2.3c.4-.3.8-.7 1.1-1.1l2.3.5 1.4-2.4L19 12Z"
                        fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"
                        stroke-linecap="round" />
                </svg>
            </span>
            <span class="sidebar-label">Settings</span>
        </button>

        @auth
            <button class="sidebar-item sidebar-profile-item" id="open-profile-btn" type="button"
                aria-label="{{ auth()->user()->github_username ?? 'Profile' }}">
                <span class="sidebar-icon sidebar-avatar-wrap" aria-hidden="true">
                    <img class="profile-avatar-img" src="https://github.com/{{ auth()->user()->github_username }}.png"
                        alt="GitHub avatar" loading="lazy">
                </span>
                <span class="sidebar-profile-meta">
                    <span
                        class="sidebar-profile-name">{{ auth()->user()->github_username ?? auth()->user()->name ?? 'user' }}</span>
                    <span class="sidebar-profile-plan">Free</span>
                </span>
            </button>
        @endauth
        @guest
            @php
                $guestConnectedProvider = !empty($defaultVcsProvider['connected'] ?? false) ? $defaultVcsProvider : null;
                $guestConnectHref = $guestConnectedProvider
                    ? (($guestConnectedProvider['key'] ?? '') === 'github' ? route('github.redirect') : '#settings-vcs')
                    : '#settings-vcs';
                $guestLabel = $guestConnectedProvider
                    ? (($guestConnectedProvider['name'] ?? 'VCS').' connected')
                    : 'Connect VCS';
            @endphp
            <a class="sidebar-item sidebar-profile-item" href="{{ $guestConnectHref }}" aria-label="{{ $guestLabel }}"
                data-loading-link data-loading-text="Opening">
                <span class="sidebar-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.7" />
                        <path
                            d="M9 17c0-2.3 6-2.3 6 0M9.5 10.8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm5 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"
                            fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </span>
                <span class="sidebar-profile-meta">
                    <span class="sidebar-profile-name">{{ $guestLabel }}</span>
                    <span class="sidebar-profile-plan">{{ $guestConnectedProvider ? 'Session' : 'Free' }}</span>
                </span>
            </a>
        @endguest
    </div>
</aside>
