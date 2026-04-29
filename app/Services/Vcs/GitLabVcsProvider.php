<?php

namespace App\Services\Vcs;

use Illuminate\Support\Facades\Http;

class GitLabVcsProvider implements VcsProviderInterface
{
    public function __construct(private readonly UnifiedDiffBuilder $diffBuilder)
    {
    }

    public function key(): string
    {
        return 'gitlab';
    }

    public function label(): string
    {
        return 'GitLab';
    }

    public function getProfile(array $connection): array
    {
        return [
            'username' => (string) ($connection['username'] ?? ''),
            'name' => (string) ($connection['username'] ?? 'GitLab user'),
            'avatar_url' => null,
        ];
    }

    public function getRepos(array $connection): array
    {
        $response = $this->client($connection)->get($this->apiBase($connection).'/projects', [
            'membership' => true,
            'simple' => true,
            'order_by' => 'last_activity_at',
            'sort' => 'desc',
            'per_page' => 100,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $repos = collect($response->json())
            ->map(fn (array $project) => [
                'name' => $project['name'] ?? '',
                'full_name' => $project['path_with_namespace'] ?? '',
                'private' => ($project['visibility'] ?? 'private') !== 'public',
                'language' => 'Unknown',
                'updated_at' => $project['last_activity_at'] ?? null,
                'open_issues_count' => $project['open_issues_count'] ?? 0,
                'default_branch' => $project['default_branch'] ?? 'main',
                'provider_repo_id' => isset($project['id']) ? (string) $project['id'] : null,
            ])
            ->filter(fn (array $repo) => $repo['full_name'] !== '')
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $repos];
    }

    public function getBranches(array $connection, array $repo): array
    {
        $response = $this->client($connection)->get($this->projectPath($connection, $repo).'/repository/branches', [
            'per_page' => 100,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $branches = collect($response->json())
            ->map(fn (array $branch) => [
                'name' => $branch['name'] ?? '',
                'protected' => $branch['protected'] ?? false,
                'updated_at' => $branch['commit']['committed_date'] ?? $branch['commit']['created_at'] ?? null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $branches];
    }

    public function getPullRequests(array $connection, array $repo): array
    {
        $response = $this->client($connection)->get($this->projectPath($connection, $repo).'/merge_requests', [
            'state' => 'opened',
            'per_page' => 100,
            'order_by' => 'updated_at',
            'sort' => 'desc',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $pulls = collect($response->json())
            ->map(fn (array $mr) => [
                'number' => $mr['iid'] ?? null,
                'title' => $mr['title'] ?? '',
                'state' => $this->normalizeState((string) ($mr['state'] ?? 'opened')),
                'draft' => $mr['draft'] ?? ($mr['work_in_progress'] ?? false),
                'html_url' => $mr['web_url'] ?? '',
                'updated_at' => $mr['updated_at'] ?? null,
                'author' => $mr['author']['username'] ?? $mr['author']['name'] ?? '',
                'comments' => $mr['user_notes_count'] ?? 0,
                'review_comments' => 0,
                'head_ref' => $mr['source_branch'] ?? '',
                'base_ref' => $mr['target_branch'] ?? '',
                'labels' => collect($mr['labels'] ?? [])->map(fn ($label) => ['name' => (string) $label, 'color' => null])->all(),
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $pulls];
    }

    public function getRecentPullRequests(array $connection, int $limit = 10): array
    {
        $limit = max(1, min(20, $limit));

        $response = $this->client($connection)->get($this->apiBase($connection).'/merge_requests', [
            'scope' => 'created_by_me',
            'state' => 'all',
            'per_page' => $limit,
            'order_by' => 'updated_at',
            'sort' => 'desc',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $pulls = collect($response->json())
            ->map(function (array $mr) {
                $fullReference = (string) ($mr['references']['full'] ?? '');
                $repo = $fullReference !== '' && str_contains($fullReference, '!')
                    ? explode('!', $fullReference, 2)[0]
                    : '';

                return [
                'repo' => $repo,
                'number' => $mr['iid'] ?? null,
                'title' => $mr['title'] ?? '',
                'state' => $this->normalizeState((string) ($mr['state'] ?? 'opened')),
                    'updated_at' => $mr['updated_at'] ?? null,
                    'author' => $mr['author']['username'] ?? $mr['author']['name'] ?? '',
                ];
            })
            ->filter(fn (array $mr) => $mr['repo'] !== '' && !empty($mr['number']))
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $pulls];
    }

    public function getPullDetails(array $connection, array $repo, string $pullNumber): array
    {
        $response = $this->client($connection)->get($this->projectPath($connection, $repo).'/merge_requests/'.$pullNumber);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $mr = $response->json();

        return [
            'ok' => true,
            'status' => 200,
            'data' => [
                'number' => $mr['iid'] ?? null,
                'title' => $mr['title'] ?? '',
                'body' => $mr['description'] ?? '',
                'state' => $this->normalizeState((string) ($mr['state'] ?? 'opened')),
                'draft' => $mr['draft'] ?? ($mr['work_in_progress'] ?? false),
                'merged_at' => $mr['merged_at'] ?? null,
                'author' => $mr['author']['username'] ?? $mr['author']['name'] ?? '',
                'changed_files' => (int) ($mr['changes_count'] ?? 0),
                'additions' => 0,
                'deletions' => 0,
                'comments' => $mr['user_notes_count'] ?? 0,
                'review_comments' => 0,
                'updated_at' => $mr['updated_at'] ?? null,
            ],
        ];
    }

    public function getPullIssueComments(array $connection, array $repo, string $pullNumber): array
    {
        $discussionResult = $this->getDiscussions($connection, $repo, $pullNumber);
        if (!$discussionResult['ok']) {
            return $discussionResult;
        }

        $comments = collect($discussionResult['data'])
            ->flatMap(fn (array $discussion) => $discussion['notes'] ?? [])
            ->filter(fn (array $note) => empty($note['position']))
            ->map(fn (array $note) => [
                'author' => $note['author']['username'] ?? $note['author']['name'] ?? '',
                'body' => $note['body'] ?? '',
                'updated_at' => $note['updated_at'] ?? null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $comments];
    }

    public function getPullReviewComments(array $connection, array $repo, string $pullNumber): array
    {
        $discussionResult = $this->getDiscussions($connection, $repo, $pullNumber);
        if (!$discussionResult['ok']) {
            return $discussionResult;
        }

        $comments = collect($discussionResult['data'])
            ->flatMap(fn (array $discussion) => $discussion['notes'] ?? [])
            ->filter(fn (array $note) => !empty($note['position']))
            ->map(function (array $note) {
                $position = $note['position'] ?? [];
                $newLine = $position['new_line'] ?? null;
                $oldLine = $position['old_line'] ?? null;

                return [
                    'author' => $note['author']['username'] ?? $note['author']['name'] ?? '',
                    'path' => $position['new_path'] ?? $position['old_path'] ?? '',
                    'line' => $newLine ?? $oldLine,
                    'original_line' => $oldLine ?? $newLine,
                    'side' => $newLine ? 'RIGHT' : 'LEFT',
                    'original_side' => $newLine ? 'RIGHT' : 'LEFT',
                    'body' => $note['body'] ?? '',
                    'updated_at' => $note['updated_at'] ?? null,
                ];
            })
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $comments];
    }

    public function getPullDiff(array $connection, array $repo, string $pullNumber): array
    {
        $response = $this->client($connection)->get($this->projectPath($connection, $repo).'/merge_requests/'.$pullNumber.'/changes', [
            'unidiff' => true,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => ''];
        }

        $diff = $this->diffBuilder->fromEntries($response->json('changes') ?? []);

        return ['ok' => true, 'status' => 200, 'data' => $diff];
    }

    public function getBranchDiff(array $connection, array $repo, string $base, string $head): array
    {
        $response = $this->client($connection)->get($this->projectPath($connection, $repo).'/repository/compare', [
            'from' => $base,
            'to' => $head,
            'unidiff' => true,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => ''];
        }

        $diff = $this->diffBuilder->fromEntries($response->json('diffs') ?? []);

        return ['ok' => true, 'status' => 200, 'data' => $diff];
    }

    private function getDiscussions(array $connection, array $repo, string $pullNumber): array
    {
        $response = $this->client($connection)->get($this->projectPath($connection, $repo).'/merge_requests/'.$pullNumber.'/discussions', [
            'per_page' => 100,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        return ['ok' => true, 'status' => 200, 'data' => $response->json() ?? []];
    }

    private function client(array $connection)
    {
        return Http::withHeaders([
            'PRIVATE-TOKEN' => (string) ($connection['token'] ?? ''),
            'Accept' => 'application/json',
        ]);
    }

    private function apiBase(array $connection): string
    {
        $baseUrl = rtrim((string) ($connection['base_url'] ?? 'https://gitlab.com'), '/');

        return str_ends_with($baseUrl, '/api/v4') ? $baseUrl : $baseUrl.'/api/v4';
    }

    private function projectPath(array $connection, array $repo): string
    {
        $project = (string) ($repo['repo_id'] ?? '');
        if ($project === '') {
            $project = rawurlencode((string) ($repo['repo'] ?? ''));
        }

        return $this->apiBase($connection).'/projects/'.$project;
    }

    private function normalizeState(string $state): string
    {
        return match (strtolower($state)) {
            'opened' => 'open',
            'merged' => 'merged',
            default => strtolower($state),
        };
    }
}
