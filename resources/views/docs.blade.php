<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.meta', [
        'metaTitle' => 'PR ai Docs',
        'metaDescription' => 'PR ai documentation for imports, audits, DocGen, voice, API keys, privacy, and security.',
        'metaType' => 'website',
    ])
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo 512 transp bg white color svg.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geologica:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/docs.css', 'resources/js/docs.js'])
</head>
<body>
@php
    $toolEntryUrl = auth()->check() ? route('auditor.index') : route('login');
    $currentPage = $currentPage ?? 'overview';
    $pages = [
        'overview' => ['label' => 'Overview', 'icon' => 'M4 5.5A1.5 1.5 0 0 1 5.5 4h13A1.5 1.5 0 0 1 20 5.5v13A1.5 1.5 0 0 1 18.5 20h-13A1.5 1.5 0 0 1 4 18.5v-13Zm3 2.5h10v2H7V8Zm0 4h10v2H7v-2Zm0 4h6v2H7v-2Z', 'class' => 'docs-nav-link--overview'],
        'quickstart' => ['label' => 'Getting started', 'icon' => 'M12 2 4 6v12l8 4 8-4V6l-8-4Zm0 4.2L16.9 8 12 10.8 7.1 8 12 6.2ZM6 9.4l5 2.8v5.8L6 15.2V9.4Zm12 5.8-5 2.8v-5.8l5-2.8v5.8Z', 'class' => 'docs-nav-link--quickstart'],
        'auth' => ['label' => 'Auth & connections', 'icon' => 'M14 3a5 5 0 0 0-3.6 8.5L4 18v2h2v-1h2v-2h2v-2h2l1.1-1.1A5 5 0 1 0 14 3Zm0 2a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z', 'class' => 'docs-nav-link--keys'],
        'workspace' => ['label' => 'Auditor workspace', 'icon' => 'M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v11A2.5 2.5 0 0 1 17.5 20h-11A2.5 2.5 0 0 1 4 17.5v-11ZM7 7v10h10V7H7Zm2 2h6v2H9V9Zm0 4h6v2H9v-2Z', 'class' => 'docs-nav-link--overview'],
        'imports' => ['label' => 'Importing code', 'icon' => 'M12 2.5 20 6v3H4V6l8-3.5ZM5 10h14v9H5v-9Zm3 2v5h2v-5H8Zm4 0v5h2v-5h-2Z', 'class' => 'docs-nav-link--imports'],
        'audits' => ['label' => 'Audit modes', 'icon' => 'M12 2 2 6l10 4 8-3.2V16h2V6L12 2Zm-7 8v4l7 3 7-3v-4l-7 3-7-3Z', 'class' => 'docs-nav-link--audits'],
        'results' => ['label' => 'Audit results', 'icon' => 'M12 2 4 6v7c0 4.7 3.2 8.9 8 10 4.8-1.1 8-5.3 8-10V6l-8-4Zm-1 6h2v6h-2V8Zm0 7h2v2h-2v-2Z', 'class' => 'docs-nav-link--security'],
        'chat' => ['label' => 'AI chat', 'icon' => 'M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8A2.5 2.5 0 0 1 17.5 16H10l-4 4v-4H6.5A2.5 2.5 0 0 1 4 13.5v-8ZM8 7h8v1.5H8V7Zm0 3h6v1.5H8V10Z', 'class' => 'docs-nav-link--voice'],
        'docgen' => ['label' => 'DocGen', 'icon' => 'M6 4h9l3 3v13H6V4Zm8 1.5V8h2.5L14 5.5ZM8 11h7v1.5H8V11Zm0 3h7v1.5H8V14Z', 'class' => 'docs-nav-link--docgen'],
        'voice' => ['label' => 'Voice input', 'icon' => 'M12 14a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v5a3 3 0 0 0 3 3Zm-5-3a5 5 0 0 0 10 0h2a7 7 0 0 1-6 6.93V21h-2v-3.07A7 7 0 0 1 5 11h2Z', 'class' => 'docs-nav-link--voice'],
        'diff' => ['label' => 'Git diff viewer', 'icon' => 'M7 5h10v2H7V5Zm-3 5h16v2H4v-2Zm3 5h10v2H7v-2Z', 'class' => 'docs-nav-link--imports'],
        'advanced' => ['label' => 'Advanced features', 'icon' => 'M12 3 4.8 8l2.5 11h7.4l2.5-11L12 3Zm0 3 3.9 2.6-1.7 7.4H9.8L8.1 8.6 12 6Z', 'class' => 'docs-nav-link--audits'],
        'keys' => ['label' => 'API keys', 'icon' => 'M14 3a5 5 0 0 0-3.6 8.5L4 18v2h2v-1h2v-2h2v-2h2l1.1-1.1A5 5 0 1 0 14 3Zm0 2a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z', 'class' => 'docs-nav-link--keys'],
        'settings' => ['label' => 'Settings', 'icon' => 'M12 8.2A3.8 3.8 0 1 0 12 15.8 3.8 3.8 0 0 0 12 8.2Zm8 3.8-.9.5.1 1.1 1.7 1.7-1.9 1.9-1.7-1.7-1.1-.1-.5.9-.4 1.4h-2.7l-.4-1.4-.5-.9-1.1.1-1.7 1.7-1.9-1.9 1.7-1.7.1-1.1-.9-.5-1.4-.4v-2.7l1.4-.4.9-.5-.1-1.1-1.7-1.7 1.9-1.9 1.7 1.7 1.1.1.5-.9.4-1.4h2.7l.4 1.4.5.9 1.1-.1 1.7-1.7 1.9 1.9-1.7 1.7-.1 1.1.9.5 1.4.4v2.7l-1.4.4Z', 'class' => 'docs-nav-link--overview'],
        'providers' => ['label' => 'VCS compare', 'icon' => 'M4 7h16v10H4V7Zm2 2v6h12V9H6Zm2 1h3v4H8v-4Zm5 0h3v4h-3v-4Z', 'class' => 'docs-nav-link--privacy'],
        'troubleshooting' => ['label' => 'Troubleshooting', 'icon' => 'M12 2 2.5 20h19L12 2Zm0 5.5a1 1 0 0 1 1 1V12h-2V8.5a1 1 0 0 1 1-1Zm-1 8.5h2v2h-2v-2Z', 'class' => 'docs-nav-link--faq'],
        'roadmap' => ['label' => 'Roadmap', 'icon' => 'M4 17h16v2H4v-2Zm2-5h4v4H6v-4Zm6-4h4v8h-4V8Zm6-4h2v12h-2V4Z', 'class' => 'docs-nav-link--audits'],
        'shortcuts' => ['label' => 'Shortcuts', 'icon' => 'M5 5h3v3H5V5Zm0 11h3v3H5v-3Zm11-11h3v3h-3V5Zm0 11h3v3h-3v-3ZM10 10h4v4h-4v-4Z', 'class' => 'docs-nav-link--quickstart'],
        'workflows' => ['label' => 'Workflows', 'icon' => 'M4 6h7v2H4V6Zm0 5h10v2H4v-2Zm0 5h14v2H4v-2Zm15-9h1v10h-1V7Z', 'class' => 'docs-nav-link--docgen'],
        'security' => ['label' => 'Security', 'icon' => 'M12 2 4 5v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V5l-8-3Zm0 6a2 2 0 0 1 2 2v2h1v5H9v-5h1v-2a2 2 0 0 1 2-2Z', 'class' => 'docs-nav-link--security'],
        'privacy' => ['label' => 'Privacy', 'icon' => 'M12 2a6 6 0 0 0-6 6v3H5a2 2 0 0 0-2 2v5h16v-5a2 2 0 0 0-2-2h-1V8a6 6 0 0 0-6-6Zm-3 9V8a3 3 0 1 1 6 0v3H9Z', 'class' => 'docs-nav-link--privacy'],
        'faq' => ['label' => 'FAQ', 'icon' => 'M12 18h.01M12 14a4 4 0 1 0-4-4h2a2 2 0 1 1 2 2v2Z', 'class' => 'docs-nav-link--faq'],
    ];
    $navGroups = [
        ['label' => 'Getting started', 'items' => ['overview', 'quickstart', 'auth', 'workspace']],
        ['label' => 'Core docs', 'items' => ['imports', 'audits', 'results', 'chat', 'docgen', 'voice', 'diff']],
        ['label' => 'Reference', 'items' => ['advanced', 'keys', 'settings', 'providers', 'security', 'privacy', 'shortcuts', 'workflows']],
        ['label' => 'Support', 'items' => ['troubleshooting', 'roadmap', 'faq']],
    ];
