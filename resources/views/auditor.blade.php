<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nabla&family=Science+Gothic:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo 512 transp bg white color svg.svg') }}">
    <title>Git PULL Assistant | Auditor</title>
    @vite(['resources/css/auditor-ui.css', 'resources/js/app.js'])
</head>

<body>

    <div class="app-shell">
        <section class="hero-view">
            <aside class="sidebar-bg" id="app-sidebar">
                <div class="sidebar-top">
                    <button class="sidebar-item sidebar-toggle-btn" id="sidebar-toggle-btn" type="button" aria-label="Expand sidebar" aria-expanded="false">
                        <span class="sidebar-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="3.5" y="4.5" width="17" height="15" rx="2.2" fill="none" stroke="currentColor" stroke-width="1.6" />
                                <path d="M9 4.5v15M6 9h0M6 12h0M6 15h0" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                        </span>
                        <span class="sidebar-label">Expand bar</span>
                    </button>

                    <button class="sidebar-item" id="sidebar-new-chat-btn" type="button" aria-label="New chat">
                        <span class="sidebar-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M6.5 4.5h8.8l3.2 3.2v11.8a1.8 1.8 0 0 1-1.8 1.8H6.5a1.8 1.8 0 0 1-1.8-1.8V6.3a1.8 1.8 0 0 1 1.8-1.8Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                <path d="M15.3 4.5v3.2h3.2M8 15.6l4.8-4.8 1.8 1.8-4.8 4.8H8zM12.4 11.2l1.4-1.4 1.8 1.8-1.4 1.4" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span class="sidebar-label">New chat</span>
                    </button>

                    <button class="sidebar-item" id="sidebar-search-chat-btn" type="button" aria-label="Search chat">
                        <span class="sidebar-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="5.2" fill="none" stroke="currentColor" stroke-width="1.8" />
                                <path d="m16 16 4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </span>
                        <span class="sidebar-label">Search chat</span>
                    </button>
                </div>

                <div class="sidebar-bottom">
                    <button class="sidebar-item" id="theme-toggle-btn" type="button" aria-label="Switch to dark mode">
                        <span class="sidebar-icon" aria-hidden="true">
                            <svg class="theme-toggle-icon theme-toggle-icon--moon" viewBox="0 0 24 24">
                                <path d="M14.5 3.5a8.5 8.5 0 1 0 6 14.5A9 9 0 1 1 14.5 3.5Z" fill="currentColor" />
                            </svg>
                            <svg class="theme-toggle-icon theme-toggle-icon--sun" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="4.1" fill="currentColor" />
                                <path d="M12 2.8v2.2m0 14v2.2m9.2-9.2h-2.2M5 12H2.8m15.7-6.7-1.6 1.6M7.1 16.9l-1.6 1.6m0-13.2 1.6 1.6m9.8 9.8 1.6 1.6"
                                    fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                        </span>
                        <span class="sidebar-label">Theme</span>
                    </button>

                    <button class="sidebar-item" id="sidebar-settings-btn" type="button" aria-label="Settings">
                        <span class="sidebar-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="2.8" fill="none" stroke="currentColor" stroke-width="1.8" />
                                <path d="m19 12 2-1.2-1.4-2.4-2.3.5a6.9 6.9 0 0 0-1.1-1.1l.5-2.3-2.4-1.4L12 5 10.8 3 8.4 4.4l.5 2.3a6.9 6.9 0 0 0-1.1 1.1l-2.3-.5L4.1 9.7 6 10.9 5 12l1 1.1-1.9 1.2 1.4 2.4 2.3-.5c.3.4.7.8 1.1 1.1l-.5 2.3 2.4 1.4L12 19l1.2 2 2.4-1.4-.5-2.3c.4-.3.8-.7 1.1-1.1l2.3.5 1.4-2.4L19 12Z"
                                    fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" stroke-linecap="round" />
                            </svg>
                        </span>
                        <span class="sidebar-label">Settings</span>
                    </button>

                    @auth
                        <button class="sidebar-item sidebar-profile-item" id="open-profile-btn" type="button" aria-label="Open profile">
                            <span class="sidebar-icon sidebar-avatar-wrap" aria-hidden="true">
                                <img class="profile-avatar-img" src="https://github.com/{{ auth()->user()->github_username }}.png" alt="GitHub avatar">
                            </span>
                            <span class="sidebar-profile-meta">
                                <span class="sidebar-profile-name">{{ auth()->user()->github_username ?? auth()->user()->name ?? 'user' }}</span>
                                <span class="sidebar-profile-plan">Free</span>
                            </span>
                        </button>
                    @endauth
                    @guest
                        <button class="sidebar-item sidebar-profile-item" id="open-profile-btn" type="button" aria-label="Open profile">
                            <span class="sidebar-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="6" cy="6" r="2.1" fill="none" stroke="currentColor" stroke-width="1.8" />
                                    <circle cx="18" cy="6" r="2.1" fill="none" stroke="currentColor" stroke-width="1.8" />
                                    <circle cx="12" cy="18" r="2.1" fill="none" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M7.9 7.2 10.3 16M16.1 7.2 13.7 16M8.1 6h7.8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span class="sidebar-profile-meta">
                                <span class="sidebar-profile-name">GitHub</span>
                                <span class="sidebar-profile-plan">Free</span>
                            </span>
                        </button>
                    @endguest
                </div>
            </aside>

            <main class="main-workspace">
                <section class="ai-panel">
                    <div class="ai-content">
                        @php
                            $chatModels = config('openai.chat_models', [config('openai.model', 'gpt-4o-mini')]);
                            $defaultChatModel = config('openai.model', 'gpt-4o-mini');
                        @endphp
                        <header class="panel-head ai-head">
                            <h3>
                                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo"
                                    class="brand-logo brand-logo--dark-on-light">
                                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo"
                                    class="brand-logo brand-logo--light-on-dark">
                                <span>PR ai</span>
                            </h3>
                            <div class="panel-actions">
                                <div class="import-hover" id="import-trigger-wrap">
                                    <button class="repo-upload-btn" id="import-trigger" type="button">Import</button>
                                    @include('partials.import-hover-menu')
                                </div>
                                <div class="hint-wrap hint-wrap--model">
                                    <select class="chat-model-select" id="chat-model-select" aria-label="Select model">
                                        @foreach ($chatModels as $chatModel)
                                            <option value="{{ $chatModel }}" @selected($chatModel === $defaultChatModel)>{{ $chatModel }}</option>
                                        @endforeach
                                    </select>
                                    <span class="tiny-action-hint" role="tooltip">Select AI model</span>
                                </div>
                            </div>
                        </header>

                        <div id="ai-response-area" class="chat-demo-list">
                            <div class="chat-empty-state" id="chat-empty-state">
                                <h4 class="chat-empty-title">
                                    Hi,
                                    <span class="chat-empty-name">{{ auth()->user()->github_username ?? auth()->user()->name ?? 'there' }}</span>
                                </h4>
                                <p class="chat-empty-subtitle">Import a repo or diff, or paste code to start auditing.</p>
                            </div>
                        </div>
                    </div>

                    <div class="chat-container">
                        <div class="chat-input-wrap">
                            <textarea class="chat-input" id="user-prompt" rows="1" placeholder="Ask AI..."></textarea>
                            <button class="action-btn input-send-btn" id="send-btn" type="button" aria-label="Send"
                                data-chat-url="{{ route('ai.chat') }}"
                                data-chat-stream-url="{{ route('ai.chat.stream') }}"
                                data-transcribe-url="{{ route('ai.transcribe') }}">
                                <img src="{{ asset('images/send.png') }}" alt="Send" class="action-icon">
                            </button>
                        </div>
                        <div class="chat-tools-row">
                            <div class="voice-record-chip-wrap">
                                <div class="voice-record-chip" id="voice-record-chip">
                                    <button class="action-btn ghost" id="mic-btn" type="button" aria-label="Mic">
                                        <img src="{{ asset('images/mic.png') }}" alt="Mic" class="action-icon"
                                            data-mic-icon="{{ asset('images/mic.png') }}"
                                            data-send-icon="{{ asset('images/send.png') }}">
                                    </button>
                                    <span class="voice-record-timer" id="voice-record-timer">00:00</span>
                                </div>
                                <span class="tiny-action-hint" role="tooltip">Talk to the AI</span>
                            </div>
                            <div class="import-hover import-hover--plus" id="import-plus-wrap">
                                <button class="action-btn ghost import-plus-btn" id="import-plus-trigger" type="button"
                                    aria-label="Import options" aria-haspopup="true" aria-expanded="false">
                                    <svg class="plus-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                                    </svg>
                                </button>
                                <span class="tiny-action-hint" role="tooltip">Upload or import file/repo</span>
                                @include('partials.import-hover-menu')
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </section>

        @include('partials.diff-viewer')
    </div>

    @include('partials.repo-import')
    @include('partials.diff-upload')
    @include('partials.import-monaco')
    @include('partials.import-paste')
    @include('partials.profile-modal')

    <div class="voice-fab" id="voice-fab">
        <div class="voice-record-chip voice-record-chip--fab" id="voice-record-chip-fab">
            <button class="action-btn ghost" id="mic-btn-fab" type="button" aria-label="Mic">
                <img src="{{ asset('images/mic.png') }}" alt="Mic" class="action-icon"
                    data-mic-icon="{{ asset('images/mic.png') }}"
                    data-send-icon="{{ asset('images/send.png') }}">
            </button>
            <span class="voice-record-timer" id="voice-record-timer-fab">00:00</span>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>

</body>

</html>
