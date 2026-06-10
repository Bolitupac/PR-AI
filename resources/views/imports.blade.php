<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.meta', [
        'metaTitle' => 'PR ai | Imports — Browse Repos, PRs & Branches',
        'metaDescription' => 'Browse GitHub and GitLab repositories, pull requests, branches, commits, and merge conflicts. Import code into the PR ai Auditor for AI-powered VAPT + OWASP Top 10 security reviews.',
        'metaType' => 'website',
        'metaRobots' => 'noindex, nofollow',
    ])
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
    @include('partials.mobile-hamburger')
    @php
        $vcsClientProviders = collect($vcsProviders ?? [])->mapWithKeys(function ($provider) {
            return [
                $provider['key'] => [
                    'label' => $provider['name'],
                    'connected' => (bool) ($provider['connected'] ?? false),
                    'connect_url' => $provider['connect_url'] ?? '#settings-vcs',
                ],
            ];
        })->all();
        $hasConnectedProvider = collect($vcsProviders ?? [])->contains(fn ($provider) => !empty($provider['connected']));
    @endphp
    <div class="app-shell sidebar-expanded imports-shell" id="imports-page"
        data-vcs-api-base="{{ url('/api/vcs') }}"
        data-default-provider="{{ $defaultVcsProviderKey ?? 'github' }}"
        data-vcs-providers='@json($vcsClientProviders)'>
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

                <article class="imports-panel imports-side-panel imports-panel--history">
                    <header class="imports-panel__head">
                        <h2 class="imports-panel__title" style="margin: 0;">Chat History</h2>
                    </header>
                    <ul class="imports-history-list imports-activity-list" id="imports-chat-history-list">
                        <li class="imports-history-item" style="color: var(--text-soft); font-size: 12px;">
                            Loading history...
                        </li>
                    </ul>
                </article>
            </section>

            <section class="imports-panel imports-repos">
                <header class="imports-panel__head">
                    <div>
                        <h1 class="imports-page-title">Import Branches & Pull Requests</h1>
                        <p class="imports-page-subtitle">Select a repository to view branches and pull requests.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <label style="display:flex; align-items:center; gap:8px; color:var(--text-soft); font-size:12px;">
                            <span>Provider</span>
                            <select id="imports-provider-select" class="chat-model-select" aria-label="Select provider">
                                @foreach (($vcsProviders ?? []) as $provider)
                                    @php $isComingSoon = in_array($provider['key'] ?? '', ['bitbucket', 'azure']); @endphp
                                    <option value="{{ $provider['key'] }}" @selected(($defaultVcsProviderKey ?? 'github') === $provider['key']) @disabled($isComingSoon)>
                                        {{ $provider['name'] }}{{ $isComingSoon ? ' — Coming Soon' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <div class="imports-import-status" id="imports-import-status" aria-live="polite"></div>
                    </div>
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

    @include('partials.settings-modal')
    @include('partials.mobile-redirect')

    {{-- Tutorial data for first-time users --}}
    @auth
        @unless(auth()->user()->tutorial_completed_at)
            <script>
                window.__PR_AI_TUTORIAL__ = { show: true, page: 'imports' };
            </script>
        @endunless
    @endauth
</body>

</html>
