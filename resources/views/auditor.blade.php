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
    <title>Git PULL Assistant | Auditor</title>
    @vite(['resources/css/auditor-ui.css', 'resources/js/app.js'])
</head>

<body>

    <div class="app-shell">
        <section class="hero-view">
            <aside class="sidebar-bg">
                <div class="sidebar-bottom">
                    <button class="icon-btn" type="button" aria-label="Menu">
                        <img src="{{ asset('images/menu.png') }}" alt="Menu" class="ui-logo">
                    </button>

                    @auth
                        <button class="icon-btn bottom profile-avatar-btn" id="open-profile-btn" type="button"
                            aria-label="Open profile">
                            <img class="profile-avatar-img" src="https://github.com/{{ auth()->user()->github_username }}.png"
                                alt="GitHub avatar">
                        </button>
                    @endauth
                    @guest
                        <button class="icon-btn bottom" id="open-profile-btn" type="button" aria-label="Open profile">
                            <img src="{{ asset('images/github.png') }}" alt="GitHub" class="ui-logo">
                        </button>
                    @endguest
                </div>
            </aside>

            <main class="main-workspace">
                <section class="codebox-outer">
                    <header class="panel-head">
                        <span class="panel-title">Logic Auditor</span>
                        @auth
                            <div class="panel-actions">
                                <button class="repo-select" id="repo-select" type="button"
                                    data-repos-url="{{ route('github.repos') }}"
                                    data-pulls-url="{{ route('github.pulls') }}"
                                    data-pull-diff-url="{{ route('github.pull-diff') }}"
                                    data-snapshot-url="{{ route('audit.snapshot') }}">
                                    Select git repo
                                </button>
                                <button class="repo-upload-btn" id="upload-diff-btn" type="button"
                                    data-snapshot-url="{{ route('audit.snapshot') }}">Upload diff file</button>
                                <button class="repo-upload-btn" id="render-diff-btn" type="button"
                                    data-snapshot-url="{{ route('audit.snapshot') }}">Render Diff</button>
                            </div>
                        @endauth
                        @guest
                            <div class="panel-actions">
                                <a class="repo-connect" href="{{ route('github.redirect') }}" data-loading-link
                                    data-loading-text="Redirecting">
                                    Connect GitHub
                                </a>
                                <button class="repo-upload-btn" id="render-diff-btn" type="button"
                                    data-snapshot-url="{{ route('audit.snapshot') }}">Render Diff</button>
                            </div>
                        @endguest
                    </header>
                    <div class="editor-status is-info" id="editor-diff-status">Paste unified git diff, then click Render
                        Diff.</div>
                    <div id="monaco-editor" aria-label="Code Editor"></div>
                </section>

                <section class="ai-panel">
                    <div class="ai-content">
                        @php
                            $chatModels = config('openai.chat_models', [config('openai.model', 'gpt-4o-mini')]);
                            $defaultChatModel = config('openai.model', 'gpt-4o-mini');
                        @endphp
                        <header class="panel-head ai-head">
                            <h3>PR ai</h3>
                            <select class="chat-model-select" id="chat-model-select" aria-label="Select model">
                                @foreach ($chatModels as $chatModel)
                                    <option value="{{ $chatModel }}" @selected($chatModel === $defaultChatModel)>{{ $chatModel }}</option>
                                @endforeach
                            </select>
                        </header>
                        <hr class="line-sep">

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
                                data-transcribe-url="{{ route('ai.transcribe') }}">
                                <img src="{{ asset('images/send.png') }}" alt="Send" class="action-icon">
                            </button>
                        </div>
                        <div class="chat-tools-row">
                            <div class="voice-record-chip" id="voice-record-chip">
                                <button class="action-btn ghost" id="mic-btn" type="button" aria-label="Mic">
                                    <img src="{{ asset('images/mic.png') }}" alt="Mic" class="action-icon">
                                </button>
                                <span class="voice-record-timer" id="voice-record-timer">00:00</span>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </section>

        @include('partials.diff-viewer')
    </div>

    <footer class="site-credit" aria-label="Copyright">
        <div class="site-credit-line"><span class="site-credit-by">by</span> <span class="site-credit-brand">BOLITUPAC</span></div>
        <div class="site-credit-line">&copy;2026</div>
    </footer>

    @include('partials.repo-import')
    @include('partials.diff-upload')
    @include('partials.profile-modal')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>

</body>

</html>
