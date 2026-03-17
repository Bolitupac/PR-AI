<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nabla&family=Science+Gothic:wght@400;600;700&display=swap"
        rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Nabla&family=Science+Gothic:wght@400;600;700&display=swap"
            rel="stylesheet">
    </noscript>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo 512 transp bg white color svg.svg') }}">
    <title>Git PULL Assistant | Imports</title>
    @vite(['resources/css/imports-ui.css', 'resources/css/imports/skeleton.css', 'resources/js/app.js'])
</head>

<body>
    @php
    $chatHistory = [
    ['title' => 'Audit for billing service PR', 'time' => '2 min ago', 'preview' => 'Risk: medium, 3 change requests.'],
    ['title' => 'Review edge API branch', 'time' => '11 min ago', 'preview' => 'Null checks missing in 2 files.'],
    ['title' => 'Frontend lint cleanup', 'time' => '45 min ago', 'preview' => 'Low-risk formatting updates only.'],
    ['title' => 'Repository sync checklist', 'time' => '1 hr ago', 'preview' => 'Branch protections and labels
    reviewed.'],
    ['title' => 'Testing out the ai system for the user', 'time' => '59 min ago', 'preview' => 'Risk: medium, 3 change
    requests.'],
    ];

    $recentCommits = [
    ['hash' => '7f3a2b1', 'message' => 'feat: add user authentication', 'author' => 'bolitupac', 'time' => '10m ago'],
    ['hash' => 'ac8d4e2', 'message' => 'fix: resolver bug in AI service', 'author' => 'carlos', 'time' => '1h ago'],
    ['hash' => '9b2c1f5', 'message' => 'docs: update readme with new API', 'author' => 'bolitupac', 'time' => '3h ago'],
    ['hash' => 'd5e6f7g', 'message' => 'refactor: simplify database queries', 'author' => 'maria', 'time' => '5h ago'],
    ['hash' => 'e1f2g3h', 'message' => 'chore: upgrade dependencies', 'author' => 'bolitupac', 'time' => '6h ago'],
    ['hash' => 'f3g4h5i', 'message' => 'style: improve mobile responsive layout', 'author' => 'carlos', 'time' => '1d ago'],
    ['hash' => 'g5h6i7j', 'message' => 'test: add unit tests for workspace module', 'author' => 'maria', 'time' => '1d ago'],
    ['hash' => 'h7i8j9k', 'message' => 'feat: implement real-time notifications', 'author' => 'bolitupac', 'time' => '2d ago'],
    ['hash' => 'i9j0k1l', 'message' => 'fix: memory leak in stream processor', 'author' => 'carlos', 'time' => '2d ago'],
    ['hash' => 'j1k2l3m', 'message' => 'refactor: cleanup unused assets', 'author' => 'maria', 'time' => '3d ago'],
    ['hash' => 'k2l3m4n', 'message' => 'docs: add architecture diagram', 'author' => 'bolitupac', 'time' => '3d ago'],
    ['hash' => 'l3m4n5o', 'message' => 'feat: support multi-repository sync', 'author' => 'carlos', 'time' => '4d ago'],
    ['hash' => 'm4n5o6p', 'message' => 'fix: escape html in chat logs', 'author' => 'maria', 'time' => '4d ago'],
    ['hash' => 'n5o6p7q', 'message' => 'chore: add linting rules for blade', 'author' => 'bolitupac', 'time' => '5d ago'],
    ];

    $vcsProviders = [
    ['name' => 'GitHub', 'state' => 'Connected'],
    ['name' => 'GitLab', 'state' => 'Available'],
    ['name' => 'Bitbucket', 'state' => 'Available'],
    ['name' => 'Azure DevOps', 'state' => 'Available'],
    ];


    @endphp

    <div class="app-shell sidebar-expanded imports-shell" id="imports-page"
        data-repos-url="{{ route('github.repos') }}"
        data-branches-url="{{ route('github.branches') }}"
        data-pulls-url="{{ route('github.pulls') }}"
        data-metadata-url="{{ route('github.metadata') }}">
        @include('partials.sidebar')

        <main class="imports-workspace">
            <section class="imports-left" style="display: flex; flex-direction: column; min-height: 0; gap: 8px;">
                <article class="imports-panel" style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                    <header class="imports-panel__head"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 class="imports-panel__title" style="margin: 0;">Chat History</h2>
                        <button class="imports-history-new-btn" aria-label="New chat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16"
                                height="16">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </button>
                    </header>
                    <ul class="imports-history-list" style="overflow-y: auto;">
                        @foreach ($chatHistory as $chat)
                        <li class="imports-history-item">
                            <div class="imports-history-flex">
                                <h4 class="imports-history-title">{{ $chat['title'] }}</h4>
                                <div class="imports-history-meta">{{ $chat['time'] }}</div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </article>

                <article class="imports-panel imports-panel--commits"
                    style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                    <header class="imports-panel__head">
                        <h2 class="imports-panel__title" style="margin: 0;">Recent Commits</h2>
                    </header>
                    <ul class="imports-history-list imports-commit-list" style="overflow-y: auto;">
                        @foreach ($recentCommits as $commit)
                            <li class="imports-history-item imports-commit-item">
                                <div class="imports-commit-flex">
                                    <div class="imports-commit-main">
                                        <code class="imports-commit-hash">{{ $commit['hash'] }}</code>
                                        <span class="imports-commit-msg">{{ $commit['message'] }}</span>
                                    </div>
                                    <div class="imports-commit-meta">
                                        <span class="imports-commit-author">{{ $commit['author'] }}</span>
                                        <span class="imports-commit-time">{{ $commit['time'] }}</span>
                                    </div>
                                    <button class="imports-commit-import-btn" aria-label="Import commit">
                                        Import
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </article>
            </section>

            <section class="imports-panel imports-repos">
                <header class="imports-panel__head">
                    <div>
                        <h1 class="imports-page-title">Import Branches & Pull Requests</h1>
                        <p class="imports-page-subtitle">Select a repository to view branches and pull requests.</p>
                    </div>
                    <div class="imports-import-status" id="imports-import-status" aria-live="polite"></div>
                </header>

                <ul class="imports-repo-list" id="repo-list-container">
                    {{-- Skeletons --}}
                    @for ($i = 0; $i < 5; $i++) <li class="repo-skeleton">
                        <div class="skeleton-item repo-skeleton__title"></div>
                        <div class="skeleton-item repo-skeleton__meta"></div>
                        </li>
                        @endfor

                        <li id="load-more-wrap" class="imports-load-more-item" style="display:none;">
                            <button id="load-more-btn" class="imports-load-more-btn" type="button">
                                Load more repositories
                            </button>
                            <p id="repo-count-label" class="imports-load-more-count"></p>
                        </li>
                </ul>
            </section>
        </main>
    </div>
</body>

</html>