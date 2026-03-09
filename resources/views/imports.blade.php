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
    @vite(['resources/css/imports-ui.css', 'resources/js/app.js'])
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

        $repos = [
            [
                'name' => 'Bolitupac/pr-ai',
                'author' => 'Bolitupac',
                'default_branch' => 'main',
                'branch_count' => 6,
                'open_pr_count' => 4,
                'updated_at' => '5 min ago',
                'branches' => [
                    [
                        'name' => 'feature/import-center',
                        'prs' => [
                            ['title' => 'Add import center skeleton UI', 'author' => 'Bolitupac', 'comments' => 8, 'status' => 'open', 'updated' => '3 min ago'],
                            ['title' => 'Sidebar cleanup before merge', 'author' => 'Teja', 'comments' => 3, 'status' => 'draft', 'updated' => '14 min ago'],
                        ],
                    ],
                    [
                        'name' => 'fix/chat-stream-buffer',
                        'prs' => [
                            ['title' => 'Fix first token delay in auto audit stream', 'author' => 'Nora', 'comments' => 5, 'status' => 'open', 'updated' => '21 min ago'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Bolitupac/payments-api',
                'author' => 'Bolitupac',
                'default_branch' => 'stable',
                'branch_count' => 4,
                'open_pr_count' => 2,
                'updated_at' => '22 min ago',
                'branches' => [
                    [
                        'name' => 'feature/refund-rules',
                        'prs' => [
                            ['title' => 'Add refund policy validation checks', 'author' => 'Ivy', 'comments' => 6, 'status' => 'open', 'updated' => '17 min ago'],
                        ],
                    ],
                    [
                        'name' => 'chore/log-rotation',
                        'prs' => [
                            ['title' => 'Rotate audit logs by file size', 'author' => 'Mako', 'comments' => 1, 'status' => 'draft', 'updated' => '1 hr ago'],
                        ],
                    ],
                ],
            ],
        ];
    @endphp

    <div class="app-shell sidebar-expanded imports-shell" id="imports-page">
        @include('partials.sidebar')

        <main class="imports-workspace">
            <section class="imports-left" style="display: flex; flex-direction: column; min-height: 0;">
                <article class="imports-panel" style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                    <header class="imports-panel__head" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 class="imports-panel__title" style="margin: 0;">Chat History</h2>
                        <button class="imports-history-new-btn" aria-label="New chat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
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
                        <p class="imports-page-subtitle">Static preview layout for upcoming import workflow.</p>
                    </div>
                </header>

                <ul class="imports-repo-list">
                    @foreach ($repos as $repoIndex => $repo)
                        <li class="imports-repo-item">
                            <details class="imports-repo-details">
                                <summary class="imports-repo-summary">
                                    <div class="imports-repo-main">
                                        <svg class="repo-icon" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 110-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1V9h-8c-.356 0-.694.074-1 .208V2.5a1 1 0 011-1h8zM5 12.25v3.25a.25.25 0 00.4.2l1.45-1.087a.25.25 0 01.3 0L8.6 15.7a.25.25 0 00.4-.2v-3.25a.25.25 0 00-.25-.25h-3.5a.25.25 0 00-.25.25z" fill="currentColor"></path></svg>
                                        <h4>{{ $repo['name'] }}</h4>
                                        <span class="imports-repo-badge">Public</span>
                                    </div>
                                    <div class="imports-repo-meta">
                                        <span>Updated {{ $repo['updated_at'] }}</span>
                                    </div>
                                    <span class="imports-chevron" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" width="20" height="20"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                </summary>

                                <div class="imports-repo-content">
                                    <ul class="imports-branches-list">
                                        @foreach ($repo['branches'] as $branchIndex => $branch)
                                            <li class="imports-branch-item">
                                                <details class="imports-branch-details">
                                                    <summary class="imports-branch-summary">
                                                        <div class="imports-branch-main">
                                                            <svg class="branch-icon" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M11.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122V6A2.5 2.5 0 0110 8.5H6a1 1 0 00-1 1v1.128a2.251 2.251 0 11-1.5 0V5.372a2.25 2.25 0 111.5 0v1.836A2.492 2.492 0 016 7h4a1 1 0 001-1v-.628A2.25 2.25 0 019.5 3.25zM4.25 12a.75.75 0 100 1.5.75.75 0 000-1.5zM3.5 3.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0z" fill="currentColor"></path></svg>
                                                            <strong>{{ $branch['name'] }}</strong>
                                                        </div>
                                                        <div class="imports-branch-meta">
                                                            <span class="imports-tag">{{ count($branch['prs']) }} Open PRs</span>
                                                        </div>
                                                        <span class="imports-chevron" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        </span>
                                                    </summary>

                                                    <div class="imports-branch-content">
                                                        <ul class="imports-pr-list">
                                                            @foreach ($branch['prs'] as $pr)
                                                                <li class="imports-pr-item">
                                                                    <div class="imports-pr-main">
                                                                        <svg class="pr-icon" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" style="color: {{ $pr['status'] === 'draft' ? '#5d6475' : '#1a7f37' }}"><path fill-rule="evenodd" d="M7.177 3.073L9.573.677A.25.25 0 0110 .854v4.792a.25.25 0 01-.427.177L7.177 3.427a.25.25 0 010-.354zM3.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122v5.256a2.251 2.251 0 11-1.5 0V5.372A2.25 2.25 0 011.5 3.25zM11 2.5h-1V4h1a1 1 0 011 1v5.628a2.251 2.251 0 101.5 0V5A2.5 2.5 0 0011 2.5zm1 10.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0zM3.75 12a.75.75 0 100 1.5.75.75 0 000-1.5z" fill="currentColor"></path></svg>
                                                                        <h5 class="imports-pr-title">{{ $pr['title'] }}</h5>
                                                                    </div>
                                                                    <div class="imports-pr-meta">
                                                                        <span>#{{ rand(100, 999) }} opened {{ $pr['updated'] }} by {{ $pr['author'] }}</span>
                                                                        <span>
                                                                            <svg viewBox="0 0 16 16" width="16" height="16"><path fill-rule="evenodd" d="M2.75 2.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h2a.75.75 0 01.75.75v2.19l2.72-2.72a.75.75 0 01.53-.22h4.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25H2.75zM1 2.75C1 1.784 1.784 1 2.75 1h10.5c.966 0 1.75.784 1.75 1.75v7.5A1.75 1.75 0 0113.25 12H9.06l-2.573 2.573A1.457 1.457 0 014 13.543V12H2.75A1.75 1.75 0 011 10.25v-7.5z" fill="currentColor"></path></svg>
                                                                            {{ $pr['comments'] }}
                                                                        </span>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </details>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </section>
        </main>
    </div>
</body>

</html>

