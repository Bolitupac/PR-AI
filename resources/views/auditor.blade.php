<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                        <button class="repo-select" id="repo-select" type="button">Select repo</button>
                    @endauth
                    @guest
                        <a class="repo-connect" href="{{ route('github.redirect') }}">Connect GitHub</a>
                    @endguest
                </header>
                <div id="monaco-editor" aria-label="Code Editor"></div>
            </section>

            <section class="ai-panel">
                <div class="ai-content">
                    <header class="panel-head ai-head">
                        <h3>PR ai</h3>
                    </header>
                    <hr class="line-sep">

                    <div id="ai-response-area" class="chat-demo-list">
                        <div class="msg user">Can you summarize the most risky changes in this pull request?</div>
                        <div class="msg ai">I found 2 high-risk logic changes in payment validation and role checks.</div>
                        <div class="msg user">Show me one critical issue quickly.</div>
                        <div class="msg ai">In <code>CheckoutService.php</code>, discount can go negative after stacked promos. Add a floor at zero.</div>
                        <div class="msg ai">Scroll down to review the diff and all detected issues.</div>
                    </div>
                </div>

                <div class="chat-container">
                    <button class="action-btn ghost" id="mic-btn" type="button" aria-label="Mic">
                        <img src="{{ asset('images/mic.png') }}" alt="Mic" class="action-icon">
                    </button>

                    <input type="text" class="chat-input" id="user-prompt" placeholder="Ask Gemini...">

                    <button class="action-btn" id="send-btn" type="button" aria-label="Send">
                        <img src="{{ asset('images/send.png') }}" alt="Send" class="action-icon">
                    </button>
                </div>
            </section>
        </main>
    </section>

    <section class="diff-section" id="diff-section">
        <div class="diff-wrap">
            <div class="diff-head">
                <h2>Pull Request Diff Summary</h2>
                <span class="badge-total">0 Errors</span>
            </div>
            <div id="diff2html-container"></div>
        </div>
    </section>
</div>

@include('partials.repo-import')

<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>
<script>
    require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs' } });
    require(['vs/editor/editor.main'], function () {
        const defaultCode = '<' + '?php\n\n// Paste logic here to audit...\n\nclass LogicAuditor {\n    public function check() {\n        return true;\n    }\n}';

        window.editor = monaco.editor.create(document.getElementById('monaco-editor'), {
            value: defaultCode,
            language: 'php',
            theme: 'vs-dark',
            automaticLayout: true,
            fontSize: 15,
            lineHeight: 24,
            roundedSelection: true,
            scrollBeyondLastLine: false,
            readOnly: false,
            minimap: { enabled: false }
        });
    });

    const repoSelect = document.getElementById('repo-select');
    const repoModal = document.getElementById('repo-modal');
    const repoSelectModal = document.getElementById('repo-import-select');
    const repoModalClose = document.querySelectorAll('[data-close="repo-modal"]');
    let reposLoaded = false;

    async function loadRepos() {
        if (reposLoaded || !repoSelectModal) return;
        try {
            const res = await fetch('{{ route('github.repos') }}');
            if (!res.ok) throw new Error('Failed to load repos');
            const data = await res.json();
            repoSelectModal.innerHTML = '';
            if (!data.repos || data.repos.length === 0) {
                const opt = document.createElement('option');
                opt.textContent = 'No repositories found';
                opt.disabled = true;
                opt.selected = true;
                repoSelectModal.appendChild(opt);
                return;
            }
            data.repos.forEach((repo) => {
                const opt = document.createElement('option');
                opt.value = repo.full_name;
                opt.textContent = repo.full_name || repo.name;
                repoSelectModal.appendChild(opt);
            });
            reposLoaded = true;
        } catch (e) {
            repoSelectModal.innerHTML = '<option selected disabled>Failed to load repos</option>';
        }
    }

    function openRepoModal() {
        if (!repoModal) return;
        repoModal.classList.add('is-open');
        repoModal.setAttribute('aria-hidden', 'false');
        loadRepos();
    }

    function closeRepoModal() {
        if (!repoModal) return;
        repoModal.classList.remove('is-open');
        repoModal.setAttribute('aria-hidden', 'true');
    }

    if (repoSelect) {
        repoSelect.addEventListener('click', function (event) {
            event.preventDefault();
            openRepoModal();
        });
    }

    repoModalClose.forEach((btn) => {
        btn.addEventListener('click', closeRepoModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeRepoModal();
    });
</script>

</body>
</html>
