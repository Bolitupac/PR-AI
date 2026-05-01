@php
    $settingsVcsProviders = $vcsProviders ?? [
        ['name' => 'GitHub', 'state' => 'Connected'],
        ['name' => 'GitLab', 'state' => 'Available'],
        ['name' => 'Bitbucket', 'state' => 'Available'],
        ['name' => 'Azure DevOps', 'state' => 'Available'],
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
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-getting-started">Getting Started</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-vcs">Connecting Accounts</button>
                    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="help-features">Features</button>
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

                <section class="settings-pane" data-settings-pane="api-keys">
                    <header class="settings-pane-head">
                        <h3>API Keys</h3>
                        <p>Manage providers and choose the key source.</p>
                    </header>

                    <div class="settings-provider-list">
                        <article class="settings-provider-item settings-provider-item--active">
                            <div class="settings-provider-top">
                                <div class="settings-provider-brand">
                                    <span class="settings-provider-logo settings-provider-logo--openai" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 3.2c2 0 3.8 1 4.9 2.6 2.2.3 4 2.2 4 4.5 0 1.1-.4 2.2-1.1 3l.1.6c.1 2.7-2 5-4.7 5.2-.9 1.1-2.2 1.8-3.7 1.8-1.5 0-2.8-.6-3.7-1.7-2.7-.3-4.8-2.6-4.7-5.4l.1-.5C2.4 12.4 2 11.3 2 10.2c0-2.3 1.7-4.2 4-4.5A5.8 5.8 0 0 1 12 3.2Z" fill="none" stroke="currentColor" stroke-width="1.4" />
                                            <path d="m7 8.8 5 2.8m0 0 5-2.8m-5 2.8v5.2m-3.8-1.8 3.8-2.2 3.8 2.2" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <div>
                                        <div class="settings-provider-name">OpenAI</div>
                                        <div class="settings-provider-sub">Fully configurable</div>
                                    </div>
                                </div>
                                <span class="settings-provider-pill is-active">Active</span>
                            </div>

                            @auth
                                <div class="settings-api-box" id="settings-ai-key-box"
                                    data-status-url="{{ route('profile.ai-key.status') }}"
                                    data-save-url="{{ route('profile.ai-key.save') }}"
                                    data-remove-url="{{ route('profile.ai-key.remove') }}"
                                    data-mode-url="{{ route('profile.ai-key.mode') }}">
                                    <label class="settings-mode-label" for="settings-ai-key-mode">Key source</label>
                                    <select class="settings-mode-select" id="settings-ai-key-mode" aria-label="API key source">
                                        <option value="developer">Use developer key (recommended)</option>
                                        <option value="personal">Use my key</option>
                                    </select>

                                    <input class="settings-api-input" id="settings-api-input" type="password" placeholder="Paste your key to save in DB (sk-...)">

                                    <div class="settings-api-actions">
                                        <button class="settings-api-save-btn" id="settings-api-save-btn" type="button" data-loading-btn data-loading-text="Saving">Save key</button>
                                        <button class="settings-api-remove-btn" id="settings-api-remove-btn" type="button" data-loading-btn data-loading-text="Removing">Remove key</button>
                                    </div>

                                    <p class="settings-api-hint" id="settings-ai-key-hint">Choose whether to use developer key or your saved key.</p>
                                    <p class="settings-api-state" id="settings-ai-key-state"></p>
                                </div>
                            @endauth

                            @guest
                                <div class="settings-guest-state">Login with GitHub to manage and save your API key.</div>
                            @endguest
                        </article>

                        <article class="settings-provider-item">
                            <div class="settings-provider-top">
                                <div class="settings-provider-brand">
                                    <span class="settings-provider-logo settings-provider-logo--anthropic" aria-hidden="true">
                                        <img src="https://logo.clearbit.com/anthropic.com" alt="Anthropic logo" loading="lazy">
                                    </span>
                                    <div>
                                        <div class="settings-provider-name">Anthropic</div>
                                        <div class="settings-provider-sub">Coming soon</div>
                                    </div>
                                </div>
                                <span class="settings-provider-pill">Soon</span>
                            </div>
                        </article>

                        <article class="settings-provider-item">
                            <div class="settings-provider-top">
                                <div class="settings-provider-brand">
                                    <span class="settings-provider-logo settings-provider-logo--google" aria-hidden="true">
                                        <img src="https://logo.clearbit.com/google.com" alt="Google logo" loading="lazy">
                                    </span>
                                    <div>
                                        <div class="settings-provider-name">Google AI</div>
                                        <div class="settings-provider-sub">Coming soon</div>
                                    </div>
                                </div>
                                <span class="settings-provider-pill">Soon</span>
                            </div>
                        </article>

                        <article class="settings-provider-item">
                            <div class="settings-provider-top">
                                <div class="settings-provider-brand">
                                    <span class="settings-provider-logo settings-provider-logo--xai" aria-hidden="true">x</span>
                                    <div>
                                        <div class="settings-provider-name">xAI</div>
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
                                $stateLabel = $provider['state'] ?? ($isConnected ? 'Connected' : 'Not connected');
                                $profile = $provider['profile'] ?? [];
                                $connectionMeta = $provider['connection_meta'] ?? [];
                            @endphp
                            <li class="settings-vcs-item">
                                <div class="settings-vcs-main">
                                    <span class="settings-vcs-logo" aria-hidden="true">
                                        @if($provider['name'] === 'GitHub')
                                            <svg viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.161 22 16.416 22 12c0-5.523-4.477-10-10-10z" fill="currentColor" /></svg>
                                        @elseif($provider['name'] === 'GitLab')
                                            <svg viewBox="0 0 24 24" fill="none"><path d="M23.955 10.37L21.316 2.246a.82.82 0 0 0-1.564 0l-2.07 6.386H6.315L4.246 2.246a.82.82 0 0 0-1.564 0L.044 10.37a.822.822 0 0 0 .296.907L12 20.59l11.66-9.311a.822.822 0 0 0 .295-.91z" fill="#FC6D26"/><path d="M12 20.59L.044 10.37a.822.822 0 0 1-.296-.906L2.68 1.34a.82.82 0 0 1 1.564 0l2.07 6.386H12v12.863z" fill="#E24329"/><path d="M12 20.59V8.632H6.315L12 20.59z" fill="#FCA326"/><path d="M12 20.59l11.956-10.22a.822.822 0 0 0 .295-.91L21.32 1.34a.82.82 0 0 0-1.564 0l-2.07 6.386H12v12.863z" fill="#E24329"/><path d="M12 20.59V8.632h5.685L12 20.59z" fill="#FCA326"/></svg>
                                        @elseif($provider['name'] === 'Bitbucket')
                                            <svg viewBox="0 0 24 24"><path d="M1.082 3.6A1.666 1.666 0 0 1 2.748 2h18.52a1.666 1.666 0 0 1 1.644 1.889l-2.613 15.013A1.666 1.666 0 0 1 18.656 20H5.319a1.666 1.666 0 0 1-1.644-1.39l-2.593-15.01zm13.195 10.23L15.65 8H8.38l1.373 5.83h4.524z" fill="#0052CC"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24"><rect x="2" y="2" width="9" height="9" fill="#00A4EF"/><rect x="2" y="12" width="9" height="9" fill="#00A4EF"/><rect x="12" y="2" width="9" height="9" fill="#00A4EF"/><rect x="12" y="12" width="9" height="9" fill="#00A4EF"/></svg>
                                        @endif
                                    </span>

                                    <div class="settings-vcs-meta">
                                        <div class="settings-vcs-item-name">{{ $provider['name'] }}</div>

                                        @if(!empty($profile['username']) || !empty($profile['name']))
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
                                    <span class="settings-vcs-item-state {{ $isConnected ? 'is-connected' : '' }}">
                                        <span class="settings-vcs-dot" aria-hidden="true"></span>
                                        {{ $stateLabel }}
                                    </span>

                                    @if($isGitHubProvider && $isConnected)
                                        <form action="{{ route('logout') }}" method="POST" class="settings-vcs-logout-form" data-loading-form>
                                            @csrf
                                            <button class="settings-vcs-logout" type="submit" data-loading-text="Logging out">Log out</button>
                                        </form>
                                    @elseif($isGitHubProvider)
                                        <a class="settings-vcs-logout" href="{{ route('github.redirect') }}">Connect</a>
                                    @elseif($isConnected)
                                        <form action="{{ route('vcs.connections.destroy', ['provider' => $provider['key']]) }}" method="POST" class="settings-vcs-logout-form">
                                            @csrf
                                            @method('DELETE')
                                            <button class="settings-vcs-logout" type="submit">Disconnect</button>
                                        </form>
                                    @endif
                                </div>

                                @unless($isGitHubProvider)
                                    <div class="settings-api-box" style="margin-top:16px; width:100%;">
                                        <form action="{{ route('vcs.connections.store', ['provider' => $provider['key']]) }}" method="POST" style="display:grid; gap:12px;">
                                            @csrf
                                            @if($provider['key'] === 'gitlab')
                                                <input class="settings-api-input" name="base_url" type="url" value="{{ old('base_url', $connectionMeta['base_url'] ?? 'https://gitlab.com') }}" placeholder="GitLab base URL">
                                                <input class="settings-api-input" name="username" type="text" value="{{ old('username', $connectionMeta['username'] ?? '') }}" placeholder="GitLab username (optional)">
                                            @endif

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

                <section class="settings-pane" data-settings-pane="help-getting-started">
                    <header class="settings-pane-head">
                        <h3>Getting Started</h3>
                        <p>How to use PR-AI and its primary features.</p>
                    </header>
                    <div class="settings-gh-section" style="padding: 24px; color: var(--text-muted); line-height: 1.6; font-size: 14px;">
                        <h4 style="color: var(--text-main); margin-top: 0; font-size: 16px;">Welcome to PR-AI</h4>
                        <p>PR-AI is an intelligent code auditing platform designed to streamline your code review and documentation workflows. It uses advanced Large Language Models (LLMs) to perform automated, context-aware audits on pull requests, branches, and specific commits.</p>
                        
                        <h4 style="color: var(--text-main); margin-top: 24px; font-size: 16px;">Primary Use Cases</h4>
                        <ul style="padding-left: 20px;">
                            <li style="margin-bottom: 8px;"><strong>Automated Code Auditing:</strong> Instantly catch bugs, security flaws, and performance bottlenecks in your code diffs.</li>
                            <li style="margin-bottom: 8px;"><strong>Interactive Chat:</strong> Chat with the AI directly about a specific piece of code, asking it to explain logic or suggest refactors.</li>
                            <li style="margin-bottom: 8px;"><strong>Documentation Generation:</strong> Automatically generate comprehensive <code>README.md</code> files, setup instructions, or technical design docs based on your repository.</li>
                            <li style="margin-bottom: 8px;"><strong>Architectural Reviews:</strong> Use the canvas/graph views to visualize complex dependencies and file structures in large codebases.</li>
                        </ul>

                        <p style="margin-top: 24px;">To begin, navigate to the <strong>Imports</strong> page to connect your repositories, or manually paste a diff in the <strong>Auditor</strong>.</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-vcs">
                    <header class="settings-pane-head">
                        <h3>Connecting Accounts</h3>
                        <p>Link your version control systems to PR-AI.</p>
                    </header>
                    <div class="settings-gh-section" style="padding: 24px; color: var(--text-muted); line-height: 1.6; font-size: 14px;">
                        <p>PR-AI supports multiple Version Control Systems (VCS) so you can directly import pull requests, branches, and commits.</p>

                        <h4 style="color: var(--text-main); margin-top: 20px; font-size: 16px;">1. GitHub</h4>
                        <p>You can connect your GitHub account via OAuth. This provides read-only access to your public and private repositories. Head to the <strong>VCS</strong> tab in these settings and click "Connect" under GitHub. You will be redirected to authorize the application.</p>

                        <h4 style="color: var(--text-main); margin-top: 20px; font-size: 16px;">2. GitLab</h4>
                        <p>To connect GitLab, you need a Personal Access Token with <code>read_api</code> scope. Under the VCS tab, input your GitLab base URL (defaults to <code>https://gitlab.com</code>), your username, and your generated token.</p>

                        <h4 style="color: var(--text-main); margin-top: 20px; font-size: 16px;">3. Bitbucket</h4>
                        <p>For Bitbucket, generate an App Password with repository read permissions. Supply your Bitbucket workspace slug, username, and the App Password to authenticate.</p>

                        <h4 style="color: var(--text-main); margin-top: 20px; font-size: 16px;">4. Azure DevOps</h4>
                        <p>For Azure DevOps, create a Personal Access Token (PAT) with Code (Read) permissions. Provide your Azure Organization name, Project name, and the PAT token to connect.</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-features">
                    <header class="settings-pane-head">
                        <h3>Features Overview</h3>
                        <p>Deep dive into PR-AI's capabilities.</p>
                    </header>
                    <div class="settings-gh-section" style="padding: 24px; color: var(--text-muted); line-height: 1.6; font-size: 14px;">
                        <h4 style="color: var(--text-main); margin-top: 0; font-size: 16px;">AI Configuration</h4>
                        <p>In the <strong>API Keys</strong> tab, you can choose to use the default developer API key, or supply your own personal OpenAI key for extended limits. The AI's behavior can be tweaked in the <strong>AI Settings</strong> tab. You can select predefined personalities (like "Strict" or "Friendly Mentor"), adjust verbosity, and even provide a custom system prompt to tailor the code reviews to your team's specific coding guidelines.</p>
                        
                        <h4 style="color: var(--text-main); margin-top: 20px; font-size: 16px;">Code Diffs & Visualizer</h4>
                        <p>The main Auditor window parses raw diffs into an elegant, side-by-side or inline view. You can upload <code>.patch</code> or <code>.diff</code> files directly if you prefer not to connect an account. The platform also includes a Node Canvas, allowing you to build visual workflows or architecture diagrams derived from the code.</p>

                        <h4 style="color: var(--text-main); margin-top: 20px; font-size: 16px;">Dynamic Activity Feeds</h4>
                        <p>Once a provider is connected, the <strong>Imports</strong> page dynamically fetches your recent pull requests and commits in real-time, allowing 1-click auditing.</p>
                    </div>
                </section>

                <section class="settings-pane" data-settings-pane="help-developer">
                    <header class="settings-pane-head">
                        <h3>About the Developer</h3>
                        <p>Learn more about the creator behind PR-AI.</p>
                    </header>
                    <div class="settings-gh-section" style="padding: 24px; color: var(--text-muted); line-height: 1.6; font-size: 14px;">
                        <div style="display:flex; align-items:center; gap:16px; margin-bottom: 24px;">
                            <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(130deg, #009eff 0%, #9360ff 100%); display:flex; align-items:center; justify-content:center; color:white; font-size: 24px; font-weight:bold;">
                                ND
                            </div>
                            <div>
                                <h4 style="color: var(--text-main); margin: 0; font-size: 18px;">Nanbol Dassak</h4>
                                <div style="color: var(--brand); font-weight: 500;">Software Engineer & AI Architect</div>
                            </div>
                        </div>

                        <p><strong>Nanbol Dassak</strong> (also known as Dassak Nanbol Felix) is a software engineer, AI systems architect, and game developer based in Nigeria. As a software engineering student at Babcock University, Nanbol focuses heavily on AI integration, workflow automation, and eliminating manual bottlenecks through intelligent systems.</p>
                        
                        <h4 style="color: var(--text-main); margin-top: 20px; font-size: 16px;">Expertise</h4>
                        <ul style="padding-left: 20px;">
                            <li style="margin-bottom: 8px;"><strong>AI & Automation:</strong> Specializes in building autonomous AI agents, integrating Large Language Models (LLMs), and designing robust n8n orchestrations.</li>
                            <li style="margin-bottom: 8px;"><strong>Software Engineering:</strong> Extensive backend experience utilizing frameworks like Laravel, Django, and Flask.</li>
                            <li style="margin-bottom: 8px;"><strong>Game Development:</strong> Develops interactive experiences and games using Godot, including the 2D platformer <em>"One Chance"</em>.</li>
                        </ul>

                        <p style="margin-top: 24px;">Nanbol is highly active in the developer community. You can find his projects and insights on platforms like GitHub, daily.dev, itch.io, and X (Twitter).</p>
                    </div>
                </section>
            </div>
        </div>
    </section>
</div>
