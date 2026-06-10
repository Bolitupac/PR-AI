@php
    $settingsVcsProviders = $vcsProviders ?? [
        ['name' => 'GitHub', 'state' => 'Connected'],
        ['name' => 'GitLab', 'state' => 'Available'],
        ['name' => 'Bitbucket', 'state' => 'Coming Soon'],
        ['name' => 'Azure DevOps', 'state' => 'Coming Soon'],
    ];
@endphp

<div class="settings-modal" id="settings-modal" aria-hidden="true">
    <div class="settings-modal-backdrop" data-close="settings-modal"></div>

    <section class="settings-modal-card" role="dialog" aria-modal="true" aria-label="Settings">
        <button class="settings-modal-close" type="button" aria-label="Close settings" data-close="settings-modal">&times;</button>

        <div class="settings-modal-layout">
            <aside class="settings-modal-sidebar" aria-label="Settings sections">
                <button class="settings-nav-btn is-active" type="button" data-settings-tab="general">
                    <span class="settings-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="2.8" fill="none" stroke="currentColor" stroke-width="1.8" />
                            <path d="m19 12 2-1.2-1.4-2.4-2.3.5a6.9 6.9 0 0 0-1.1-1.1l.5-2.3-2.4-1.4L12 5 10.8 3 8.4 4.4l.5 2.3a6.9 6.9 0 0 0-1.1 1.1l-2.3-.5L4.1 9.7 6 10.9 5 12l1 1.1-1.9 1.2 1.4 2.4 2.3-.5c.3.4.7.8 1.1 1.1l-.5 2.3 2.4 1.4L12 19l1.2 2 2.4-1.4-.5-2.3c.4-.3.8-.7 1.1-1.1l2.3.5 1.4-2.4L19 12Z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span>General</span>
                </button>

                <button class="settings-nav-btn" type="button" data-settings-tab="api-keys">
                    <span class="settings-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M9.2 12.8a4.6 4.6 0 1 1 1.5 1.5L8.6 16.4v1.8H6.8V20H5v2H2v-3.5l6.2-5.7Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="16.4" cy="7.6" r="1.5" fill="currentColor"/>
                        </svg>
                    </span>
                    <span>API Keys</span>
                </button>

                <button class="settings-nav-btn" type="button" data-settings-tab="profile">
                    <span class="settings-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="3.2" fill="none" stroke="currentColor" stroke-width="1.7" />
                            <path d="M5 19.2c1.6-3.1 4.1-4.7 7-4.7s5.4 1.6 7 4.7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span>Profile</span>
                </button>

                <button class="settings-nav-btn" type="button" data-settings-tab="ai-settings">
                    <span class="settings-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3.5a3 3 0 0 1 3 3v.6a4.7 4.7 0 0 1 1.9 1.1l.5-.3a3 3 0 1 1 3 5.2l-.6.3v1.2l.6.3a3 3 0 1 1-3 5.2l-.5-.3a4.7 4.7 0 0 1-1.9 1.1v.6a3 3 0 1 1-6 0V21a4.7 4.7 0 0 1-1.9-1.1l-.5.3a3 3 0 1 1-3-5.2l.6-.3v-1.2l-.6-.3a3 3 0 1 1 3-5.2l.5.3A4.7 4.7 0 0 1 9 7.1v-.6a3 3 0 0 1 3-3Z" fill="none" stroke="currentColor" stroke-width="1.5" />
                            <circle cx="12" cy="12" r="2.6" fill="none" stroke="currentColor" stroke-width="1.5" />
                        </svg>
                    </span>
                    <span>AI Settings</span>
                </button>

                <button class="settings-nav-btn" type="button" data-settings-tab="vcs">
                    <span class="settings-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="6" cy="6" r="2.2" fill="none" stroke="currentColor" stroke-width="1.6" />
                            <circle cx="18" cy="6" r="2.2" fill="none" stroke="currentColor" stroke-width="1.6" />
                            <circle cx="12" cy="18" r="2.2" fill="none" stroke="currentColor" stroke-width="1.6" />
                            <path d="M8 7.3 10.8 16m4.2-8.7L13.2 16M8.2 6h7.6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span>VCS</span>
                </button>

                <button class="settings-nav-btn" type="button" data-settings-tab="help">
                    <span class="settings-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6" />
                            <path d="M12 17v.01M9 10a3 3 0 0 1 6 0c0 1.5-1.5 2.5-3 3.5v1" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span>Help</span>
                </button>
                <div class="settings-nav-sub" id="help-nav-sub" style="display:none;">
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-c2">The Auditor Workspace</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-c3">Importing Code</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-c4">Auditing Modes</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-c5">DocGen Mode</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-c6">Voice Interactions</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-c7">Git Diffs</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-c8">Advanced Features</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-vapt">VAPT &amp; OWASP Audit</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-c9">API Keys</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-c10">Roadmap &amp; Known Issues</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-developer">About Developer</button>
                </div>
            </aside>

            <div class="settings-modal-content">
                <section class="settings-pane is-active" data-settings-pane="general">
                    <header class="settings-pane-head">
                        <h3>General</h3>
                        <p>Application preferences and appearance.</p>
                    </header>

                    <div class="settings-gh-section">
                        <div class="settings-gh-row-head">
                            <div class="settings-gh-row-title">Theme</div>
                            <div class="settings-gh-row-sub">Choose your preferred color mode.</div>
                        </div>
                        <div class="settings-theme-actions">
                            <button class="settings-theme-btn" type="button" data-theme-select="light">Light</button>
                            <button class="settings-theme-btn" type="button" data-theme-select="dark">Dark</button>
                            <button class="settings-theme-btn" type="button" data-theme-select="toggle">Toggle</button>
                        </div>
                    </div>

                    <div class="settings-gh-section">
                        <div class="settings-gh-row-head">
                            <div class="settings-gh-row-title">Workspace</div>
                            <div class="settings-gh-row-sub">Quick preferences for your review workflow.</div>
                        </div>
                        <label class="settings-demo-toggle">
                            <input type="checkbox" checked>
                            <span>Auto-open latest diff after import</span>
                        </label>
                        <label class="settings-demo-toggle">
                            <input type="checkbox" checked>
                            <span>Show recent activity on Imports page</span>
                        </label>
                        <label class="settings-demo-toggle">
                            <input type="checkbox">
                            <span>Compact repository list layout</span>
                        </label>
                    </div>

                    <div class="settings-gh-section">
                        <div class="settings-gh-row-head">
                            <div class="settings-gh-row-title">Notifications</div>
                            <div class="settings-gh-row-sub">Demo controls for in-app alerts.</div>
                        </div>
                        <label class="settings-demo-toggle">
                            <input type="checkbox" checked>
                            <span>Notify when audit finishes</span>
                        </label>
                        <label class="settings-demo-toggle">
                            <input type="checkbox">
                            <span>Notify on pull request import errors</span>
                        </label>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="profile">
                    <header class="settings-pane-head">
                        <h3>Profile</h3>
                        <p>Your account, plan, and AI key status.</p>
                    </header>

                    @auth
                        <div class="settings-profile-user">
                            <img class="profile-modal-avatar" src="https://github.com/{{ auth()->user()->github_username }}.png"
                                alt="GitHub avatar" style="width:58px;height:58px;border-radius:50%;border:2px solid #e0e6f2;flex-shrink:0;">
                            <div style="display:flex;flex-direction:column;gap:2px;flex:1;min-width:0;">
                                <span style="font-size:16px;font-weight:700;color:var(--text-main);letter-spacing:-0.03em;">{{ auth()->user()->name ?? 'User' }}</span>
                                <span style="font-size:13px;color:#5e6475;">&#64;{{ auth()->user()->github_username ?? 'github-user' }}</span>
                                <span style="font-size:12px;color:#7c8398;">{{ auth()->user()->email ?? 'no-email' }}</span>
                            </div>
                            <form action="{{ route('logout') }}" method="POST" data-loading-form style="flex-shrink:0;">
                                @csrf
                                <button class="profile-logout-btn" type="submit" data-loading-text="Logging out" style="white-space:nowrap;">Log out</button>
                            </form>
                        </div>

                        <div class="settings-profile-grid">
                            {{-- Plan --}}
                            <div class="profile-modal-box">
                                <div class="profile-modal-label">Current Plan</div>
                                <div class="profile-modal-value">
                                    <span class="profile-plan-pill">{{ auth()->user()->plan_name ?? 'Free' }}</span>
                                </div>
                            </div>

                            {{-- OpenAI Key Status --}}
                            <div class="profile-modal-box profile-key-status-box" id="settings-profile-openai-status-box"
                                style="cursor:pointer;" data-nav-to="api-keys" title="Click to manage API keys">
                                <div class="profile-modal-label">OpenAI Key</div>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img src="{{ asset('images/openailogo.png') }}" alt="OpenAI" style="width:20px;height:20px;object-fit:contain;">
                                    <span id="settings-profile-openai-badge" style="font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;background:rgba(45,164,78,0.1);color:#1a7f37;border:1px solid rgba(45,164,78,0.3);">Developer key active</span>
                                </div>
                            </div>

                            {{-- DeepSeek Key Status --}}
                            <div class="profile-modal-box profile-key-status-box" id="settings-profile-deepseek-status-box"
                                style="cursor:pointer;" data-nav-to="api-keys" title="Click to manage API keys">
                                <div class="profile-modal-label">DeepSeek Key</div>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img src="{{ asset('images/deepseeklogo.png') }}" alt="DeepSeek" style="width:20px;height:20px;object-fit:contain;">
                                    <span id="settings-profile-deepseek-badge" style="font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;background:rgba(45,164,78,0.1);color:#1a7f37;border:1px solid rgba(45,164,78,0.3);">Developer key active</span>
                                </div>
                            </div>

                            {{-- GitHub Account --}}
                            <div class="profile-modal-box">
                                <div class="profile-modal-label">GitHub Account</div>
                                <div class="profile-modal-value">{{ auth()->user()->github_username ?? 'Connected' }}</div>
                                <div class="profile-api-sub">Used to sign in and access PR ai.</div>
                            </div>
                        </div>

                        <div class="settings-profile-actions">
                            <a class="profile-modal-action" href="https://github.com/{{ auth()->user()->github_username }}"
                                target="_blank" rel="noopener noreferrer">
                                View on GitHub
                            </a>
                            <button class="profile-delete-btn" id="settings-profile-delete-btn" type="button"
                                data-delete-url="{{ route('account.delete') }}">
                                Delete Profile
                            </button>
                        </div>
                    @endauth

                    @guest
                        <div class="profile-modal-box" style="text-align:center;padding:32px;">
                            <div class="profile-modal-label">Status</div>
                            <div class="profile-modal-value">Guest</div>
                            <div class="profile-api-sub" style="margin-top:8px;">Log in with GitHub to access profile settings.</div>
                        </div>
                        <div class="settings-profile-actions" style="justify-content:center;">
                            <a class="profile-modal-action" href="{{ route('login') }}">Go to login</a>
                        </div>
                    @endguest
                </section>

                <section class="settings-pane" data-settings-pane="api-keys">
                    <header class="settings-pane-head">
                        <h3>API Keys</h3>
                        <p>Real AI providers — your keys, your control.</p>
                    </header>

                    <div class="settings-provider-list">
                        {{-- OpenAI --}}
                        <article class="settings-provider-item settings-provider-item--active">
                            <div class="settings-provider-top">
                                <div class="settings-provider-brand">
                                    <span class="settings-provider-logo" aria-hidden="true">
                                        <img src="{{ asset('images/openailogo.png') }}" alt="OpenAI" style="width:24px;height:24px;object-fit:contain;">
                                    </span>
                                    <div>
                                        <div class="settings-provider-name">OpenAI</div>
                                        <div class="settings-provider-sub" id="settings-openai-status-text">Developer key active</div>
                                    </div>
                                </div>
                                <span class="settings-provider-pill is-active" id="settings-openai-pill">Active</span>
                            </div>

                            @auth
                                <div class="settings-api-box" id="settings-ai-key-box"
                                    data-status-url="{{ route('profile.ai-key.status') }}"
                                    data-save-url="{{ route('profile.ai-key.save') }}"
                                    data-remove-url="{{ route('profile.ai-key.remove') }}"
                                    data-mode-url="{{ route('profile.ai-key.mode') }}">
                                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                        <span style="font-size:13px;font-weight:600;color:var(--text-main);">Key source:</span>
                                        <select class="settings-mode-select" id="settings-ai-key-mode" aria-label="OpenAI key source" style="width:auto;margin-bottom:0;">
                                            <option value="developer">Developer key</option>
                                            <option value="personal">Personal key</option>
                                        </select>
                                        <span id="settings-openai-status-badge" style="font-size:11px;padding:3px 8px;border-radius:999px;font-weight:600;background:rgba(45,164,78,0.1);color:#1a7f37;border:1px solid rgba(45,164,78,0.3);">Developer</span>
                                    </div>
                                    <div id="settings-openai-key-row" style="display:none;margin-top:10px;">
                                        <input class="settings-api-input" id="settings-api-input" type="password" placeholder="sk-...">
                                        <div class="settings-api-actions" style="margin-top:8px;">
                                            <button class="settings-api-save-btn" id="settings-api-save-btn" type="button" data-loading-btn data-loading-text="Saving">Save key</button>
                                            <button class="settings-api-remove-btn" id="settings-api-remove-btn" type="button" data-loading-btn data-loading-text="Removing">Remove key</button>
                                        </div>
                                    </div>
                                    <p class="settings-api-state" id="settings-ai-key-state"></p>
                                </div>
                            @endauth
                            @guest
                                <div class="settings-guest-state">Login with GitHub to manage your API key.</div>
                            @endguest
                        </article>

                        {{-- DeepSeek --}}
                        <article class="settings-provider-item">
                            <div class="settings-provider-top">
                                <div class="settings-provider-brand">
                                    <span class="settings-provider-logo" aria-hidden="true">
                                        <img src="{{ asset('images/deepseeklogo.png') }}" alt="DeepSeek" style="width:24px;height:24px;object-fit:contain;">
                                    </span>
                                    <div>
                                        <div class="settings-provider-name">DeepSeek</div>
                                        <div class="settings-provider-sub" id="settings-deepseek-status-text">Developer key active</div>
                                    </div>
                                </div>
                                <span class="settings-provider-pill is-active" id="settings-deepseek-pill">Active</span>
                            </div>

                            @auth
                                <div class="settings-api-box" id="settings-deepseek-key-box"
                                    data-status-url="{{ route('profile.deepseek-key.status') }}"
                                    data-save-url="{{ route('profile.deepseek-key.save') }}"
                                    data-remove-url="{{ route('profile.deepseek-key.remove') }}"
                                    data-mode-url="{{ route('profile.ai-key.mode') }}">
                                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                        <span style="font-size:13px;font-weight:600;color:var(--text-main);">Key source:</span>
                                        <select class="settings-mode-select" id="settings-deepseek-key-mode" aria-label="DeepSeek key source" style="width:auto;margin-bottom:0;">
                                            <option value="developer">Developer key</option>
                                            <option value="personal">Personal key</option>
                                        </select>
                                        <span id="settings-deepseek-status-badge" style="font-size:11px;padding:3px 8px;border-radius:999px;font-weight:600;background:rgba(45,164,78,0.1);color:#1a7f37;border:1px solid rgba(45,164,78,0.3);">Developer</span>
                                    </div>
                                    <div id="settings-deepseek-key-row" style="display:none;margin-top:10px;">
                                        <input class="settings-api-input" id="settings-deepseek-api-input" type="password" placeholder="sk-...">
                                        <div class="settings-api-actions" style="margin-top:8px;">
                                            <button class="settings-api-save-btn" id="settings-deepseek-api-save-btn" type="button" data-loading-btn data-loading-text="Saving">Save key</button>
                                            <button class="settings-api-remove-btn" id="settings-deepseek-api-remove-btn" type="button" data-loading-btn data-loading-text="Removing">Remove key</button>
                                        </div>
                                    </div>
                                    <p class="settings-api-state" id="settings-deepseek-key-state"></p>
                                </div>
                            @endauth
                            @guest
                                <div class="settings-guest-state">Login with GitHub to manage your API key.</div>
                            @endguest
                        </article>

                        {{-- Coming Soon --}}
                        <article class="settings-provider-item" style="opacity:0.55;">
                            <div class="settings-provider-top">
                                <div class="settings-provider-brand">
                                    <span class="settings-provider-logo" aria-hidden="true">
                                        <svg viewBox="0 0 32 32" width="24" height="24" fill="none"><rect x="4" y="8" width="24" height="16" rx="4" stroke="#141414" stroke-width="2.5"/><path d="M12 12v8m8-8v8" stroke="#141414" stroke-width="2.5" stroke-linecap="round"/></svg>
                                    </span>
                                    <div>
                                        <div class="settings-provider-name">Anthropic</div>
                                        <div class="settings-provider-sub">Coming soon</div>
                                    </div>
                                </div>
                                <span class="settings-provider-pill">Soon</span>
                            </div>
                        </article>

                        <article class="settings-provider-item" style="opacity:0.55;">
                            <div class="settings-provider-top">
                                <div class="settings-provider-brand">
                                    <span class="settings-provider-logo" aria-hidden="true">
                                        <svg viewBox="0 0 32 32" width="24" height="24" fill="none"><path d="M16 3c7.18 0 13 5.82 13 13s-5.82 13-13 13S3 23.18 3 16 8.82 3 16 3z" fill="url(#gsg)"/><path d="M10 14l6-4 6 4-6 4-6-4zM10 18l6-4 6 4-6 4-6-4z" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><defs><linearGradient id="gsg" x1="3" y1="3" x2="29" y2="29"><stop stop-color="#4285F4"/><stop offset="1" stop-color="#9B72CB"/></linearGradient></defs></svg>
                                    </span>
                                    <div>
                                        <div class="settings-provider-name">Google AI</div>
                                        <div class="settings-provider-sub">Coming soon</div>
                                    </div>
                                </div>
                                <span class="settings-provider-pill">Soon</span>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="ai-settings">
                    <header class="settings-pane-head">
                        <h3>AI Settings</h3>
                        <p>Customize assistant personality and response style.</p>
                    </header>

                    <div class="settings-gh-section" id="settings-ai-preferences">
                        <div class="settings-ai-grid">
                            <label class="settings-field">
                                <span>Personality preset</span>
                                <select id="ai-pref-personality">
                                    <option value="balanced">Balanced reviewer</option>
                                    <option value="strict">Strict and concise</option>
                                    <option value="mentor">Friendly mentor</option>
                                    <option value="architect">Architecture-first</option>
                                </select>
                            </label>

                            <label class="settings-field">
                                <span>Response length</span>
                                <select id="ai-pref-verbosity">
                                    <option value="short">Short</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="detailed">Detailed</option>
                                </select>
                            </label>
                        </div>

                        <label class="settings-field">
                            <span>Code-review tone</span>
                            <select id="ai-pref-tone">
                                <option value="supportive" selected>Supportive</option>
                                <option value="neutral">Neutral</option>
                                <option value="direct">Direct</option>
                            </select>
                        </label>

                        <label class="settings-field">
                            <span>Custom personality instructions</span>
                            <textarea id="ai-pref-custom-prompt" rows="4" placeholder="Example: Focus on security risks first, then performance and readability."></textarea>
                        </label>

                        <div class="settings-ai-actions">
                            <button class="settings-theme-btn" type="button" id="ai-pref-save-btn">Save AI preferences</button>
                            <span class="settings-api-state" id="ai-pref-state"></span>
                        </div>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="vcs">
                    <header class="settings-pane-head">
                        <h3>Version Control Systems</h3>
                        <p>Connected accounts, provider state, and access control.</p>
                    </header>

                    @if (session('vcs_connection_message'))
                        <p class="settings-api-state">{{ session('vcs_connection_message') }}</p>
                    @endif

                    <ul class="settings-vcs-list">
                        @foreach ($settingsVcsProviders as $provider)
                            @php
                                $isGitHubProvider = $provider['key'] === 'github';
                                $isConnected = !empty($provider['connected']);
                                $isComingSoon = in_array($provider['key'] ?? '', ['bitbucket', 'azure']);
                                $stateLabel = $isComingSoon ? 'Coming Soon' : ($provider['state'] ?? ($isConnected ? 'Connected' : 'Not connected'));
                                $profile = $provider['profile'] ?? [];
                                $connectionMeta = $provider['connection_meta'] ?? [];
                            @endphp
                            <li class="settings-vcs-item {{ $isComingSoon ? 'is-disabled' : '' }}">
                                <div class="settings-vcs-main">
                                    <span class="settings-vcs-logo" aria-hidden="true">
                                        @if($provider['name'] === 'GitHub')
                                            <svg viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.161 22 16.416 22 12c0-5.523-4.477-10-10-10z" fill="currentColor" /></svg>
                                        @elseif($provider['name'] === 'GitLab')
                                            <img src="{{ asset('images/gitlab-logo-500-rgb.svg') }}" alt="GitLab" style="width:48px;height:48px;object-fit:contain;">
                                        @elseif($provider['name'] === 'Bitbucket')
                                            <svg viewBox="0 0 24 24"><path d="M1.082 3.6A1.666 1.666 0 0 1 2.748 2h18.52a1.666 1.666 0 0 1 1.644 1.889l-2.613 15.013A1.666 1.666 0 0 1 18.656 20H5.319a1.666 1.666 0 0 1-1.644-1.39l-2.593-15.01zm13.195 10.23L15.65 8H8.38l1.373 5.83h4.524z" fill="#0052CC"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24"><rect x="2" y="2" width="9" height="9" fill="#00A4EF"/><rect x="2" y="12" width="9" height="9" fill="#00A4EF"/><rect x="12" y="2" width="9" height="9" fill="#00A4EF"/><rect x="12" y="12" width="9" height="9" fill="#00A4EF"/></svg>
                                        @endif
                                    </span>

                                    <div class="settings-vcs-meta">
                                        <div class="settings-vcs-item-name">{{ $provider['name'] }}</div>

                                        @if($isComingSoon)
                                            <div class="settings-vcs-sub">Coming soon — stay tuned.</div>
                                        @elseif(!empty($profile['username']) || !empty($profile['name']))
                                            <div class="settings-vcs-account">
                                                @if(!empty($profile['avatar_url']))
                                                    <img class="settings-vcs-avatar" src="{{ $profile['avatar_url'] }}" alt="{{ $provider['name'] }} avatar" loading="lazy">
                                                @endif
                                                <span>{{ $profile['username'] ?? $profile['name'] ?? 'Connected account' }}</span>
                                            </div>
                                        @else
                                            <div class="settings-vcs-sub">No account linked yet.</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="settings-vcs-right">
                                    <span class="settings-vcs-item-state {{ $isConnected ? 'is-connected' : '' }} {{ $isComingSoon ? 'is-coming-soon' : '' }}">
                                        <span class="settings-vcs-dot" aria-hidden="true"></span>
                                        {{ $stateLabel }}
                                    </span>

                                    @if($isComingSoon)
                                        {{-- No action — coming soon --}}
                                    @elseif($isGitHubProvider && $isConnected)
                                        <form action="{{ route('logout') }}" method="POST" class="settings-vcs-logout-form" data-loading-form>
                                            @csrf
                                            <button class="settings-vcs-logout" type="submit" data-loading-text="Logging out">Log out</button>
                                        </form>
                                    @elseif($isGitHubProvider)
                                        <a class="settings-vcs-logout" href="{{ route('github.redirect') }}">Connect</a>
                                    @elseif($provider['key'] === 'gitlab' && !$isConnected)
                                        <a class="settings-vcs-logout" href="{{ route('gitlab.redirect') }}">Connect</a>
                                    @elseif($isConnected)
                                        <form action="{{ route('vcs.connections.destroy', ['provider' => $provider['key']]) }}" method="POST" class="settings-vcs-logout-form">
                                            @csrf
                                            @method('DELETE')
                                            <button class="settings-vcs-logout" type="submit">Disconnect</button>
                                        </form>
                                    @endif
                                </div>

                                @unless($isGitHubProvider || $provider['key'] === 'gitlab' || $isComingSoon)
                                    <div class="settings-api-box" style="margin-top:16px; width:100%;">
                                        <form action="{{ route('vcs.connections.store', ['provider' => $provider['key']]) }}" method="POST" style="display:grid; gap:12px;">
                                            @csrf

                                            @if($provider['key'] === 'bitbucket')
                                                <input class="settings-api-input" name="workspace" type="text" value="{{ old('workspace', $connectionMeta['workspace'] ?? '') }}" placeholder="Workspace slug">
                                                <input class="settings-api-input" name="username" type="text" value="{{ old('username', $connectionMeta['username'] ?? '') }}" placeholder="Bitbucket username">
                                            @endif

                                            @if($provider['key'] === 'azure')
                                                <input class="settings-api-input" name="organization" type="text" value="{{ old('organization', $connectionMeta['organization'] ?? '') }}" placeholder="Azure organization">
                                                <input class="settings-api-input" name="project" type="text" value="{{ old('project', $connectionMeta['project'] ?? '') }}" placeholder="Azure project">
                                                <input class="settings-api-input" name="username" type="text" value="{{ old('username', $connectionMeta['username'] ?? '') }}" placeholder="Email or username for recent PR filtering (optional)">
                                            @endif

                                            <input class="settings-api-input" name="token" type="password" placeholder="{{ $isConnected ? 'Enter a new token to update this connection' : 'Paste an access token' }}">
                                            <div class="settings-api-actions">
                                                <button class="settings-api-save-btn" type="submit">{{ $isConnected ? 'Update connection' : 'Save connection' }}</button>
                                            </div>
                                        </form>
                                    </div>
                                @endunless
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="settings-pane" data-settings-pane="help">
                    <header class="settings-pane-head">
                        <h3>Help &amp; Documentation</h3>
                        <p>Welcome to the PR-AI help center.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>Getting Started</h4>
                        <p>PR-AI accelerates code reviews, security checks, and documentation with AI. Sign in with <strong>GitHub</strong> or <strong>GitLab</strong> on the login page, then use <strong>Imports</strong> to browse repos or the <strong>Auditor</strong> to review diffs. Full details are in <code>APP.md</code> in the project repository.</p>

                        {{-- First Walkthrough — Auditor Workspace --}}
                        <div class="help-walkthrough-section">
                            <div class="help-walkthrough-header">
                                <span class="help-walkthrough-icon">🖥️</span>
                                <div>
                                    <div class="help-walkthrough-title">Auditor Workspace Walkthrough</div>
                                    <div class="help-walkthrough-sub">Learn your way around the main review environment</div>
                                </div>
                            </div>
                            <ol class="help-walkthrough-steps">
                                <li>
                                    <strong>Chat Box</strong>
                                    <span>The central text area at the bottom — type prompts, ask coding questions, or request code reviews. Press <kbd>Enter</kbd> or click the send button to submit.</span>
                                </li>
                                <li>
                                    <strong>Voice Input</strong>
                                    <span>Click the <strong>🎤 microphone button</strong> in the chat toolbar to speak directly to PR-AI. Your voice is transcribed and sent as a prompt.</span>
                                </li>
                                <li>
                                    <strong>Import Button (+)</strong>
                                    <span>The <strong>➕ plus button</strong> in the chat toolbar lets you upload diff files, paste code in the Monaco editor, or jump to the Imports page to browse repos.</span>
                                </li>
                                <li>
                                    <strong>Send Button</strong>
                                    <span>The <strong>blue send arrow</strong> (or press <kbd>Enter</kbd>) submits your prompt. The AI streams its response in real time.</span>
                                </li>
                                <li>
                                    <strong>Response Area</strong>
                                    <span>The main scrollable area above the chat box displays AI audit results, security scores, OWASP coverage, Mermaid diagrams, and follow-up suggestions.</span>
                                </li>
                                <li>
                                    <strong>AI Provider &amp; Model Selectors</strong>
                                    <span>Top-right dropdowns let you switch between <strong>OpenAI</strong> and <strong>DeepSeek</strong>, and pick specific models like GPT-4o or DeepSeek-Chat.</span>
                                </li>
                                <li>
                                    <strong>Diff Viewer</strong>
                                    <span>After importing code, the bottom panel shows a syntax-highlighted side-by-side diff with file navigation and inline review comments.</span>
                                </li>
                            </ol>
                        </div>

                        {{-- First Walkthrough — Import Page --}}
                        <div class="help-walkthrough-section">
                            <div class="help-walkthrough-header">
                                <span class="help-walkthrough-icon">📥</span>
                                <div>
                                    <div class="help-walkthrough-title">Import Page Walkthrough</div>
                                    <div class="help-walkthrough-sub">How to bring repositories and code into PR-AI</div>
                                </div>
                            </div>
                            <ol class="help-walkthrough-steps">
                                <li>
                                    <strong>Recent Activity</strong>
                                    <span>The top section shows your <strong>recent pull requests, commits, and merge conflicts</strong> across connected repos. Click any item to import and audit it instantly.</span>
                                </li>
                                <li>
                                    <strong>Repository Browser</strong>
                                    <span>Click a repository to expand it and see <strong>branches, pull requests, and commits</strong>. Use the provider dropdown to switch between GitHub and GitLab.</span>
                                </li>
                                <li>
                                    <strong>Import a Pull Request</strong>
                                    <span>Click any pull request to <strong>fetch its diff, metadata, and comments</strong>. PR-AI opens the Auditor and auto-runs a full VAPT + OWASP security audit.</span>
                                </li>
                                <li>
                                    <strong>Audit a Branch</strong>
                                    <span>Click a branch name to <strong>compute the diff against the default branch</strong> (main) and audit the entire branch. Great for feature branch reviews before opening a PR.</span>
                                </li>
                                <li>
                                    <strong>Manual Import Options</strong>
                                    <span>From the Auditor, use the <strong>➕ Import button</strong> to upload <code>.diff</code> / <code>.patch</code> files or paste code directly into the built-in Monaco editor.</span>
                                </li>
                                <li>
                                    <strong>API Keys &amp; Providers</strong>
                                    <span>Go to <strong>Settings → API Keys</strong> to add your personal OpenAI or DeepSeek key. Switch between developer (shared) and personal key modes anytime.</span>
                                </li>
                            </ol>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 24px;">
                            <button class="settings-theme-btn" style="text-align:left;" type="button" onclick="document.querySelector('[data-settings-tab=\'help-c2\']').click()">
                                <strong>The Auditor Workspace</strong><br>
                                <small>Understanding the UI layout.</small>
                            </button>
                            <button class="settings-theme-btn" style="text-align:left;" type="button" onclick="document.querySelector('[data-settings-tab=\'help-c3\']').click()">
                                <strong>Importing Code</strong><br>
                                <small>GitHub, GitLab OAuth, and diffs.</small>
                            </button>
                            <button class="settings-theme-btn" style="text-align:left;" type="button" onclick="document.querySelector('[data-settings-tab=\'help-vapt\']').click()">
                                <strong>VAPT &amp; OWASP Audit</strong><br>
                                <small>How PR-AI analyzes security risks.</small>
                            </button>
                            <button class="settings-theme-btn" style="text-align:left;" type="button" onclick="document.querySelector('[data-settings-tab=\'help-c5\']').click()">
                                <strong>DocGen Mode</strong><br>
                                <small>Auto-generating documentation.</small>
                            </button>
                        </div>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-c2">
                    <header class="settings-pane-head">
                        <h3>The Auditor Workspace</h3>
                        <p>Understanding the layout and UI elements.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>Understanding the Layout</h4>
                        <p>The Auditor page is divided into three main sections:</p>
                        <table>
                            <thead><tr><th>Section</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><strong>Header</strong></td><td>Import Button, Model Selector, and Settings gear — all top-right controls</td></tr>
                                <tr><td><strong>AI Chat Panel (Center)</strong></td><td>Shows AI responses and chat history. Empty state welcomes you to import code.</td></tr>
                                <tr><td><strong>Git Diff Viewer (Bottom)</strong></td><td>Displays syntax-highlighted file differences. Only visible after loading code or diffs.</td></tr>
                                <tr><td><strong>Chat Input Area</strong></td><td>Text input for questions, Send button, Voice button, Plus (+) button, and DocGen mode indicator when active.</td></tr>
                            </tbody>
                        </table>

                        <h4>Top Header</h4>
                        <p><strong>Import Button</strong> (Top Right) — Opens a dropdown menu with three import options: "Import from repo provider", "Upload diff file", and "Paste diff/code".</p>
                        <p><strong>Model Selector</strong> (Top Right, Next to Import) — Switch between available AI models (e.g., GPT-4o-mini, GPT-4). Your selection persists across audits.</p>

                        <h4>Chat Input Area</h4>
                        <ul>
                            <li><strong>Text Input Field</strong> — Type questions or prompts to the AI. The AI maintains context with your active audit. Press Enter or click Send to submit.</li>
                            <li><strong>Send Button (→)</strong> — Submits your message to the AI.</li>
                            <li><strong>Microphone Button (🎤)</strong> — Click to start voice recording. Speak your prompt naturally. Timer shows recording duration. Press again or wait for silence detection to send.</li>
                            <li><strong>Plus Button (+)</strong> — Opens import/upload menu for documents. Upload files without leaving the chat. Add additional context during review.</li>
                            <li><strong>DocGen Mode Chip</strong> (Golden badge) — Appears when DocGen mode is active. Click to toggle DocGen on/off.</li>
                        </ul>

                        <h4>Sidebar Navigation</h4>
                        <ul>
                            <li><strong>Auditor</strong> — Main code review workspace</li>
                            <li><strong>Import</strong> — Browse recent PRs and commits; select from repositories</li>
                            <li><strong>Apps</strong> — Enable/disable features like DocGen</li>
                        </ul>
                        <p><strong>Bottom Controls:</strong> Theme Toggle (light/dark mode), Settings (configure API keys, VCS), Profile (account and plan tier).</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-c3">
                    <header class="settings-pane-head">
                        <h3>Importing Code</h3>
                        <p>All the ways to bring code into PR-AI.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>Method 1: Import from Repository Provider</h4>
                        <ol>
                            <li>Click the <strong>Import</strong> button (top right of Auditor)</li>
                            <li>Select <strong>"Import from repo provider"</strong></li>
                            <li>Choose a provider (GitHub, GitLab, Bitbucket, Azure)</li>
                            <li>You'll be redirected to the <strong>Imports page</strong></li>
                        </ol>
                        <p><strong>On the Imports Page:</strong> The left column lists <strong>Recent Pull Requests</strong>, <strong>Recent Commits</strong>, and <strong>Merge Conflicts</strong>. Click <strong>Import</strong> on a conflicted PR/MR to open the Auditor with a conflict viewer, AI explanation, Mermaid diagram, and a copyable agent fix prompt.</p>
                        <p>Click on a repository to expand it and see Branches, Pull Requests, and Commits.</p>

                        <h4>GitHub and GitLab sign-in</h4>
                        <p>Use <strong>Sign in with GitHub</strong> or <strong>Sign in with GitLab</strong> on the login page. After sign-in, Imports and Auditor use your OAuth token automatically—no manual token paste required for gitlab.com or github.com.</p>
                        <p>For self-hosted GitLab, sign in with OAuth when supported, or paste a Personal Access Token under Settings → GitLab (optional base URL).</p>

                        <p><strong>Branch audits</strong> compare the selected branch with a base branch. <strong>Pull request</strong> and <strong>commit</strong> audits load diffs from GitHub or GitLab APIs and open in the Auditor.</p>

                        <h4>Method 2: Upload a Diff File</h4>
                        <ol>
                            <li>Click the <strong>Import</strong> button or the <strong>Plus (+)</strong> button in chat</li>
                            <li>Select <strong>"Upload diff file"</strong></li>
                            <li>Choose a <code>.diff</code> or <code>.patch</code> file from your computer</li>
                            <li>The file uploads and the diff appears at the bottom</li>
                        </ol>
                        <p><strong>Supported Formats:</strong> <code>.diff</code> files (unified diff format), <code>.patch</code> files (Git patch format), raw diff output from <code>git diff</code> command.</p>

                        <h4>Method 3: Paste Diff or Code Manually</h4>
                        <ol>
                            <li>Click the <strong>Plus (+)</strong> button in chat</li>
                            <li>Select <strong>"Paste diff/code"</strong></li>
                            <li>A Monaco Editor opens (VS Code–like editor)</li>
                            <li>Paste your diff or code into the editor</li>
                            <li>Click <strong>Audit</strong> or <strong>Analyze</strong></li>
                        </ol>
                        <p>⚠️ <strong>Critical:</strong> For Git diff to render at the bottom, the pasted content must be in <strong>valid diff format</strong>. If you paste raw code without diff markers, the diff viewer won't show it—but you can still chat with the AI about the code.</p>
                        <p><strong>Valid Diff Format Example:</strong></p>
                        <pre>--- a/src/index.js
