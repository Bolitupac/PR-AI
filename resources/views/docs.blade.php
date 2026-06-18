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
        'quickstart' => ['label' => 'Quick start', 'icon' => 'M12 2 4 6v12l8 4 8-4V6l-8-4Zm0 4.2L16.9 8 12 10.8 7.1 8 12 6.2ZM6 9.4l5 2.8v5.8L6 15.2V9.4Zm12 5.8-5 2.8v-5.8l5-2.8v5.8Z', 'class' => 'docs-nav-link--quickstart'],
        'imports' => ['label' => 'Imports', 'icon' => 'M12 2.5 20 6v3H4V6l8-3.5ZM5 10h14v9H5v-9Zm3 2v5h2v-5H8Zm4 0v5h2v-5h-2Z', 'class' => 'docs-nav-link--imports'],
        'audits' => ['label' => 'Audit modes', 'icon' => 'M12 2 2 6l10 4 8-3.2V16h2V6L12 2Zm-7 8v4l7 3 7-3v-4l-7 3-7-3Z', 'class' => 'docs-nav-link--audits'],
        'docgen' => ['label' => 'DocGen', 'icon' => 'M6 4h9l3 3v13H6V4Zm8 1.5V8h2.5L14 5.5ZM8 11h7v1.5H8V11Zm0 3h7v1.5H8V14Z', 'class' => 'docs-nav-link--docgen'],
        'voice' => ['label' => 'Voice input', 'icon' => 'M12 14a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v5a3 3 0 0 0 3 3Zm-5-3a5 5 0 0 0 10 0h2a7 7 0 0 1-6 6.93V21h-2v-3.07A7 7 0 0 1 5 11h2Z', 'class' => 'docs-nav-link--voice'],
        'keys' => ['label' => 'API keys', 'icon' => 'M14 3a5 5 0 0 0-3.6 8.5L4 18v2h2v-1h2v-2h2v-2h2l1.1-1.1A5 5 0 1 0 14 3Zm0 2a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z', 'class' => 'docs-nav-link--keys'],
        'security' => ['label' => 'Security', 'icon' => 'M12 2 4 5v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V5l-8-3Zm0 6a2 2 0 0 1 2 2v2h1v5H9v-5h1v-2a2 2 0 0 1 2-2Z', 'class' => 'docs-nav-link--security'],
        'privacy' => ['label' => 'Privacy', 'icon' => 'M12 2a6 6 0 0 0-6 6v3H5a2 2 0 0 0-2 2v5h16v-5a2 2 0 0 0-2-2h-1V8a6 6 0 0 0-6-6Zm-3 9V8a3 3 0 1 1 6 0v3H9Z', 'class' => 'docs-nav-link--privacy'],
        'faq' => ['label' => 'FAQ', 'icon' => 'M12 18h.01M12 14a4 4 0 1 0-4-4h2a2 2 0 1 1 2 2v2Z', 'class' => 'docs-nav-link--faq'],
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
            <span class="docs-nav-label">Pages</span>
            @foreach ($pages as $slug => $page)
                <a href="{{ route('docs.index', ['page' => $slug]) }}" class="docs-nav-link {{ $page['class'] }} {{ $currentPage === $slug ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $page['icon'] }}"></path></svg>
                    <span>{{ $page['label'] }}</span>
                </a>
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

        <section class="docs-page">
            @if ($currentPage === 'overview')
                <div class="docs-hero">
                    <div class="docs-kicker">PR ai docs</div>
                    <h1>Everything you need to import, audit, and secure your code</h1>
                    <p>Use the left nav to open a specific docs page. This layout is intentionally closer to the app: flatter panels, tighter borders, and the same blue tone as the auditor workspace.</p>
                </div>
            @endif

            @if ($currentPage === 'overview')
                <div class="docs-grid">
                    <a class="docs-card" href="{{ route('docs.index', ['page' => 'imports']) }}">
                        <h3>Imports</h3><p>Branches, PRs, commits, and manual diffs.</p>
                    </a>
                    <a class="docs-card" href="{{ route('docs.index', ['page' => 'security']) }}">
                        <h3>Security</h3><p>VAPT and OWASP guidance in one place.</p>
                    </a>
                    <a class="docs-card" href="{{ route('docs.index', ['page' => 'privacy']) }}">
                        <h3>Privacy</h3><p>How code, keys, audio, and sessions are handled.</p>
                    </a>
                    <a class="docs-card" href="{{ route('docs.index', ['page' => 'faq']) }}">
                        <h3>FAQ</h3><p>Short answers for common questions.</p>
                    </a>
                </div>
            @elseif ($currentPage === 'quickstart')
                <section class="docs-section">
                    <div class="docs-section-head">
                        <div>
                            <div class="docs-kicker">Getting started</div>
                            <h2>Quick start</h2>
                            <p class="docs-section-intro">Get from sign-in to an audit fast.</p>
                        </div>
                    </div>
                    <div class="docs-section-grid">
                        <div class="docs-note"><h4>1. Sign in</h4><p>Use GitHub or GitLab to unlock Imports and the Auditor.</p></div>
                        <div class="docs-note"><h4>2. Open Imports</h4><p>Browse repositories, branches, pull requests, and recent commits.</p></div>
                        <div class="docs-note"><h4>3. Pick a source</h4><p>Select a branch, PR, commit, uploaded diff, or pasted code.</p></div>
                        <div class="docs-note"><h4>4. Audit</h4><p>Review the diff and ask follow-up questions in the same workspace.</p></div>
                    </div>
                </section>
            @elseif ($currentPage === 'imports')
                <section class="docs-section">
                    <div class="docs-section-head">
                        <div>
                            <div class="docs-kicker">Imports</div>
                            <h2>Importing code and branch audits</h2>
                            <p class="docs-section-intro">Branch audits compare a branch against its base branch. If a branch was merged or deleted, the compare may be empty, and that is okay.</p>
                        </div>
                    </div>
                    <table class="docs-table">
                        <thead><tr><th>Source</th><th>Loads</th><th>Use it for</th></tr></thead>
                        <tbody>
                            <tr><td>Pull request</td><td>Diff, metadata, comments</td><td>Pre-merge review</td></tr>
                            <tr><td>Branch</td><td>Branch-to-base compare</td><td>Feature branch review</td></tr>
                            <tr><td>Commit</td><td>Single commit diff</td><td>Historical review</td></tr>
                            <tr><td>Manual diff</td><td>Uploaded or pasted text</td><td>Ad hoc audits</td></tr>
                        </tbody>
                    </table>
                </section>
            @elseif ($currentPage === 'audits')
                <section class="docs-section">
                    <div class="docs-section-head">
                        <div>
                            <div class="docs-kicker">Audit modes</div>
                            <h2>How audits work</h2>
                            <p class="docs-section-intro">Each mode frames the report differently.</p>
                        </div>
                    </div>
                    <div class="docs-section-grid">
                        <div class="docs-note"><h4>Pull Request</h4><p>Full PR context with comments and metadata.</p></div>
                        <div class="docs-note"><h4>Branch</h4><p>Branch against base, even when merged or rebased.</p></div>
                        <div class="docs-note"><h4>Commit</h4><p>Single commit change set.</p></div>
                        <div class="docs-note"><h4>Merge conflicts</h4><p>Conflict data and resolution guidance.</p></div>
                    </div>
                </section>
            @elseif ($currentPage === 'docgen')
                <section class="docs-section">
                    <div class="docs-section-head">
                        <div>
                            <div class="docs-kicker">DocGen</div>
                            <h2>Document generation</h2>
                            <p class="docs-section-intro">Turn context into README-style docs, specs, or planning notes.</p>
                        </div>
                    </div>
                    <div class="docs-section-grid">
                        <div class="docs-note"><p>DocGen can ask clarifying questions before writing the final doc.</p></div>
                        <div class="docs-note"><p>Preview output inside the app before exporting it.</p></div>
                    </div>
                </section>
            @elseif ($currentPage === 'voice')
                <section class="docs-section">
                    <div class="docs-section-head">
                        <div>
                            <div class="docs-kicker">Voice input</div>
                            <h2>Speak to PR ai</h2>
                            <p class="docs-section-intro">Use voice for quick prompts and follow-up questions.</p>
                        </div>
                    </div>
                    <div class="docs-section-grid">
                        <div class="docs-note"><p>Floating mic access stays available as you scroll.</p></div>
                        <div class="docs-note"><p>Great for long audits or hands-free follow-ups.</p></div>
                    </div>
                </section>
            @elseif ($currentPage === 'keys')
                <section class="docs-section">
                    <div class="docs-section-head">
                        <div>
                            <div class="docs-kicker">API keys</div>
                            <h2>AI provider modes</h2>
                            <p class="docs-section-intro">Choose between the shared developer key and your own personal key.</p>
                        </div>
                    </div>
                    <div class="docs-section-grid">
                        <div class="docs-note"><p>Developer Key is the fastest start.</p></div>
                        <div class="docs-note"><p>Personal Key gives you your own billing and control.</p></div>
                    </div>
                </section>
            @elseif ($currentPage === 'security')
                <section class="docs-section">
                    <div class="docs-section-head">
                        <div>
                            <div class="docs-kicker">Security</div>
                            <h2>VAPT and OWASP</h2>
                            <p class="docs-section-intro">The audit should call out real risk, not just labels.</p>
                        </div>
                    </div>
                    <ol class="docs-list">
                        <li>A01 access control</li>
                        <li>A03 injection</li>
                        <li>A05 misconfiguration</li>
                        <li>A07 auth failures</li>
                        <li>A10 SSRF</li>
                    </ol>
                </section>
            @elseif ($currentPage === 'privacy')
                <section class="docs-section">
                    <div class="docs-section-head">
                        <div>
                            <div class="docs-kicker">Privacy</div>
                            <h2>Data handling</h2>
                            <p class="docs-section-intro">Short version: code and prompts can be sent to the chosen AI provider, and auth/session data is used to keep the app working.</p>
                        </div>
                    </div>
                    <div class="docs-faq">
                        <div class="docs-faq-item"><strong>OAuth</strong><p>Only the profile data authorized by GitHub or GitLab.</p></div>
                        <div class="docs-faq-item"><strong>Keys</strong><p>Encrypted at rest and used for your own requests.</p></div>
                        <div class="docs-faq-item"><strong>Audio</strong><p>Transcribed for voice input.</p></div>
                    </div>
                </section>
            @else
                <section class="docs-section">
                    <div class="docs-section-head">
                        <div>
                            <div class="docs-kicker">FAQ</div>
                            <h2>Common questions</h2>
                        </div>
                    </div>
                    <div class="docs-faq">
                        <div class="docs-faq-item"><strong>Can I audit a merged branch?</strong><p>Yes, if the compare still exists or can be reconstructed.</p></div>
                        <div class="docs-faq-item"><strong>What if the diff is empty?</strong><p>The page should stay usable, even without a rendered diff.</p></div>
                        <div class="docs-faq-item"><strong>Need a key?</strong><p>No, you can start with the shared developer key.</p></div>
                    </div>
                </section>
            @endif
        </section>
    </main>
</div>
</body>
</html>