@endphp

<div class="docs-shell">
    <aside class="docs-sidebar">
        <a href="{{ route('docs.index') }}" class="docs-brand">
            <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo">
            <span class="docs-brand-mark">
                <strong>PR ai</strong>
                <span>Docs</span>
            </span>
        </a>

        <nav class="docs-nav-group" aria-label="Documentation pages">
            @foreach ($navGroups as $group)
                <span class="docs-nav-label">{{ $group['label'] }}</span>
                @foreach ($group['items'] as $slug)
                    @php($page = $pages[$slug])
                    <a href="{{ route('docs.index', ['page' => $slug]) }}" class="docs-nav-link {{ $page['class'] }} {{ $currentPage === $slug ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $page['icon'] }}"></path></svg>
                        <span>{{ $page['label'] }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="docs-sidebar-card">
            <div class="docs-kicker">Need the app?</div>
            <h3>Jump back to PR ai</h3>
            <p>Open the Auditor, browse imports, or keep reviewing without losing context.</p>
            <div class="docs-sidebar-actions">
                <a href="{{ $toolEntryUrl }}" class="docs-btn docs-btn--primary">Open app</a>
                <a href="{{ route('imports.index') }}" class="docs-btn">Go to imports</a>
            </div>
        </div>
    </aside>

    <main class="docs-main">
        <header class="docs-topbar">
            <div class="docs-hero-brand">
                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="">
                <span>Docs</span>
            </div>
            <div class="docs-topbar-search">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m20 20-3.5-3.5"></path>
                </svg>
                <input id="docs-search" type="search" placeholder="Search this page">
            </div>
            <button class="docs-theme-toggle" id="docs-theme-toggle" type="button" aria-label="Switch to dark mode" title="Switch theme">
                <svg id="docs-theme-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.5A8.5 8.5 0 1 1 9.5 3.5a7 7 0 0 0 11 11Z" fill="currentColor"></path></svg>
            </button>
        </header>

        <section class="docs-page docs-page-content" data-docs-page>
            @switch($currentPage)
                @case('overview')
                    <div class="docs-hero" style="--docs-order:0">
                        <div class="docs-kicker">PR ai docs</div>
                        <h1>Everything you need to import, audit, and document code with PR ai</h1>
                        <p>Use the left nav to jump into a focused page. The docs keep the same flatter, darker auditor look, but the content is broader and broken into real product pages instead of anchor sections.</p>
                    </div>
                    <div class="docs-grid" style="--docs-order:1">
                        <a class="docs-card" href="{{ route('docs.index', ['page' => 'quickstart']) }}">
                            <h3>Getting started</h3><p>Sign-in, first login, browser support, and fast setup.</p>
                        </a>
                        <a class="docs-card" href="{{ route('docs.index', ['page' => 'imports']) }}">
                            <h3>Importing code</h3><p>Repos, diffs, branches, commits, uploads, and paste flows.</p>
                        </a>
                        <a class="docs-card" href="{{ route('docs.index', ['page' => 'audits']) }}">
                            <h3>Audit modes</h3><p>Branch, PR, commit, merge conflict, and manual audits.</p>
                        </a>
                        <a class="docs-card" href="{{ route('docs.index', ['page' => 'settings']) }}">
                            <h3>Settings</h3><p>Theme, providers, voice, chat history, and preferences.</p>
                        </a>
                    </div>
                    <section class="docs-section" style="--docs-order:2">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">What’s inside</div>
                                <h2>Docs pages now map to the full product</h2>
                                <p class="docs-section-intro">Each major feature has its own page so users can jump straight to what they need without hunting through one long help sheet.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Clear entry points</h4><p>Auth, imports, audits, DocGen, and settings all get their own page.</p></div>
                            <div class="docs-note"><h4>Theme match</h4><p>The docs use the same darker audit palette and smaller borders as the app.</p></div>
                            <div class="docs-note"><h4>Searchable</h4><p>The top search filters the current page so users can jump to the right section fast.</p></div>
                            <div class="docs-note"><h4>Single-click navigation</h4><p>Every sidebar item opens a dedicated docs route, not a hash section.</p></div>
                        </div>
                    </section>
                    @break
                @case('quickstart')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Getting started</div>
                                <h2>Quick start</h2>
                                <p class="docs-section-intro">Get from sign-in to the first audit quickly.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>1. Sign in</h4><p>Use GitHub or GitLab OAuth to unlock Imports, the Auditor, and chat.</p></div>
                            <div class="docs-note"><h4>2. Open imports</h4><p>Pick a repository, branch, pull request, or commit from the provider browser.</p></div>
                            <div class="docs-note"><h4>3. Choose a source</h4><p>Run a branch audit, upload a diff, paste code, or start from a commit.</p></div>
                            <div class="docs-note"><h4>4. Review results</h4><p>Read the audit, ask follow-up questions, and refine the output in chat.</p></div>
                        </div>
                    </section>
                    <section class="docs-section" style="--docs-order:1">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">First login</div>
                                <h2>New user checklist</h2>
                            </div>
                        </div>
                        <ol class="docs-list">
                            <li>Allow the provider connection requested during sign-in.</li>
                            <li>Confirm the app can access the repositories you want to review.</li>
                            <li>Open Imports and make sure the repo list is loading correctly.</li>
                            <li>If you hit a blank page, refresh once and retry the connection.</li>
                        </ol>
                    </section>
                    @break
                @case('auth')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Authentication</div>
                                <h2>Connections and provider access</h2>
                                <p class="docs-section-intro">PR ai supports provider logins so the app can read repos, branches, PRs, and commit history.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>GitHub</h4><p>OAuth login for GitHub-hosted repos and branch browsing.</p></div>
                            <div class="docs-note"><h4>GitLab</h4><p>GitLab OAuth and self-hosted access where configured.</p></div>
                            <div class="docs-note"><h4>Reconnect</h4><p>If a token expires, reconnect from the provider flow or settings.</p></div>
                            <div class="docs-note"><h4>Best practice</h4><p>Grant only the repository scopes you need for the review session.</p></div>
                        </div>
                    </section>
                    <section class="docs-section" style="--docs-order:1">
                        <div class="docs-section-head"><div><div class="docs-kicker">Security</div><h2>Token hygiene</h2></div></div>
                        <div class="docs-faq">
                            <div class="docs-faq-item"><strong>Use short-lived access where possible</strong><p>It reduces risk if a session is left open.</p></div>
                            <div class="docs-faq-item"><strong>Disconnect stale providers</strong><p>Remove old connections you do not use anymore.</p></div>
                            <div class="docs-faq-item"><strong>Retry with a fresh login</strong><p>Many access problems are just expired OAuth sessions.</p></div>
                        </div>
                    </section>
                    @break
                @case('workspace')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Workspace</div>
                                <h2>The Auditor workspace</h2>
                                <p class="docs-section-intro">The workspace is the main review surface: import controls up top, chat in the middle, and supporting tools around it.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Header</h4><p>Import, model selection, settings, and theme live in the top bar.</p></div>
                            <div class="docs-note"><h4>Chat area</h4><p>Ask for security, performance, or refactor feedback without leaving the review.</p></div>
                            <div class="docs-note"><h4>Sidebar</h4><p>Conversation history, imports, and helper actions stay available while you work.</p></div>
                            <div class="docs-note"><h4>Shortcuts</h4><p>Keyboard support keeps the review flow quick for power users.</p></div>
                        </div>
                    </section>
                    @break
                @case('imports')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Imports</div>
                                <h2>Importing code</h2>
                                <p class="docs-section-intro">Import from repos, diffs, pasted code, or manual snippets. Branch audits work best on active branches — merged branches may still be auditable if a merge commit exists in the recent history (see Audit modes for details).</p>
                            </div>
                        </div>
                        <table class="docs-table">
                            <thead><tr><th>Source</th><th>What loads</th><th>Best for</th></tr></thead>
                            <tbody>
                                <tr><td>Repository</td><td>Branches, PRs, commits</td><td>Live provider browsing</td></tr>
                                <tr><td>Branch</td><td>Branch-to-base compare</td><td>Feature review</td></tr>
                                <tr><td>Upload</td><td>.diff or .patch files</td><td>Offline diffs</td></tr>
                                <tr><td>Paste</td><td>Raw code or patch text</td><td>Quick manual review</td></tr>
                            </tbody>
                        </table>
                    </section>
                    <section class="docs-section" style="--docs-order:1">
                        <div class="docs-section-head"><div><div class="docs-kicker">Methods</div><h2>Four import paths</h2></div></div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Provider browser</h4><p>Use GitHub or GitLab to choose repos, branches, pull requests, and recent commits.</p></div>
                            <div class="docs-note"><h4>Uploaded diff</h4><p>Useful when you already have a patch file and do not need the provider API.</p></div>
                            <div class="docs-note"><h4>Pasted content</h4><p>Paste code or diff text directly into the editor for a fast ad hoc audit.</p></div>
                            <div class="docs-note"><h4>Manual entry</h4><p>Write or edit a snippet in the built-in editor when you need a custom review sample.</p></div>
                        </div>
                    </section>
                    @break
                @case('audits')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Audit modes</div>
                                <h2>How audits work</h2>
                                <p class="docs-section-intro">PR ai runs AI-powered security reviews against diffs fetched from your VCS provider. Each audit mode frames the report differently, but the core engine is the same: it evaluates every change against the OWASP Top 10 and VAPT methodology.</p>
                            </div>
                        </div>
                        <table class="docs-table">
                            <thead><tr><th>Mode</th><th>What it compares</th><th>Best for</th></tr></thead>
                            <tbody>
                                <tr><td>Pull request</td><td>PR diff with metadata and review comments</td><td>Pre-merge review</td></tr>
                                <tr><td>Branch</td><td>Entire branch against its base branch</td><td>Feature review, integration readiness</td></tr>
                                <tr><td>Commit</td><td>Single commit diff</td><td>Targeted historical review</td></tr>
                                <tr><td>Merge conflict</td><td>Active conflict markers</td><td>Safe conflict resolution</td></tr>
                                <tr><td>Manual</td><td>Pasted or uploaded diff</td><td>Quick ad hoc review</td></tr>
                            </tbody>
                        </table>
                    </section>
                    <section class="docs-section" style="--docs-order:1">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Branch audits</div>
                                <h2>How branch audits work</h2>
                                <p class="docs-section-intro">A branch audit compares your entire feature branch against a base branch (usually <code>main</code>) and produces a comprehensive security report focused on integration readiness and attack surface analysis.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Diff fetching</h4><p>The app calls your VCS provider's compare API — e.g. GitHubʼs <code>/compare/:base...:head</code> endpoint with <code>Accept: application/vnd.github.v3.diff</code>. This returns a raw unified diff of every change on the branch relative to its base.</p></div>
                            <div class="docs-note"><h4>Empty diff fallback</h4><p>If the comparison returns empty (usually because the branch was already merged), the app scans the 50 most recent commits on the base branch for a merge commit whose message contains the branch name. If found, the merge commitʼs diff is used instead.</p></div>
                            <div class="docs-note"><h4>Prompt composition</h4><p>The diff is parsed into changed lines, truncated at 200&thinsp;KB if needed (1400-line budget), and assembled into a structured prompt with the branch name, base branch, repo, and any available PR context.</p></div>
                            <div class="docs-note"><h4>AI analysis</h4><p>The model produces a full report: Branch Overview → Executive Summary → OWASP Top 10 Coverage → VAPT Findings → Impact Map → Logic Flow → Detailed Walkthrough → Remediation Roadmap. Special attention goes to A01 (access control) and A05 (security misconfiguration).</p></div>
                        </div>
                    </section>
                    <section class="docs-section" style="--docs-order:2">
                        <div class="docs-section-head"><div><div class="docs-kicker">Comparison</div><h2>Branch audit vs. PR audit</h2></div></div>
                        <table class="docs-table">
                            <thead><tr><th>Dimension</th><th>Branch audit</th><th>PR audit</th></tr></thead>
                            <tbody>
                                <tr><td>Diff budget</td><td>200&thinsp;KB</td><td>120&thinsp;KB</td></tr>
                                <tr><td>Changed-line limit</td><td>1&hairsp;400 lines</td><td>700 lines</td></tr>
                                <tr><td>Audit status</td><td><code>active</code></td><td><code>open</code> / <code>draft</code> / <code>merged</code></td></tr>
                                <tr><td>Report structure</td><td>Adds a Branch Overview section before the Executive Summary</td><td>Standard sections</td></tr>
                                <tr><td>OWASP emphasis</td><td>A01 (access control), A05 (security misconfiguration)</td><td>A03 (injection), A01, A07 (auth failures)</td></tr>
                                <tr><td>AI focus</td><td>Branch readiness, missing checks, attack-surface delta, integration risk</td><td>Merge readiness, correctness, security risk</td></tr>
                                <tr><td>Suggestion labels</td><td>Ready for merge / Revise before merge / Review before merge</td><td>Merge / Donʼt merge / Review then merge</td></tr>
                            </tbody>
                        </table>
                    </section>
                    <section class="docs-section" style="--docs-order:3">
                        <div class="docs-section-head"><div><div class="docs-kicker">Merged branches</div><h2>Can I audit a branch that has already been merged?</h2></div></div>
                        <p class="docs-section-intro">It depends on <em>how</em> the branch was merged.</p>
                        <div class="docs-faq">
                            <div class="docs-faq-item"><strong>Merge commit (standard merge)</strong><p>✅ Yes. The branch comparison returns empty because all changes are already in the base branch, but the fallback finds the merge commit — a commit with two or more parents — and audits its diff. This works as long as the merge commit is among the 50 most recent commits on the base branch.</p></div>
                            <div class="docs-faq-item"><strong>Squash merge</strong><p>❌ No. Squash merges combine all branch commits into a single commit with only <em>one</em> parent. There is no merge commit with two parents to find, so the fallback returns empty and the audit is silently skipped.</p></div>
                            <div class="docs-faq-item"><strong>Rebase + fast-forward</strong><p>❌ No. Rebase replays commits onto the base branch and fast-forward merges produce no merge commit at all. Without a merge commit the fallback has nothing to latch onto.</p></div>
                            <div class="docs-faq-item"><strong>Old merge (more than 50 commits ago)</strong><p>❌ No. The fallback only scans the 50 most recent commits on the base branch to keep the API call fast. If the merge commit has aged out of that window it will not be found.</p></div>
                            <div class="docs-faq-item"><strong>Deleted branch</strong><p>⚠️ Probably not. If the branch reference was deleted, some providers (GitHub) may still return a diff from the compare API since the commits still exist. But if both the branch and its commits are gone, the API returns <code>404</code>.</p></div>
                        </div>
                    </section>
                    <section class="docs-section" style="--docs-order:4">
                        <div class="docs-section-head"><div><div class="docs-kicker">Errors</div><h2>When branch audits fail</h2></div></div>
                        <table class="docs-table">
                            <thead><tr><th>Error</th><th>Cause</th><th>What happens</th></tr></thead>
                            <tbody>
                                <tr><td>No VCS connection</td><td>Session expired or provider never connected</td><td>HTTP 401 — "Connect the provider to load branch diffs."</td></tr>
                                <tr><td>Invalid repo</td><td>Empty or malformed repo name</td><td>HTTP 422 — "Invalid repo"</td></tr>
                                <tr><td>Missing branches</td><td><code>base</code> or <code>head</code> not provided</td><td>HTTP 422 — "Base and head branches are required"</td></tr>
                                <tr><td>API HTTP error</td><td>Provider returns 4xx or 5xx</td><td>User-friendly message explaining the status code</td></tr>
                                <tr><td>Diff too large</td><td>Raw diff exceeds 200&thinsp;KB</td><td>Diff is truncated at a line boundary; the AI acknowledges the truncation</td></tr>
                                <tr><td>Branch not found</td><td>Head branch deleted or never existed</td><td>Provider returns 404; error message shown</td></tr>
                                <tr><td>Empty diff (no fallback)</td><td>Squash merge, rebase, or old merge with no merge commit found</td><td>Returns empty string silently; audit does not run</td></tr>
                                <tr><td>AI provider error</td><td>Model API fails during streaming</td><td>SSE <code>error</code> event sent to client with the error message</td></tr>
                                <tr><td>CSRF expiry</td><td>Session token expired</td><td>HTTP 419 — "Session expired" prompt to refresh</td></tr>
                                <tr><td>Payload too large</td><td>Diff exceeds the 10&thinsp;MB request limit</td><td>HTTP 413 — suggestion to audit individual PRs instead</td></tr>
                            </tbody>
                        </table>
                    </section>
                    @break
                @case('results')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Results</div>
                                <h2>Understanding audit results</h2>
                                <p class="docs-section-intro">The result view should tell you what changed, how risky it looks, and what to do next.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Risk scores</h4><p>Security, reliability, performance, and maintainability can be called out separately.</p></div>
                            <div class="docs-note"><h4>Suggestions</h4><p>Use the recommendation to decide whether to merge, review again, or stop.</p></div>
                            <div class="docs-note"><h4>Diagrams</h4><p>Mermaid-style flow or dependency diagrams help show the bigger picture.</p></div>
                            <div class="docs-note"><h4>Evidence</h4><p>Line references and comments make the audit easier to trust.</p></div>
                        </div>
                    </section>
                    @break
                @case('chat')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">AI chat</div>
                                <h2>Chat and follow-ups</h2>
                                <p class="docs-section-intro">Use chat to dig deeper into an audit instead of restarting the review from scratch.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Follow-up prompts</h4><p>Ask about security, performance, refactors, or specific files in the change.</p></div>
                            <div class="docs-note"><h4>Context stays with you</h4><p>The chat can reuse the current audit so the conversation stays grounded.</p></div>
                            <div class="docs-note"><h4>Pinpoint questions</h4><p>Ask for one function, one file, or one risk area at a time for better answers.</p></div>
                            <div class="docs-note"><h4>Conversation history</h4><p>Saved threads make it easier to return to prior investigations.</p></div>
                        </div>
                    </section>
                    @break
                @case('docgen')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">DocGen</div>
                                <h2>DocGen mode</h2>
                                <p class="docs-section-intro">DocGen turns code context into docs, README drafts, specs, or planning notes.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Drafts first</h4><p>Generate a draft before exporting to keep the output reviewable.</p></div>
                            <div class="docs-note"><h4>Prompt aware</h4><p>Ask for a README, API guide, setup note, or spec style doc.</p></div>
                            <div class="docs-note"><h4>Export ready</h4><p>Keep the generated result in a format that can be copied into your repo.</p></div>
                            <div class="docs-note"><h4>Clarify when needed</h4><p>DocGen can ask a question before it writes the final copy.</p></div>
                        </div>
                    </section>
                    @break
                @case('voice')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Voice</div>
                                <h2>Voice input</h2>
                                <p class="docs-section-intro">Voice is meant for quick prompts, long reviews, and hands-free follow-ups.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Enable mic access</h4><p>Allow microphone permission before you start recording.</p></div>
                            <div class="docs-note"><h4>Speak naturally</h4><p>Short phrases are easier to transcribe accurately than rushed long blocks.</p></div>
                            <div class="docs-note"><h4>Keep it available</h4><p>The floating voice control should stay accessible while you scroll.</p></div>
                            <div class="docs-note"><h4>Best for</h4><p>Quick follow-ups, long audit sessions, and hands-free notes.</p></div>
                        </div>
                    </section>
                    @break
                @case('diff')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Diffs</div>
                                <h2>Git diff viewer</h2>
                                <p class="docs-section-intro">The diff viewer should make changes easy to scan, even when the page is handling a large patch or an empty result.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Read headers</h4><p>Understand `---`, `+++`, and `@@` so the patch makes sense.</p></div>
                            <div class="docs-note"><h4>Unified view</h4><p>Use the compact format when the file is small or you need speed.</p></div>
                            <div class="docs-note"><h4>Line focus</h4><p>Highlight important lines so reviewers can jump to the right spot.</p></div>
                            <div class="docs-note"><h4>Graceful fallback</h4><p>If the diff cannot be rendered, keep the page usable and explain why.</p></div>
                        </div>
                    </section>
                    @break
                @case('advanced')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Advanced</div>
                                <h2>Advanced features</h2>
                                <p class="docs-section-intro">This page collects the power-user features that sit around the core audit flow.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Mermaid diagrams</h4><p>Visualize flow, sequence, state, or dependency ideas from the current context.</p></div>
                            <div class="docs-note"><h4>Comment overlays</h4><p>View PR comments alongside the code that triggered them.</p></div>
                            <div class="docs-note"><h4>Document uploads</h4><p>Bring in specs or notes so the chat has more context.</p></div>
                            <div class="docs-note"><h4>Inline comments</h4><p>Plan for review comments that can be placed directly on the change later.</p></div>
                        </div>
                    </section>
                    @break
                @case('keys')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">AI keys</div>
                                <h2>API key management</h2>
                                <p class="docs-section-intro">Choose between the shared developer key and your own personal key based on cost, privacy, and control.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Developer key</h4><p>Fastest to start, with shared credits managed by the app.</p></div>
                            <div class="docs-note"><h4>Personal key</h4><p>Use your own provider key when you want billing and usage under your account.</p></div>
                            <div class="docs-note"><h4>Model choice</h4><p>Switch models when you need speed, cost savings, or deeper reasoning.</p></div>
                            <div class="docs-note"><h4>Billing</h4><p>Keep an eye on usage so you do not run into surprises later.</p></div>
                        </div>
                    </section>
                    @break
                @case('settings')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Settings</div>
                                <h2>Settings and preferences</h2>
                                <p class="docs-section-intro">Use settings to control theme, AI behavior, provider connections, and session preferences.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Theme</h4><p>Light and dark modes are available, and the switch is in the top right.</p></div>
                            <div class="docs-note"><h4>Providers</h4><p>Connect or disconnect repos without leaving the app.</p></div>
                            <div class="docs-note"><h4>Voice</h4><p>Adjust microphone usage and language behavior when needed.</p></div>
                            <div class="docs-note"><h4>History</h4><p>Keep or clear chats based on your team’s workflow.</p></div>
                        </div>
                    </section>
                    @break
                @case('providers')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">VCS</div>
                                <h2>Provider comparison</h2>
                                <p class="docs-section-intro">GitHub, GitLab, Bitbucket, and Azure DevOps each fit a different team shape, and the docs should explain the tradeoffs clearly.</p>
                            </div>
                        </div>
                        <table class="docs-table">
                            <thead><tr><th>Provider</th><th>Strengths</th><th>Watchouts</th></tr></thead>
                            <tbody>
                                <tr><td>GitHub</td><td>Best ecosystem support</td><td>Public API limits</td></tr>
                                <tr><td>GitLab</td><td>Strong self-hosted option</td><td>Token scopes matter</td></tr>
                                <tr><td>Bitbucket</td><td>Useful for Jira-heavy teams</td><td>Some workflows differ</td></tr>
                                <tr><td>Azure DevOps</td><td>Enterprise-friendly</td><td>Requires careful token setup</td></tr>
                            </tbody>
                        </table>
                    </section>
                    @break
                @case('troubleshooting')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Support</div>
                                <h2>Troubleshooting and FAQs</h2>
                                <p class="docs-section-intro">If something looks broken, this page should help you narrow it down fast.</p>
                            </div>
                        </div>
                        <div class="docs-faq">
                            <div class="docs-faq-item"><strong>Auth fails</strong><p>Reconnect the provider and confirm the token scopes are still valid.</p></div>
                            <div class="docs-faq-item"><strong>Diff does not render</strong><p>Keep the page usable and show an explanation instead of throwing the user out.</p></div>
                            <div class="docs-faq-item"><strong>Voice is noisy</strong><p>Try a quieter room and shorter prompts.</p></div>
                            <div class="docs-faq-item"><strong>Quota issues</strong><p>Switch providers or add your personal key if the developer key is exhausted.</p></div>
                        </div>
                    </section>
                    @break
                @case('roadmap')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Roadmap</div>
                                <h2>Roadmap and known issues</h2>
                                <p class="docs-section-intro">A good roadmap page sets expectations and names the rough edges honestly.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Coming soon</h4><p>Inline comments, richer reports, and more workflow automation.</p></div>
                            <div class="docs-note"><h4>Known issues</h4><p>Large diffs, noisy transcripts, and provider quirks are still being improved.</p></div>
                            <div class="docs-note"><h4>Branch audits</h4><p>Support for squash-merged and rebased branches without a merge-commit fallback.</p></div>
                            <div class="docs-note"><h4>Performance</h4><p>Better loading and rendering under heavier repos is part of the plan.</p></div>
                        </div>
                    </section>
                    @break
                @case('shortcuts')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Reference</div>
                                <h2>Keyboard shortcuts and reference</h2>
                                <p class="docs-section-intro">A compact reference helps power users move faster without guessing the UI.</p>
                            </div>
                        </div>
                        <table class="docs-table">
                            <thead><tr><th>Action</th><th>Shortcut</th></tr></thead>
                            <tbody>
                                <tr><td>Open search</td><td><kbd>Ctrl</kbd> + <kbd>K</kbd></td></tr>
                                <tr><td>Toggle theme</td><td><kbd>Ctrl</kbd> + <kbd>J</kbd></td></tr>
                                <tr><td>Send prompt</td><td><kbd>Enter</kbd></td></tr>
                                <tr><td>New line</td><td><kbd>Shift</kbd> + <kbd>Enter</kbd></td></tr>
                            </tbody>
                        </table>
                    </section>
                    @break
                @case('workflows')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Workflows</div>
                                <h2>Workflows and use cases</h2>
                                <p class="docs-section-intro">These are the common ways teams will actually use PR ai day to day.</p>
                            </div>
                        </div>
                        <div class="docs-section-grid">
                            <div class="docs-note"><h4>Pre-merge review</h4><p>Audit a PR, inspect the comments, and decide whether it is safe to merge.</p></div>
                            <div class="docs-note"><h4>Branch audit</h4><p>Compare feature work against the base branch, even after merge.</p></div>
                            <div class="docs-note"><h4>Security pass</h4><p>Focus the review on auth, access control, injection, and unsafe handling.</p></div>
                            <div class="docs-note"><h4>Documentation</h4><p>Turn the code context into a README, setup note, or spec draft.</p></div>
                        </div>
                    </section>
                    @break
                @case('security')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Security</div>
                                <h2>Security guidance</h2>
                                <p class="docs-section-intro">The docs should call out real risk and explain the main areas the auditor watches for.</p>
                            </div>
                        </div>
                        <ol class="docs-list">
                            <li>Access control and authorization checks.</li>
                            <li>Injection risks and unsafe input handling.</li>
                            <li>Misconfiguration and secret exposure.</li>
                            <li>Authentication failures and session issues.</li>
                            <li>SSRF, file handling, and external request risks.</li>
                        </ol>
                    </section>
                    @break
                @case('privacy')
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">Privacy</div>
                                <h2>Privacy and data handling</h2>
                                <p class="docs-section-intro">Code, prompts, keys, and audio should be described plainly so users know what leaves the app and why.</p>
                            </div>
                        </div>
                        <div class="docs-faq">
                            <div class="docs-faq-item"><strong>OAuth data</strong><p>Only the provider profile and repository permissions needed for the session.</p></div>
                            <div class="docs-faq-item"><strong>API keys</strong><p>Stored for use with your selected provider and used to make the requests you ask for.</p></div>
                            <div class="docs-faq-item"><strong>Voice data</strong><p>Mic audio is transcribed for voice input and review assistance.</p></div>
                        </div>
                    </section>
                    @break
                @default
                    <section class="docs-section" style="--docs-order:0">
                        <div class="docs-section-head">
                            <div>
                                <div class="docs-kicker">FAQ</div>
                                <h2>Common questions</h2>
                                <p class="docs-section-intro">Short answers for the issues people hit most often.</p>
                            </div>
                        </div>
                        <div class="docs-faq">
                            <div class="docs-faq-item"><strong>Can I audit a merged branch?</strong><p>It depends on how the branch was merged. Merge commits (standard merges) work — the app finds the merge commit and audits its diff. Squash merges and rebase-based merges do not produce a merge commit, so the audit cannot reconstruct the diff. See the Audit modes page for the full breakdown.</p></div>
                            <div class="docs-faq-item"><strong>What if the diff is empty?</strong><p>The audit is silently skipped — no error is shown, but no report is generated. This most often happens with squash-merged or rebased branches. Use a PR audit or upload the diff manually instead.</p></div>
                            <div class="docs-faq-item"><strong>Do I need a key?</strong><p>No. You can start with the shared developer key and upgrade later if you want.</p></div>
                        </div>
                    </section>
            @endswitch
        </section>
    </main>
</div>
</body>
</html>