+++ b/src/index.js
@@ -10,3 +10,5 @@
 function greet() {
   console.log("Hello");
+  console.log("Updated");
+  console.log("New line");
 }</pre>

                        <h4>Why Git Diff Matters</h4>
                        <p>The Git Diff Viewer at the bottom is crucial for effective code review because it shows added lines in green, removed lines in red, context lines in gray, and includes syntax highlighting and line numbers—making it easy to spot changes at a glance.</p>
                        <p><strong>If your diff doesn't appear:</strong> Verify it's in valid unified diff format. Check that it contains <code>---</code> and <code>+++</code> lines with file paths. Try re-uploading or re-pasting the diff.</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-c4">
                    <header class="settings-pane-head">
                        <h3>Auditing Modes</h3>
                        <p>How the AI analyzes your code.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>Overview</h4>
                        <table>
                            <thead><tr><th>Audit Type</th><th>What It Does</th><th>Best For</th></tr></thead>
                            <tbody>
                                <tr><td><strong>Pull Request Audit</strong></td><td>Analyzes a complete PR with all changes and comments</td><td>Full code review before merging</td></tr>
                                <tr><td><strong>Commit Audit</strong></td><td>Focuses on changes in a single commit</td><td>Reviewing specific work, understanding commit history</td></tr>
                                <tr><td><strong>Branch Audit</strong></td><td>Compares an entire branch against main</td><td>High-level branch review (currently main only)</td></tr>
                                <tr><td><strong>Manual/Paste Audit</strong></td><td>Audits code or diffs you paste manually</td><td>Quick reviews, snippets, external code samples</td></tr>
                            </tbody>
                        </table>

                        <h4>How Audits Work</h4>
                        <p>The AI automatically:</p>
                        <ul>
                            <li>✅ Reads all changed lines in the code</li>
                            <li>✅ Analyzes the diff context (lines before/after changes)</li>
                            <li>✅ Reviews pull request metadata (title, description)</li>
                            <li>✅ Extracts and incorporates any PR comments or discussions</li>
                            <li>✅ Runs automated checks for: Security vulnerabilities, Performance issues, Code style violations, Logic errors, Dependency risks, Best practice violations</li>
                        </ul>
                        <p>The AI returns: a structured <strong>summary</strong> of findings, <strong>risk signals</strong> (high/medium/low severity), specific line-by-line insights, architecture impact analysis, recommendations for improvement, and <strong>Mermaid diagrams</strong> (if relevant).</p>

                        <h4>Understanding Audit Limitations</h4>
                        <p>⚠️ <strong>Important:</strong> PR-AI is in <strong>early stage development</strong>. You should expect:</p>
                        <ul>
                            <li>Occasional inaccurate suggestions</li>
                            <li>Sometimes missing obvious issues</li>
                            <li>AI models making reasoning mistakes</li>
                            <li>False positives or negatives</li>
                            <li>Improved accuracy as the tool evolves</li>
                        </ul>
                        <p><strong>Always use PR-AI as an assistant</strong>, not a replacement for human review. Critical code should still be reviewed by team members.</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-c5">
                    <header class="settings-pane-head">
                        <h3>DocGen Mode</h3>
                        <p>Automatic documentation generation.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>What is DocGen?</h4>
                        <p>DocGen (Documentation Generation) mode enables the AI to automatically generate comprehensive documentation from your code, including:</p>
                        <ul>
                            <li>✅ README.md files</li>
                            <li>✅ Setup instructions</li>
                            <li>✅ API documentation</li>
                            <li>✅ Architecture guides</li>
                            <li>✅ Technical design documents</li>
                        </ul>

                        <h4>Enabling DocGen Mode</h4>
                        <p><strong>Method 1: Via Apps Menu</strong></p>
                        <ol>
                            <li>Click <strong>Apps</strong> in the sidebar</li>
                            <li>Find <strong>DocGen</strong> in the list</li>
                            <li>Click <strong>Activate</strong></li>
                            <li>A golden badge appears in the chat area showing DocGen is active</li>
                        </ol>
                        <p><strong>Method 2: Via Settings Modal</strong></p>
                        <ol>
                            <li>Click <strong>Settings</strong> (gear icon) in sidebar</li>
                            <li>Scroll to <strong>Applications</strong> section</li>
                            <li>Toggle <strong>DocGen</strong> on</li>
                        </ol>
                        <p><strong>Method 3: Direct Toggle</strong> — The golden <strong>DocGen</strong> chip appears in the chat tools when available. Click it to toggle on/off.</p>

                        <h4>Using DocGen</h4>
                        <p>Once enabled, the AI detects document-generation requests in your prompts. You can ask:</p>
                        <ul>
                            <li>"Generate a README for this project"</li>
                            <li>"Create API documentation"</li>
                            <li>"Write a setup guide"</li>
                            <li>"Document the architecture"</li>
                        </ul>
                        <p>The AI will analyze your code, extract key components and functions, generate well-structured markdown, include code examples, and provide clear instructions.</p>

                        <h4>DocGen Output Format</h4>
                        <p>Documentation is generated in <strong>Markdown format</strong> (.md), ready to copy and paste into your GitHub repo, share with the team, convert to PDF, or use as-is and customize further.</p>

                        <h4>Important Notes</h4>
                        <p>⚠️ If the AI detects a document-generation intent but DocGen isn't active, it will prompt you to turn on DocGen mode in Apps and re-send your request.</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-c6">
                    <header class="settings-pane-head">
                        <h3>Voice Interactions</h3>
                        <p>Talk to the AI hands-free.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>Voice Review Workflow</h4>
                        <p>PR-AI supports voice-to-AI conversations, allowing you to:</p>
                        <ul>
                            <li>✅ Speak prompts naturally instead of typing</li>
                            <li>✅ Keep your hands free during code review</li>
                            <li>✅ Speed up review discussions</li>
                            <li>✅ Use natural language effortlessly</li>
                        </ul>

                        <h4>Using Voice — Step by Step</h4>
                        <ol>
                            <li>Click the <strong>Microphone button (🎤)</strong> in the chat tools. The button changes color (usually blue/animated). A timer appears showing recording duration.</li>
                            <li>Speak your question or prompt naturally — e.g., "What are the security risks in this PR?" or "Check for performance issues".</li>
                            <li>Stop speaking (button turns back normal) or wait for silence detection. The UI may auto-detect when you've finished, or press the microphone button again to stop recording.</li>
                            <li>The audio is transcribed and sent to the AI.</li>
                            <li>The AI responds with its analysis.</li>
                        </ol>

                        <h4>Voice Button Locations</h4>
                        <ul>
                            <li><strong>In Chat Area:</strong> Located in the chat tools row near the Plus (+) button. Always visible when you're in the Auditor.</li>
                            <li><strong>Floating Action Button (FAB):</strong> A separate voice button appears in the bottom-right corner of the screen. Useful for quick voice prompts without scrolling to the chat area. Works the same as the main microphone button.</li>
                        </ul>

                        <h4>Supported Languages</h4>
                        <p>Voice transcription supports multiple languages including English (US, UK, AU), Spanish, French, German, and many more. You can configure your default language in Settings → Voice section.</p>

                        <h4>Troubleshooting Voice</h4>
                        <p><strong>If microphone doesn't work:</strong> Check browser permissions (allow microphone access). Ensure your browser supports Web Audio API. Try Chrome, Firefox, or Safari. Check microphone hardware is working.</p>
                        <p><strong>If transcription is inaccurate:</strong> Speak more clearly and slowly. Reduce background noise. Use a higher-quality microphone. Try shorter, more specific prompts.</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-c7">
                    <header class="settings-pane-head">
                        <h3>Git Diffs</h3>
                        <p>Understanding the diff viewer at the bottom of the auditor.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>Understanding the Diff Viewer</h4>
                        <p>The Git Diff Viewer appears at the bottom of the Auditor workspace and shows:</p>
                        <ul>
                            <li>✅ Side-by-side or unified view of code changes</li>
                            <li>✅ Added lines (typically green)</li>
                            <li>✅ Removed lines (typically red)</li>
                            <li>✅ Context lines (unchanged, gray background)</li>
                            <li>✅ Syntax highlighting for better readability</li>
                            <li>✅ File headers showing file names and paths</li>
                            <li>✅ Line numbers for reference</li>
                        </ul>

                        <h4>When Does the Diff Viewer Appear?</h4>
                        <p>The diff viewer only appears after you've:</p>
                        <ol>
                            <li>Imported a repository (GitHub PR, commit, or branch)</li>
                            <li>Uploaded a diff file (<code>.diff</code> or <code>.patch</code>)</li>
                            <li>Pasted valid diff-formatted code (with <code>---</code> and <code>+++</code> markers)</li>
                        </ol>
                        <p>If you paste raw code without diff formatting, the AI can still analyze it, but the diff viewer won't render.</p>

                        <h4>Reading the Diff Viewer</h4>
                        <ul>
                            <li>🟢 <strong>Green lines with +</strong> = New code added</li>
                            <li>🔴 <strong>Red lines with -</strong> = Code removed</li>
                            <li>⚪ <strong>White/gray lines</strong> = Context (unchanged code)</li>
                            <li>🔵 <strong>Blue headers</strong> = File information</li>
                        </ul>
                        <p><strong>Header Example:</strong></p>
                        <pre>--- a/src/index.js      ← Old file path
