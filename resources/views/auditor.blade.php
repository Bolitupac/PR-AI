<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

                    <div class="signin-anchor">
                        <button class="icon-btn bottom" type="button" aria-label="GitHub">
                            <img src="{{ asset('images/github.png') }}" alt="GitHub" class="ui-logo">
                        </button>
                        <div class="signin-popover" role="dialog" aria-label="Sign in">
                            @auth
                                <div class="signin-title">Connected</div>
                                <div class="signin-profile">
                                    <img class="signin-avatar"
                                        src="https://github.com/{{ auth()->user()->github_username }}.png"
                                        alt="GitHub avatar">
                                    <div class="signin-meta">
                                        <div class="signin-name">{{ auth()->user()->name ?? 'User' }}</div>
                                        <div class="signin-handle">
                                            &#64;{{ auth()->user()->github_username ?? 'github-user' }}</div>
                                    </div>
                                </div>
                                <button class="signin-action" type="button">Open profile</button>
                            @endauth
                            @guest
                                <div class="signin-title">Connect GitHub</div>
                                <div class="signin-desc">Link your GitHub to load repositories and PRs.</div>
                                <a class="signin-action" href="{{ route('github.redirect') }}">Connect GitHub</a>
                            @endguest
                        </div>
                    </div>
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
                                    data-pull-diff-url="{{ route('github.pull-diff') }}">
                                    Select git repo
                                </button>
                                <button class="repo-upload-btn" id="upload-diff-btn" type="button">Upload diff file</button>
                                <button class="repo-upload-btn" id="render-diff-btn" type="button">Render Diff</button>
                            </div>
                        @endauth
                        @guest
                            <div class="panel-actions">
                                <a class="repo-connect" href="{{ route('github.redirect') }}">Connect GitHub</a>
                                <button class="repo-upload-btn" id="render-diff-btn" type="button">Render Diff</button>
                            </div>
                        @endguest
                    </header>
                    <div class="editor-status is-info" id="editor-diff-status">Paste unified git diff, then click Render
                        Diff.</div>
                    <div id="monaco-editor" aria-label="Code Editor"></div>
                </section>

                <section class="ai-panel">
                    <div class="ai-content">
                        <header class="panel-head ai-head">
                            <h3>PR ai</h3>
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
                        <button class="action-btn ghost" id="mic-btn" type="button" aria-label="Mic">
                            <img src="{{ asset('images/mic.png') }}" alt="Mic" class="action-icon">
                        </button>

                        <textarea class="chat-input" id="user-prompt" rows="1" placeholder="Ask AI..."></textarea>

                        <button class="action-btn" id="send-btn" type="button" aria-label="Send"
                            data-chat-url="{{ route('ai.chat') }}">
                            <img src="{{ asset('images/send.png') }}" alt="Send" class="action-icon">
                        </button>
                    </div>
                </section>
            </main>
        </section>

        @include('partials.diff-viewer')
    </div>

    @include('partials.repo-import')
    @include('partials.diff-upload')

    <div class="mic-listening-modal" id="mic-listening-modal" aria-hidden="true">
        <div class="mic-listening-card" role="dialog" aria-modal="true" aria-label="Voice Listening">
            <div class="mic-listening-top">
                <h4 class="mic-listening-title">Listening...</h4>
                <button class="mic-listening-close" id="mic-listening-close" type="button" aria-label="Close">&times;</button>
            </div>
            <div class="mic-listening-body">
                <div class="mic-wave"></div>
                <p class="mic-listening-text">Speak now. Your voice input will appear in chat.</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>

</body>

</html>
