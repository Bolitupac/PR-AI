<?php

namespace App\Services\Vcs;

use Illuminate\Support\Facades\Http;

class BitbucketVcsProvider implements VcsProviderInterface
{
    public function key(): string
    {
        return 'bitbucket';
    }

    public function label(): string
    {
        return 'Bitbucket';
    }

    public function getProfile(array $connection): array
    {
        return [
            'username' => (string) ($connection['username'] ?? ''),
            'name' => (string) ($connection['username'] ?? 'Bitbucket user'),
            'avatar_url' => null,
        ];
    }

    public function getRepos(array $connection): array
    {
        $workspace = (string) ($connection['workspace'] ?? '');
        $response = $this->client($connection)->get($this->apiBase().'/repositories/'.$workspace, [
            'role' => 'member',
            'sort' => '-updated_on',
            'pagelen' => 100,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $repos = collect($response->json('values') ?? [])
            ->map(fn (array $repo) => [
                'name' => $repo['name'] ?? '',
                'full_name' => $repo['full_name'] ?? sprintf('%s/%s', $workspace, $repo['slug'] ?? ''),
                'private' => $repo['is_private'] ?? true,
                'language' => 'Unknown',
                'updated_at' => $repo['updated_on'] ?? null,
                'open_issues_count' => 0,
                'default_branch' => $repo['mainbranch']['name'] ?? 'main',
                'provider_workspace' => $workspace,
                'provider_repo_slug' => $repo['slug'] ?? null,
            ])
            ->filter(fn (array $repo) => $repo['full_name'] !== '')
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $repos];
    }

    public function getBranches(array $connection, array $repo): array
    {
        [$workspace, $slug] = $this->repoParts($connection, $repo);
        $response = $this->client($connection)->get($this->apiBase()."/repositories/{$workspace}/{$slug}/refs/branches", [
            'sort' => '-target.date',
            'pagelen' => 100,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $branches = collect($response->json('values') ?? [])
            ->map(fn (array $branch) => [
                'name' => $branch['name'] ?? '',
                'protected' => false,
                'updated_at' => $branch['target']['date'] ?? null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $branches];
    }

    public function getPullRequests(array $connection, array $repo): array
    {
        [$workspace, $slug] = $this->repoParts($connection, $repo);
        $response = $this->client($connection)->get($this->apiBase()."/repositories/{$workspace}/{$slug}/pullrequests", [
            'state' => 'OPEN',
            'sort' => '-updated_on',
            'pagelen' => 100,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $pulls = collect($response->json('values') ?? [])
            ->map(fn (array $pull) => [
                'number' => $pull['id'] ?? null,
                'title' => $pull['title'] ?? '',
                'state' => strtolower((string) ($pull['state'] ?? 'open')),
                'draft' => $pull['draft'] ?? false,
                'html_url' => $pull['links']['html']['href'] ?? '',
                'updated_at' => $pull['updated_on'] ?? null,
                'author' => $pull['author']['display_name'] ?? $pull['author']['nickname'] ?? '',
                'comments' => $pull['comment_count'] ?? 0,
                'review_comments' => 0,
                'head_ref' => $pull['source']['branch']['name'] ?? '',
                'base_ref' => $pull['destination']['branch']['name'] ?? '',
                'labels' => [],
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $pulls];
    }

    public function getRecentPullRequests(array $connection, int $limit = 10): array
    {
        $workspace = (string) ($connection['workspace'] ?? '');
        $username = (string) ($connection['username'] ?? '');
        if ($workspace === '' || $username === '') {
            return ['ok' => false, 'status' => 422, 'data' => []];
        }

        $response = $this->client($connection)->get($this->apiBase()."/workspaces/{$workspace}/pullrequests/{$username}", [
            'state' => ['OPEN', 'MERGED', 'DECLINED'],
            'sort' => '-updated_on',
            'pagelen' => max(1, min(20, $limit)),
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $pulls = collect($response->json('values') ?? [])
            ->map(fn (array $pull) => [
                'repo' => $pull['destination']['repository']['full_name'] ?? $pull['source']['repository']['full_name'] ?? '',
                'number' => $pull['id'] ?? null,
                'title' => $pull['title'] ?? '',
                'state' => strtolower((string) ($pull['state'] ?? 'open')),
                'updated_at' => $pull['updated_on'] ?? null,
                'author' => $pull['author']['display_name'] ?? $pull['author']['nickname'] ?? '',
            ])
            ->filter(fn (array $pull) => $pull['repo'] !== '' && !empty($pull['number']))
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $pulls];
    }

    public function getPullDetails(array $connection, array $repo, string $pullNumber): array
    {
        [$workspace, $slug] = $this->repoParts($connection, $repo);
        $response = $this->client($connection)->get($this->apiBase()."/repositories/{$workspace}/{$slug}/pullrequests/{$pullNumber}");

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $pull = $response->json();

        return [
            'ok' => true,
            'status' => 200,
            'data' => [
                'number' => $pull['id'] ?? null,
                'title' => $pull['title'] ?? '',
                'body' => $pull['description'] ?? '',
                'state' => strtolower((string) ($pull['state'] ?? 'open')),
                'draft' => $pull['draft'] ?? false,
                'merged_at' => $pull['state'] === 'MERGED' ? ($pull['updated_on'] ?? null) : null,
                'author' => $pull['author']['display_name'] ?? $pull['author']['nickname'] ?? '',
                'changed_files' => 0,
                'additions' => 0,
                'deletions' => 0,
                'comments' => $pull['comment_count'] ?? 0,
                'review_comments' => 0,
                'updated_at' => $pull['updated_on'] ?? null,
            ],
        ];
    }

    public function getPullIssueComments(array $connection, array $repo, string $pullNumber): array
    {
        $commentsResult = $this->getComments($connection, $repo, $pullNumber);
        if (!$commentsResult['ok']) {
            return $commentsResult;
        }

        $comments = collect($commentsResult['data'])
            ->filter(fn (array $comment) => empty($comment['inline']))
            ->map(fn (array $comment) => [
                'author' => $comment['user']['display_name'] ?? $comment['user']['nickname'] ?? '',
                'body' => $comment['content']['raw'] ?? '',
                'updated_at' => $comment['updated_on'] ?? $comment['created_on'] ?? null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $comments];
    }

    public function getPullReviewComments(array $connection, array $repo, string $pullNumber): array
    {
        $commentsResult = $this->getComments($connection, $repo, $pullNumber);
        if (!$commentsResult['ok']) {
            return $commentsResult;
        }

        $comments = collect($commentsResult['data'])
            ->filter(fn (array $comment) => !empty($comment['inline']))
            ->map(function (array $comment) {
                $inline = $comment['inline'] ?? [];
                $line = $inline['to'] ?? $inline['from'] ?? null;

                return [
                    'author' => $comment['user']['display_name'] ?? $comment['user']['nickname'] ?? '',
                    'path' => $inline['path'] ?? '',
                    'line' => $line,
                    'original_line' => $line,
                    'side' => isset($inline['to']) ? 'RIGHT' : 'LEFT',
                    'original_side' => isset($inline['to']) ? 'RIGHT' : 'LEFT',
                    'body' => $comment['content']['raw'] ?? '',
                    'updated_at' => $comment['updated_on'] ?? $comment['created_on'] ?? null,
                ];
            })
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $comments];
    }

    public function getPullDiff(array $connection, array $repo, string $pullNumber): array
    {
        [$workspace, $slug] = $this->repoParts($connection, $repo);
        $response = $this->client($connection)
            ->withHeaders(['Accept' => 'text/plain'])
            ->get($this->apiBase()."/repositories/{$workspace}/{$slug}/pullrequests/{$pullNumber}/diff");

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => ''];
        }

        return ['ok' => true, 'status' => 200, 'data' => $response->body()];
    }

    public function getBranchDiff(array $connection, array $repo, string $base, string $head): array
    {
        [$workspace, $slug] = $this->repoParts($connection, $repo);
        $response = $this->client($connection)
            ->withHeaders(['Accept' => 'text/plain'])
            ->get($this->apiBase()."/repositories/{$workspace}/{$slug}/diff/{$base}..{$head}");

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => ''];
        }

        return ['ok' => true, 'status' => 200, 'data' => $response->body()];
    }

    private function getComments(array $connection, array $repo, string $pullNumber): array
    {
        [$workspace, $slug] = $this->repoParts($connection, $repo);
        $response = $this->client($connection)->get($this->apiBase()."/repositories/{$workspace}/{$slug}/pullrequests/{$pullNumber}/comments", [
            'sort' => '-updated_on',
            'pagelen' => 100,
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        return ['ok' => true, 'status' => 200, 'data' => $response->json('values') ?? []];
    }

    private function client(array $connection)
    {
        return Http::withToken((string) ($connection['token'] ?? ''))
            ->withHeaders(['Accept' => 'application/json']);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function repoParts(array $connection, array $repo): array
    {
        $workspace = (string) ($repo['workspace'] ?? $repo['provider_workspace'] ?? $connection['workspace'] ?? '');
        $slug = (string) ($repo['repo_slug'] ?? $repo['provider_repo_slug'] ?? '');

        if ($slug === '') {
            $parts = explode('/', (string) ($repo['repo'] ?? ''), 2);
            $workspace = $workspace !== '' ? $workspace : (string) ($parts[0] ?? '');
            $slug = (string) ($parts[1] ?? '');
        }

        return [$workspace, $slug];
    }

    private function apiBase(): string
    {
        return 'https://api.bitbucket.org/2.0';
    }
}
