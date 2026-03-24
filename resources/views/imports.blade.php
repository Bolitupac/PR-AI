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
            <section class="imports-left">
                <article class="imports-panel imports-side-panel">
                    <header class="imports-panel__head">
                        <h2 class="imports-panel__title" style="margin: 0;">Recent Pull Requests</h2>
                    </header>
                    <ul class="imports-history-list imports-activity-list" id="recent-pull-requests-list">
                        <li class="imports-history-item" style="color: var(--text-soft); font-size: 12px;">
                            Loading recent pull requests...
                        </li>
                    </ul>
                </article>

                <article class="imports-panel imports-side-panel imports-panel--commits">
                    <header class="imports-panel__head">
                        <h2 class="imports-panel__title" style="margin: 0;">Recent Commits</h2>
                    </header>
                    <ul class="imports-history-list imports-activity-list imports-commit-list">
                        @forelse ($recentCommits ?? [] as $commit)
                            <li class="imports-history-item imports-activity-item imports-commit-item">
                                <div class="imports-activity-flex imports-commit-flex">
                                    <div class="imports-activity-main imports-commit-main">
                                        <code class="imports-activity-badge imports-commit-hash">{{ $commit['hash'] ?? '—' }}</code>
                                        <span class="imports-activity-title imports-commit-msg">{{ $commit['message'] ?? '' }}</span>
                                    </div>
                                    <div class="imports-activity-meta imports-commit-meta">
                                        <span class="imports-commit-author">{{ $commit['author'] ?? '' }}</span>
                                        <span class="imports-commit-repo">{{ $commit['repo'] ?? '' }}</span>
                                        <span class="imports-activity-time imports-commit-time">{{ $commit['time'] ?? '' }}</span>
                                    </div>
                                    <button class="imports-activity-action-btn imports-commit-import-btn" type="button"
                                        data-commit="{{ $commit['hash'] ?? '' }}"
                                        data-title="{{ $commit['message'] ?? '' }}"
                                        data-repo="{{ $commit['repo'] ?? '' }}"
                                        aria-label="Audit commit">
                                        Audit
                                    </button>
                                </div>
                            </li>
                        @empty
                            <li class="imports-history-item" style="color: var(--text-soft); font-size: 12px;">
                                {{ $recentCommitsUnavailableReason ?? 'No commits available.' }}
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
