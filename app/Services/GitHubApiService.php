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
     *
     * Falls back to the merge commit diff when the branch comparison
     * returns empty (e.g. the branch was already merged).
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

        $diff = $response->body();

        if (trim($diff) === '') {
            return $this->getMergeCommitDiffForBranch($encryptedToken, $repo, $head, $base);
        }

        return ['ok' => true, 'status' => 200, 'data' => $diff];
    }

    /**
     * Finds the merge commit that merged $head into $base and returns its diff.
     */
    private function getMergeCommitDiffForBranch(string $encryptedToken, string $repo, string $head, string $base): array
    {
        $commitsResponse = $this->client($encryptedToken)
            ->get("https://api.github.com/repos/{$repo}/commits", [
                'sha' => $base,
                'per_page' => 50,
            ]);

        if ($commitsResponse->failed()) {
            return ['ok' => true, 'status' => 200, 'data' => ''];
        }

        $mergeSha = null;
        $headLower = strtolower($head);

        foreach ($commitsResponse->json() as $commit) {
            $parents = $commit['parents'] ?? [];
            if (count($parents) >= 2) {
                $message = strtolower($commit['commit']['message'] ?? '');
                if (str_contains($message, $headLower)) {
                    $mergeSha = $commit['sha'];
                    break;
                }
            }
        }

        if ($mergeSha === null) {
            return ['ok' => true, 'status' => 200, 'data' => ''];
        }

        return $this->getCommitDiff($encryptedToken, $repo, $mergeSha);
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
     * GitHub does not expose conflict marker hunks via REST; we verify conflict status per PR.
     *
     * @return array{ok:bool,status:int,data:array<int,array<string,mixed>>}
     */
    public function getRecentMergeConflicts(string $encryptedToken, string $username, int $limit = 10): array
    {
        $limit = max(1, min(15, $limit));
        $username = trim($username);
        if ($username === '') {
            return ['ok' => false, 'status' => 422, 'data' => []];
        }

        $searchResponse = $this->client($encryptedToken)->get('https://api.github.com/search/issues', [
            'q' => sprintf('is:pr is:open author:%s', $username),
            'sort' => 'updated',
            'order' => 'desc',
            'per_page' => 30,
        ]);

        if ($searchResponse->failed()) {
            return ['ok' => false, 'status' => $searchResponse->status(), 'data' => []];
        }

        $conflicts = [];
        foreach ($searchResponse->json('items') ?? [] as $item) {
            $repo = $this->extractRepoFullName($item['repository_url'] ?? '');
            $number = $item['number'] ?? null;
            if ($repo === '' || $number === null) {
                continue;
            }

            $detailResponse = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/pulls/{$number}");
            if ($detailResponse->failed()) {
                continue;
            }

            $pr = $detailResponse->json();
            if (!$this->isPullRequestMergeConflicted($pr)) {
                continue;
            }

            $conflicts[] = [
                'repo' => $repo,
                'number' => $pr['number'] ?? $number,
                'title' => $pr['title'] ?? ($item['title'] ?? ''),
                'state' => $pr['state'] ?? 'open',
                'updated_at' => $pr['updated_at'] ?? ($item['updated_at'] ?? null),
                'author' => $pr['user']['login'] ?? $username,
                'base_ref' => $pr['base']['ref'] ?? '',
                'head_ref' => $pr['head']['ref'] ?? '',
                'mergeable_state' => $pr['mergeable_state'] ?? 'dirty',
                'conflict_source' => 'github_metadata_only',
            ];

            if (count($conflicts) >= $limit) {
                break;
            }
        }

        return ['ok' => true, 'status' => 200, 'data' => $conflicts];
    }

    /**
     * Returns metadata-only conflict info. GitHub REST does not provide conflict hunks.
     *
     * @return array{ok:bool,status:int,data:array<string,mixed>,message?:string}
     */
    public function getMergeConflicts(string $encryptedToken, string $repo, string $pullNumber): array
    {
        $prResponse = $this->client($encryptedToken)->get("https://api.github.com/repos/{$repo}/pulls/{$pullNumber}");
        if ($prResponse->failed()) {
            return ['ok' => false, 'status' => $prResponse->status(), 'data' => []];
        }

        $pr = $prResponse->json();
        $baseRef = (string) ($pr['base']['ref'] ?? '');
        $headRef = (string) ($pr['head']['ref'] ?? '');
        $mergeableState = (string) ($pr['mergeable_state'] ?? '');
        $hasConflicts = $this->isPullRequestMergeConflicted($pr);

        return [
            'ok' => true,
            'status' => 200,
            'data' => [
                'repo' => $repo,
                'pr_number' => $pullNumber,
                'title' => $pr['title'] ?? '',
                'base_ref' => $baseRef,
                'head_ref' => $headRef,
                'has_conflicts' => $hasConflicts,
                'mergeable_state' => $mergeableState,
                'mergeable' => $pr['mergeable'] ?? null,
                'files' => [],
                'has_hunks' => false,
                'conflict_source' => 'github_metadata_only',
                'message' => $hasConflicts
                    ? 'GitHub reports this pull request as conflicted (mergeable_state: dirty). The GitHub REST API does not expose per-file conflict marker hunks. Reproduce and resolve the merge locally or in the GitHub web UI.'
                    : 'GitHub does not currently report merge conflicts for this pull request. It may have been resolved or is still computing mergeability.',
                'suggested_git_commands' => [
                    'git fetch origin',
                    "git checkout {$headRef}",
                    "git merge origin/{$baseRef}",
                    '# If conflicts appear, resolve markers in your editor, then:',
                    'git add .',
                    'git commit',
                    '# Or abort: git merge --abort',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $pr
     */
    public function isPullRequestMergeConflicted(array $pr): bool
    {
        return ($pr['mergeable'] ?? null) === false
            && (string) ($pr['mergeable_state'] ?? '') === 'dirty';
    }
}