+++ b/src/index.js      ← New file path
@@ -10,5 +10,7 @@    ← Hunk header (line numbers)</pre>

                        <h4>What If Diff Doesn't Appear?</h4>
                        <ol>
                            <li><strong>Verify diff format</strong> — Is your content in valid unified diff format? Valid content starts with <code>---</code> file path line. Raw code without diff markers is invalid.</li>
                            <li><strong>Check file type</strong> — Ensure uploaded file is <code>.diff</code>, <code>.patch</code>, or plain text.</li>
                            <li><strong>Re-upload/paste</strong> — Try uploading or pasting the content again.</li>
                            <li><strong>Still can't see it?</strong> — The AI can still analyze raw code; just ask questions in chat.</li>
                        </ol>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-c8">
                    <header class="settings-pane-head">
                        <h3>Advanced Features</h3>
                        <p>Mermaid diagrams, context-aware chat, and document uploads.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>Mermaid Diagrams</h4>
                        <p>PR-AI uses Mermaid.js to automatically generate visual diagrams of:</p>
                        <ul>
                            <li>✅ Architecture diagrams — Component relationships</li>
                            <li>✅ Flow charts — Process logic and workflows</li>
                            <li>✅ Sequence diagrams — Method call sequences</li>
                            <li>✅ State diagrams — State transitions</li>
                            <li>✅ Entity-relationship diagrams — Database schemas</li>
                        </ul>
                        <p>The AI automatically generates diagrams when analyzing code that involves complex workflows, multiple interacting components, database relationships, authentication flows, or business logic with decision trees.</p>
                        <p><strong>Viewing Diagrams:</strong> Diagrams appear in the chat response area and are rendered as interactive visualizations. Hover over nodes to see details. Some diagrams allow zooming/panning. Right-click to export as image (browser-dependent).</p>
                        <p><strong>Example Prompts:</strong> "Create a diagram showing how the authentication flow works", "Visualize the database schema for this module", "Show me the component architecture".</p>

                        <h4>Context-Aware Questioning</h4>
                        <p>Once you've loaded a code audit, the AI maintains context about the specific files changed, the commit/PR metadata (title, description), comments from pull request discussions, and the complete diff of all changes.</p>
                        <p><strong>This means:</strong> You can ask follow-up questions without re-explaining the code. The AI understands pronouns like "this function" or "these changes". You can ask deeper questions like "What about error handling here?" or request different perspectives like "What about performance?"</p>
                        <p><strong>Example Follow-up Chat:</strong></p>
                        <pre>You: "Audit this PR"
