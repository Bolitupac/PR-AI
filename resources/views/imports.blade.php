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
                        @forelse ($chatHistory ?? [] as $chat)
                            <li class="imports-history-item">
                                <div class="imports-history-flex">
                                    <h4 class="imports-history-title">{{ $chat['title'] ?? 'Untitled' }}</h4>
                                    <div class="imports-history-meta">{{ $chat['time'] ?? '' }}</div>
                                </div>
                            </li>
                        @empty
                            <li class="imports-history-item" style="color: var(--text-soft); font-size: 12px;">
                                No history yet.
                            </li>
                        @endforelse
                    </ul>
                </article>

                <article class="imports-panel imports-panel--commits"
                    style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                    <header class="imports-panel__head">
                        <h2 class="imports-panel__title" style="margin: 0;">Recent Commits</h2>
                    </header>
                    <ul class="imports-history-list imports-commit-list" style="overflow-y: auto;">
                        @forelse ($recentCommits ?? [] as $commit)
                            <li class="imports-history-item imports-commit-item">
                                <div class="imports-commit-flex">
                                    <div class="imports-commit-main">
                                        <code class="imports-commit-hash">{{ $commit['hash'] ?? '—' }}</code>
                                        <span class="imports-commit-msg">{{ $commit['message'] ?? '' }}</span>
                                    </div>
                                    <div class="imports-commit-meta">
                                        <span class="imports-commit-author">{{ $commit['author'] ?? '' }}</span>
                                        <span class="imports-commit-repo">{{ $commit['repo'] ?? '' }}</span>
                                        <span class="imports-commit-time">{{ $commit['time'] ?? '' }}</span>
                                    </div>
                                    <button class="imports-commit-import-btn" aria-label="Audit commit">
                                        Audit
                                    </button>
                                </div>
                            </li>
                        @empty
                            <li class="imports-history-item" style="color: var(--text-soft); font-size: 12px;">
                                No commits available.
                            </li>
                        @endforelse
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
