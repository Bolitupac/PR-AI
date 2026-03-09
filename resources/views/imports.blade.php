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
            ['title' => 'Repository sync checklist', 'time' => '1 hr ago', 'preview' => 'Branch protections and labels reviewed.'],
        ];

        $vcsProviders = [
            ['name' => 'GitHub', 'state' => 'Connected'],
            ['name' => 'GitLab', 'state' => 'Available'],
            ['name' => 'Bitbucket', 'state' => 'Available'],
            ['name' => 'Azure DevOps', 'state' => 'Available'],
        ];


    @endphp

    <div class="app-shell sidebar-expanded imports-shell" id="imports-page">
        @include('partials.sidebar')

        <main class="imports-workspace">
            <section class="imports-left" style="display: flex; flex-direction: column; min-height: 0;">
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
            </section>

            <section class="imports-panel imports-repos">
                <header class="imports-panel__head">
                    <div>
                        <h1 class="imports-page-title">Import Branches & Pull Requests</h1>
                        <p class="imports-page-subtitle">Select a repository to view branches and pull requests.</p>
                    </div>
                </header>

                <ul class="imports-repo-list" id="repo-list-container">
                    {{-- Skeletons --}}
                    @for ($i = 0; $i < 5; $i++)
                        <li class="repo-skeleton">
                            <div class="skeleton-item repo-skeleton__title"></div>
                            <div class="skeleton-item repo-skeleton__meta"></div>
                        </li>
                    @endfor
                </ul>
            </section>
        </main>
    </div>
</body>

</html>