AI: [Provides full analysis]

You: "What about security?"
AI: [Analyzes security implications specifically]

You: "Can this cause any race conditions?"
AI: [Analyzes concurrency and threading issues]

You: "Suggest optimizations"
AI: [Proposes performance improvements]</pre>

                        <h4>Document Upload During Review</h4>
                        <p>Upload external context to help the AI understand requirements:</p>
                        <ol>
                            <li>Click the <strong>Plus (+) button</strong> in chat tools</li>
                            <li>Select <strong>Upload file</strong> or drag-and-drop</li>
                            <li>Upload relevant documents: technical specifications, architecture diagrams, design documents, API specifications, or internal coding standards</li>
                        </ol>
                        <p><strong>Supported Formats:</strong> <code>.pdf</code>, <code>.md</code>, <code>.txt</code>, <code>.json</code>, Images (<code>.jpg</code>, <code>.png</code>). The AI will reference these documents in its analysis.</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-vapt">
                    <header class="settings-pane-head">
                        <h3>VAPT &amp; OWASP Auditing</h3>
                        <p>How PR-AI secures your code using industry standards.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>What is VAPT?</h4>
                        <p>Vulnerability Assessment and Penetration Testing (VAPT) is a systematic approach to finding and mitigating security weaknesses in code. PR-AI integrates VAPT methodologies directly into its code review process to catch vulnerabilities before they reach production.</p>

                        <h4>OWASP Top 10 Alignment</h4>
                        <p>PR-AI's security audits are strictly aligned with the <strong>OWASP Top 10 (2021)</strong>, the globally recognized standard for web application security. Every code change you import is automatically scanned against these critical vulnerability categories:</p>

                        <ol>
                            <li><strong>A01: Broken Access Control</strong> - Enforcing permissions and authorization.</li>
                            <li><strong>A02: Cryptographic Failures</strong> - Protecting sensitive data and secrets.</li>
                            <li><strong>A03: Injection</strong> - Preventing SQL, NoSQL, OS, and LDAP injection.</li>
                            <li><strong>A04: Insecure Design</strong> - Identifying architectural security flaws.</li>
                            <li><strong>A05: Security Misconfiguration</strong> - Checking default settings and headers.</li>
                            <li><strong>A06: Vulnerable and Outdated Components</strong> - Spotting risky dependencies.</li>
                            <li><strong>A07: Identification and Authentication Failures</strong> - Securing logins and sessions.</li>
                            <li><strong>A08: Software and Data Integrity Failures</strong> - Validating updates and CI/CD pipelines.</li>
                            <li><strong>A09: Security Logging and Monitoring Failures</strong> - Ensuring sufficient audit trails.</li>
                            <li><strong>A10: Server-Side Request Forgery (SSRF)</strong> - Preventing unauthorized internal requests.</li>
                        </ol>

                        <h4>How It Works</h4>
                        <p>During an audit, PR-AI evaluates your code diffs and specifically flags lines that introduce security risks based on the OWASP framework. It categorizes the severity (High, Medium, Low), explains the exploitation vector, and provides exact remediation code.</p>

                        <h4>Why It Matters</h4>
                        <p>By shifting security left and embedding VAPT analysis into the pull request review stage, PR-AI helps development teams maintain velocity without sacrificing code safety. You don't need to be a security expert to catch critical vulnerabilities.</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-c9">
                    <header class="settings-pane-head">
                        <h3>API Keys</h3>
                        <p>Managing your OpenAI API key and billing modes.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>Two-Mode Key System</h4>
                        <p>PR-AI supports two ways to handle OpenAI API keys:</p>

                        <h4>Mode 1: Developer Key (Shared / Free)</h4>
                        <ul>
                            <li>Uses a shared developer API key provided by PR-AI</li>
                            <li>No cost to you (PR-AI covers API costs for free tier)</li>
                            <li>Limited usage quotas</li>
                            <li>Good for: Getting started, light usage, teams</li>
                        </ul>
                        <p><strong>How to Use:</strong> Sign up for PR-AI and you're automatically in "Developer" mode. Start auditing immediately — no API key needed.</p>

                        <h4>Mode 2: Personal Key (Your Own)</h4>
                        <ul>
                            <li>You provide your own OpenAI API key</li>
                            <li>You control billing and costs</li>
                            <li>Full quota limits (your OpenAI account limits)</li>
                            <li>Good for: Heavy usage, enterprises, cost control, privacy</li>
                        </ul>
                        <p><strong>How to Set Up:</strong></p>
                        <ol>
                            <li>Get your OpenAI API key at <strong>platform.openai.com/api-keys</strong> — create a new API key and copy it (you'll only see it once).</li>
                            <li>Add it to PR-AI: Click <strong>Settings</strong> in sidebar → go to <strong>API Keys</strong> section → paste your OpenAI key → click <strong>Save</strong>.</li>
                            <li>Switch to Personal mode: click the toggle to switch to <strong>Personal</strong> mode. All subsequent audits use your key and you're billed directly through OpenAI.</li>
                        </ol>

                        <h4>Managing Your Personal Key</h4>
                        <ul>
                            <li><strong>View your masked key:</strong> Settings shows <code>sk-••••••••••••••••1234</code> (last 4 characters visible)</li>
                            <li><strong>Remove your key:</strong> In Settings, click <strong>Remove</strong> next to your key — you'll return to Developer mode</li>
                            <li><strong>Change your key:</strong> Remove the old key, add a new key, verify it works with a test audit</li>
                        </ul>

                        <h4>Billing Considerations</h4>
                        <table>
                            <thead><tr><th>Audit Type</th><th>Estimated Cost (Personal Key)</th></tr></thead>
                            <tbody>
                                <tr><td>Small PR (&lt; 50 lines)</td><td>$0.01 – $0.05</td></tr>
                                <tr><td>Medium PR (50–500 lines)</td><td>$0.05 – $0.20</td></tr>
                                <tr><td>Large PR (500+ lines)</td><td>$0.20 – $1.00</td></tr>
                                <tr><td>DocGen output</td><td>$0.10 – $0.50</td></tr>
                            </tbody>
                        </table>
                        <p><em>Costs depend on model choice, token usage, and current OpenAI pricing.</em></p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-c10">
                    <header class="settings-pane-head">
                        <h3>Roadmap &amp; Known Issues</h3>
                        <p>What's coming and what to expect as an early user.</p>
                    </header>
                    <div class="help-doc-content">
                        <h4>What's Coming Soon 🚀</h4>
                        <ul>
                            <li><strong>Inline Code Comments</strong> — AI will add comments directly on specific lines of code, with manual approval before posting to GitHub/GitLab.</li>
                            <li><strong>Extensive Technical Reports</strong> — Auto-generate detailed PDF reports with metrics, trends, and recommendations to share with stakeholders.</li>
                            <li><strong>Test Plan Generation</strong> — AI creates comprehensive test plans including edge cases and test coverage suggestions, exportable to CI/CD systems.</li>
                            <li><strong>QA Documentation</strong> — Automated QA checklists, test case generation, and bug severity classification.</li>
                            <li><strong>Commit &amp; Push Workflows</strong> — Instruct the AI to prepare commits from natural language. "Make these changes" → AI suggests commits.</li>
                            <li><strong>Multi-Branch Comparisons</strong> — Compare any branch with any other branch (not just main). Branch-to-branch diffs and multiple branch analysis.</li>
                            <li><strong>Performance Profiling</strong> — Analyze code for performance bottlenecks, suggest optimizations, profile memory usage patterns.</li>
                        </ul>

                        <h4>Known Issues &amp; Bugs ⚠️</h4>
                        <p>PR-AI is in <strong>early stage development</strong>. Expect these known issues:</p>
                        <ul>
                            <li><strong>False Positives</strong> — AI may flag safe code as risky or suggest unnecessary changes. <em>Workaround: Cross-check AI suggestions with your domain knowledge.</em></li>
                            <li><strong>Incomplete Diffs</strong> — Large files (&gt;10MB) may not render fully in diff viewer. Some binary files and special characters may be skipped. <em>Workaround: Upload split diffs.</em></li>
                            <li><strong>Voice Transcription Errors</strong> — Accents may cause transcription mistakes. Background noise affects accuracy. <em>Workaround: Speak clearly; use typed prompts for critical reviews.</em></li>
                            <li><strong>Diagram Generation</strong> — Complex code may not generate useful diagrams. Mermaid rendering has browser compatibility issues. <em>Workaround: Request specific diagram types.</em></li>
                            <li><strong>Performance on Large PRs</strong> — PRs over 5000 lines may take longer. API timeouts on extremely complex changes. <em>Workaround: Split large PRs; use during off-peak hours.</em></li>
                            <li><strong>Session Timeouts</strong> — Long inactive periods may disconnect. Chat history may not persist on browser clear. <em>Workaround: Save important audits; clear cache periodically.</em></li>
                        </ul>

                        <h4>Performance Tips 💡</h4>
                        <ul>
                            <li>Use recent browsers (Chrome, Firefox, Safari, Edge)</li>
                            <li>Keep diffs under 2000 lines per audit</li>
                            <li>Use specific, clear prompts instead of vague questions</li>
                            <li>Enable personal API key for consistent performance</li>
                            <li>Audit during off-peak hours for faster responses</li>
                            <li>Clear browser cache if experiencing slowdowns</li>
                            <li>Upload documents to provide better context</li>
                        </ul>

                        <h4>Frequently Asked Questions</h4>
                        <ul>
                            <li><strong>Is my code secure?</strong> All code is processed securely. With a personal API key, your code is sent to OpenAI for analysis. With the developer key, code is processed with enterprise-grade privacy safeguards. Never upload highly sensitive credentials or private keys.</li>
                            <li><strong>Can I use PR-AI offline?</strong> No, PR-AI requires internet and connection to OpenAI/Gemini APIs. Offline analysis is not currently supported.</li>
                            <li><strong>How do I cancel my personal API key?</strong> Go to Settings → API Keys → Remove. You'll return to Developer mode immediately.</li>
                            <li><strong>Can I export the audit results?</strong> Yes, you can copy chat responses. Export of audit reports as PDF is coming soon.</li>
                            <li><strong>What if I hit my API quota?</strong> If using personal key, upgrade your OpenAI plan. If using developer key, wait for the monthly reset.</li>
                        </ul>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-developer">
                    <header class="settings-pane-head">
                        <h3>About Developer</h3>
                        <p>The person behind PR-AI.</p>
                    </header>
                    <div class="help-doc-content">
                        <div style="display:flex; align-items:center; gap:16px; margin-bottom: 24px;">
                            <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(130deg, #009eff 0%, #9360ff 100%); display:flex; align-items:center; justify-content:center; color:white; font-size: 24px; font-weight:bold; flex-shrink:0;">
                                ND
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 18px;">Nanbol Dassak</h4>
                                <div style="color: var(--brand); font-weight: 500;">Software Engineer &amp; AI Architect</div>
                                <a href="https://bolitupac.github.io/" target="_blank" style="color: var(--brand); text-decoration: underline; font-size: 13px;">bolitupac.github.io</a>
                            </div>
                        </div>

                        <p><strong>Nanbol Dassak</strong> (also known as Dassak Nanbol Felix) is a software engineer, AI systems architect, and game developer based in Nigeria. As a software engineering student at Babcock University, Nanbol focuses heavily on AI integration, workflow automation, and eliminating manual bottlenecks through intelligent systems.</p>

                        <h4>Expertise</h4>
                        <ul>
                            <li><strong>AI &amp; Automation:</strong> Specializes in building autonomous AI agents, integrating Large Language Models (LLMs), and designing robust n8n orchestrations.</li>
                            <li><strong>Software Engineering:</strong> Extensive backend experience utilizing frameworks like Laravel, Django, and Flask.</li>
                            <li><strong>Game Development:</strong> Develops interactive experiences and games using Godot, including the 2D platformer <em>"One Chance"</em>.</li>
                        </ul>

                        <p>Nanbol is highly active in the developer community. You can find his projects and insights on platforms like GitHub, daily.dev, itch.io, and X (Twitter).</p>
                    </div>
                </section>
            </div>
        </div>
    </section>
</div>
