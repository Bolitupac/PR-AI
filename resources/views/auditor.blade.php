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
                                <img class="signin-avatar" src="https://github.com/{{ auth()->user()->github_username }}.png" alt="GitHub avatar">
                                <div class="signin-meta">
                                    <div class="signin-name">{{ auth()->user()->name ?? 'User' }}</div>
                                    <div class="signin-handle">&#64;{{ auth()->user()->github_username ?? 'github-user' }}</div>
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
                            <button
                                class="repo-select"
                                id="repo-select"
                                type="button"
                                data-repos-url="{{ route('github.repos') }}"
                                data-pulls-url="{{ route('github.pulls') }}"
                                data-pull-diff-url="{{ route('github.pull-diff') }}"
                            >
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
                <div class="editor-status is-info" id="editor-diff-status">Paste unified git diff, then click Render Diff.</div>
                <div id="monaco-editor" aria-label="Code Editor"></div>
            </section>

            <section
                class="ai-panel"
                id="ai-panel"
                data-authenticated="{{ auth()->check() ? '1' : '0' }}"
                data-ai-audit-url="{{ route('ai.audit-pr') }}"
                data-ai-chat-url="{{ route('ai.chat-pr') }}"
            >
                <div class="ai-content">
                    <header class="panel-head ai-head">
                        <h3>PR ai</h3>
                        <button class="repo-upload-btn" id="run-ai-audit-btn" type="button">Run AI Audit</button>
                    </header>
                    <hr class="line-sep">

                    <div id="ai-response-area" class="chat-demo-list">
                        <div class="msg ai">Load or paste a diff, then click Run AI Audit.</div>
                    </div>
                </div>

                <div class="chat-container">
                    <button class="action-btn ghost" id="mic-btn" type="button" aria-label="Mic">
                        <img src="{{ asset('images/mic.png') }}" alt="Mic" class="action-icon">
                    </button>

                    <input type="text" class="chat-input" id="user-prompt" placeholder="Ask Gemini about this diff...">

                    <button class="action-btn" id="send-btn" type="button" aria-label="Send">
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>

</body>
</html>
