<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GitHubApiService
{
    // Creates an authenticated GitHub API client from encrypted token.
    private function client(string $encryptedToken)
    {
        $token = Crypt::decryptString($encryptedToken);

        return Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json']);
    }

    // Fetches current user's repositories from GitHub.
    public function getRepos(string $encryptedToken): array
    {
        $response = $this->client($encryptedToken)->get('https://api.github.com/user/repos', [
            'per_page' => 100,
            'sort' => 'updated',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $repos = collect($response->json())
            ->map(fn($repo) => [
                'name' => $repo['name'] ?? '',
                'full_name' => $repo['full_name'] ?? '',
                'private' => $repo['private'] ?? false,
                'language' => $repo['language'] ?? 'Unknown',
                'updated_at' => $repo['updated_at'] ?? null,
                'open_issues_count' => $repo['open_issues_count'] ?? 0,
                'default_branch' => $repo['default_branch'] ?? 'main',
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $repos];
    }

    // Fetches branches for a selected repository.
    public function getBranches(string $encryptedToken, string $repo): array
    {
        $response = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/branches", [
            'per_page' => 100,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $branches = collect($response->json())
            ->map(fn($branch) => [
                'name' => $branch['name'] ?? '',
                'protected' => $branch['protected'] ?? false,
                // The branches list endpoint includes the tip commit object
                'updated_at' => $branch['commit']['commit']['author']['date'] ?? null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $branches];
    }

    // Fetches open pull requests for a selected repository.
    public function getPullRequests(string $encryptedToken, string $repo): array
    {
        $client = $this->client($encryptedToken);

        // Fetch Pull Requests (for state, draft, refs)
        $pullsResponse = $client->get("https://api.github.com/repos/{$repo}/pulls", [
            'state' => 'open',
            'per_page' => 100,
            'sort' => 'updated',
            'direction' => 'desc',
        ]);

        if ($pullsResponse->failed()) {
            return ['ok' => false, 'status' => $pullsResponse->status(), 'data' => []];
        }

        // Fetch Issues (to get comment counts, as pulls list doesn't have them)
        $issuesResponse = $client->get("https://api.github.com/repos/{$repo}/issues", [
            'state' => 'open',
            'per_page' => 100,
        ]);

        $commentCounts = [];
        if ($issuesResponse->successful()) {
            foreach ($issuesResponse->json() as $issue) {
                if (isset($issue['number'])) {
                    $commentCounts[$issue['number']] = $issue['comments'] ?? 0;
                }
            }
        }

        $pulls = collect($pullsResponse->json())
            ->map(fn($pr) => [
                'number' => $pr['number'] ?? null,
                'title' => $pr['title'] ?? '',
                'state' => $pr['state'] ?? '',
                'draft' => $pr['draft'] ?? false,
                'html_url' => $pr['html_url'] ?? '',
                'updated_at' => $pr['updated_at'] ?? null,
                'author' => $pr['user']['login'] ?? '',
                'comments' => $commentCounts[$pr['number']] ?? 0,
                'review_comments' => $pr['review_comments'] ?? 0, // List API might have this sometimes or not
                'head_ref' => $pr['head']['ref'] ?? '',
                'base_ref' => $pr['base']['ref'] ?? '',
                'labels' => collect($pr['labels'] ?? [])->map(fn($l) => [
                    'name' => $l['name'],
                    'color' => $l['color']
                ])->all(),
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $pulls];
    }

    // Fetches the most recently updated pull requests for a repository.
    public function getRecentPullRequests(string $encryptedToken, string $repo, int $limit = 3): array
    {
        $limit = max(1, min(20, $limit));

        $response = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/pulls", [
            'state' => 'all',
            'per_page' => $limit,
            'sort' => 'updated',
            'direction' => 'desc',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $pulls = collect($response->json())
            ->map(fn($pr) => [
                'number' => $pr['number'] ?? null,
                'title' => $pr['title'] ?? '',
                'state' => $pr['state'] ?? '',
                'draft' => $pr['draft'] ?? false,
                'merged_at' => $pr['merged_at'] ?? null,
                'updated_at' => $pr['updated_at'] ?? null,
                'author' => $pr['user']['login'] ?? '',
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $pulls];
    }

    public function getRecentAccountPullRequests(string $encryptedToken, string $username, int $limit = 10): array
    {
        $limit = max(1, min(20, $limit));

        $response = $this->client($encryptedToken)->get('https://api.github.com/search/issues', [
            'q' => sprintf('is:pr author:%s', $username),
            'sort' => 'updated',
            'order' => 'desc',
            'per_page' => $limit,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $items = collect($response->json('items') ?? []);

        $pulls = $items->map(function (array $item) {
            $repo = $this->extractRepoFullName($item['repository_url'] ?? '');

            return [
                'repo' => $repo,
                'number' => $item['number'] ?? null,
                'title' => $item['title'] ?? '',
                'state' => $item['state'] ?? '',
                'updated_at' => $item['updated_at'] ?? null,
                'author' => $item['user']['login'] ?? '',
            ];
        })
            ->filter(fn (array $pr) => !empty($pr['repo']) && !empty($pr['number']))
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $pulls];
    }

    public function getRecentAccountCommits(string $encryptedToken, string $username, int $limit = 15): array
    {
        $limit = max(1, min(30, $limit));

        $response = $this->client($encryptedToken)->get("https://api.github.com/users/{$username}/events", [
            'per_page' => 100,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $events = collect($response->json() ?? []);
        $commits = [];

        foreach ($events as $event) {
            if (($event['type'] ?? '') === 'PushEvent') {
                $repoName = $event['repo']['name'] ?? '';
                $pushCommits = $event['payload']['commits'] ?? [];
                
                foreach (array_reverse($pushCommits) as $commit) {
                    $commits[] = [
                        'repo' => $repoName,
                        'hash' => (string) ($commit['sha'] ?? ''),
                        'message' => $commit['message'] ?? '',
                        'author' => $commit['author']['name'] ?? $username,
                        'time' => \Illuminate\Support\Carbon::parse($event['created_at'])->diffForHumans(),
                    ];

                    if (count($commits) >= $limit) {
                        break 2;
                    }
                }
            }
        }

        return ['ok' => true, 'status' => 200, 'data' => $commits];
    }

    private function extractRepoFullName(string $repositoryUrl): string
    {
        if ($repositoryUrl === '') {
            return '';
        }

        $prefix = 'https://api.github.com/repos/';
        if (!Str::startsWith($repositoryUrl, $prefix)) {
            return '';
        }

        return trim(Str::after($repositoryUrl, $prefix), '/');
    }

    // Fetches unified diff text for a pull request.
    public function getPullDiff(string $encryptedToken, string $repo, string $prNumber): array
    {
        $token = Crypt::decryptString($encryptedToken);
        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github.v3.diff'])
            ->get("https://api.github.com/repos/{$repo}/pulls/{$prNumber}");

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => ''];
        }

        return ['ok' => true, 'status' => 200, 'data' => $response->body()];
    }

    // Fetches pull request details used for AI context.
    public function getPullDetails(string $encryptedToken, string $repo, string $prNumber): array
    {
        $response = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/pulls/{$prNumber}");

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $data = $response->json();

        return [
            'ok' => true,
            'status' => 200,
            'data' => [
                'number' => $data['number'] ?? null,
                'title' => $data['title'] ?? '',
                'body' => $data['body'] ?? '',
                'state' => $data['state'] ?? '',
                'draft' => $data['draft'] ?? false,
                'merged_at' => $data['merged_at'] ?? null,
                'author' => $data['user']['login'] ?? '',
                'changed_files' => $data['changed_files'] ?? 0,
                'additions' => $data['additions'] ?? 0,
                'deletions' => $data['deletions'] ?? 0,
                'comments' => $data['comments'] ?? 0,
                'review_comments' => $data['review_comments'] ?? 0,
                'updated_at' => $data['updated_at'] ?? null,
            ],
        ];
    }

    // Fetches issue comments for a pull request.
    public function getPullIssueComments(string $encryptedToken, string $repo, string $prNumber): array
    {
        $response = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/issues/{$prNumber}/comments", [
            'per_page' => 100,
            'sort' => 'updated',
            'direction' => 'desc',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $comments = collect($response->json())
            ->map(fn($comment) => [
                'author' => $comment['user']['login'] ?? '',
                'body' => $comment['body'] ?? '',
                'updated_at' => $comment['updated_at'] ?? null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $comments];
    }

    // Fetches code review comments for a pull request.
    public function getPullReviewComments(string $encryptedToken, string $repo, string $prNumber): array
    {
        $response = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/pulls/{$prNumber}/comments", [
            'per_page' => 100,
            'sort' => 'updated',
            'direction' => 'desc',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $comments = collect($response->json())
            ->map(fn($comment) => [
                'author' => $comment['user']['login'] ?? '',
                'path' => $comment['path'] ?? '',
                'line' => $comment['line'] ?? null,
                'original_line' => $comment['original_line'] ?? null,
                'side' => $comment['side'] ?? null,
                'original_side' => $comment['original_side'] ?? null,
                'body' => $comment['body'] ?? '',
                'updated_at' => $comment['updated_at'] ?? null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $comments];
    }

    /**
     * Fetches unified diff text comparing two branches.
     */
    public function getBranchDiff(string $encryptedToken, string $repo, string $base, string $head): array
    {
        $token = Crypt::decryptString($encryptedToken);
        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github.v3.diff'])
            ->get("https://api.github.com/repos/{$repo}/compare/{$base}...{$head}");

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => ''];
        }

        return ['ok' => true, 'status' => 200, 'data' => $response->body()];
    }

    /**
     * Fetches unified diff text for a single commit.
     */
    public function getCommitDiff(string $encryptedToken, string $repo, string $commit): array
    {
        $response = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/commits/{$commit}");

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => ''];
        }

        $entries = collect($response->json('files') ?? [])
            ->map(fn (array $file) => [
                'old_path' => $file['filename'] ?? '',
                'new_path' => $file['filename'] ?? '',
                'diff' => $file['patch'] ?? '',
                'new_file' => ($file['status'] ?? '') === 'added',
                'deleted_file' => ($file['status'] ?? '') === 'removed',
            ])
            ->all();

        $diff = app(\App\Services\Vcs\UnifiedDiffBuilder::class)->fromEntries($entries);

        return ['ok' => true, 'status' => 200, 'data' => $diff];
    }

    /**
     * @return array{ok:bool,status:int,data:array<int,array<string,mixed>>}
     */
    public function getRecentMergeConflicts(string $encryptedToken, int $limit = 10): array
    {
        $limit = max(1, min(15, $limit));
        $reposResult = $this->getRepos($encryptedToken);
        if (!$reposResult['ok']) {
            return $reposResult;
        }

        $conflicts = [];
        foreach (array_slice($reposResult['data'], 0, 12) as $repo) {
            $fullName = (string) ($repo['full_name'] ?? '');
            if ($fullName === '') {
                continue;
            }

            $response = $this->client($encryptedToken)->get("https://api.github.com/repos/{$fullName}/pulls", [
                'state' => 'open',
                'per_page' => 20,
            ]);

            if ($response->failed()) {
                continue;
            }

            foreach ($response->json() ?? [] as $pr) {
                if ($pr['mergeable'] !== false) {
                    continue;
                }

                $state = (string) ($pr['mergeable_state'] ?? '');
                if (!in_array($state, ['dirty', 'blocked', 'unknown'], true)) {
                    continue;
                }

                $conflicts[] = [
                    'repo' => $fullName,
                    'number' => $pr['number'] ?? null,
                    'title' => $pr['title'] ?? '',
                    'state' => $pr['state'] ?? 'open',
                    'updated_at' => $pr['updated_at'] ?? null,
                    'author' => $pr['user']['login'] ?? '',
                    'base_ref' => $pr['base']['ref'] ?? '',
                    'head_ref' => $pr['head']['ref'] ?? '',
                    'mergeable_state' => $state,
                ];

                if (count($conflicts) >= $limit) {
                    break 2;
                }
            }
        }

        return ['ok' => true, 'status' => 200, 'data' => $conflicts];
    }

    /**
     * @return array{ok:bool,status:int,data:array<string,mixed>,message?:string}
     */
    public function getMergeConflicts(string $encryptedToken, string $repo, string $pullNumber): array
    {
        $prResponse = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/pulls/{$pullNumber}");
        if ($prResponse->failed()) {
            return ['ok' => false, 'status' => $prResponse->status(), 'data' => []];
        }

        $pr = $prResponse->json();
        $headSha = (string) ($pr['head']['sha'] ?? '');
        $baseRef = (string) ($pr['base']['ref'] ?? '');
        $headRef = (string) ($pr['head']['ref'] ?? '');

        $filesResponse = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/pulls/{$pullNumber}/files", [
            'per_page' => 100,
        ]);

        if ($filesResponse->failed()) {
            return ['ok' => false, 'status' => $filesResponse->status(), 'data' => []];
        }

        $rawFiles = [];
        foreach ($filesResponse->json() ?? [] as $file) {
            $path = (string) ($file['filename'] ?? '');
            if ($path === '' || $headSha === '') {
                continue;
            }

            $contentResponse = $this->client($encryptedToken)->get(
                "https://api.github.com/repos/{$repo}/contents/".rawurlencode($path),
                ['ref' => $headSha]
            );

            if ($contentResponse->failed()) {
                continue;
            }

            $payload = $contentResponse->json();
            $encoded = (string) ($payload['content'] ?? '');
            if ($encoded === '') {
                continue;
            }

            $content = base64_decode(str_replace("\n", '', $encoded), true);
            if ($content === false) {
                continue;
            }

            $rawFiles[] = ['path' => $path, 'content' => $content];
        }

        $parser = app(\App\Services\Vcs\MergeConflictParser::class);
        $files = $parser->parseFiles($rawFiles);

        return [
            'ok' => true,
            'status' => 200,
            'data' => [
                'repo' => $repo,
                'pr_number' => $pullNumber,
                'title' => $pr['title'] ?? '',
                'base_ref' => $baseRef,
                'head_ref' => $headRef,
                'has_conflicts' => $files !== [] || $pr['mergeable'] === false,
                'mergeable_state' => $pr['mergeable_state'] ?? null,
                'files' => $files,
                'suggested_git_commands' => [
                    'git fetch origin',
                    "git checkout {$headRef}",
                    "git merge origin/{$baseRef}",
                    '# Resolve conflict markers, then:',
                    'git add .',
                    'git commit',
                ],
            ],
        ];
    }
}
