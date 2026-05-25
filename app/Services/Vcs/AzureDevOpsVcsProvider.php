<?php

namespace App\Services\Vcs;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class AzureDevOpsVcsProvider implements VcsProviderInterface
{
    public function key(): string
    {
        return 'azure';
    }

    public function label(): string
    {
        return 'Azure DevOps';
    }

    public function getProfile(array $connection): array
    {
        return [
            'username' => (string) ($connection['username'] ?? ''),
            'name' => (string) ($connection['username'] ?? 'Azure DevOps user'),
            'avatar_url' => null,
        ];
    }

    public function getRepos(array $connection): array
    {
        $response = $this->client($connection)->get($this->projectApiBase($connection).'/git/repositories', [
            'api-version' => '7.1',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $organization = (string) ($connection['organization'] ?? '');
        $projectName = (string) ($connection['project'] ?? '');

        $repos = collect($response->json('value') ?? [])
            ->map(fn (array $repo) => [
                'name' => $repo['name'] ?? '',
                'full_name' => trim($organization.'/'.$projectName.'/'.($repo['name'] ?? ''), '/'),
                'private' => (($repo['project']['visibility'] ?? 'private') !== 'public'),
                'language' => 'Unknown',
                'updated_at' => null,
                'open_issues_count' => 0,
                'default_branch' => $this->stripRefPrefix((string) ($repo['defaultBranch'] ?? 'refs/heads/main')),
                'provider_repo_id' => isset($repo['id']) ? (string) $repo['id'] : null,
                'provider_project' => $repo['project']['name'] ?? $projectName,
                'provider_project_id' => isset($repo['project']['id']) ? (string) $repo['project']['id'] : null,
                'provider_organization' => $organization,
            ])
            ->filter(fn (array $repo) => $repo['full_name'] !== '')
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $repos];
    }

    public function getBranches(array $connection, array $repo): array
    {
        $response = $this->client($connection)->get($this->repositoryApiBase($connection, $repo).'/refs', [
            'filter' => 'heads/',
            'api-version' => '7.1',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $branches = collect($response->json('value') ?? [])
            ->map(fn (array $branch) => [
                'name' => $this->stripRefPrefix((string) ($branch['name'] ?? '')),
                'protected' => $branch['isLocked'] ?? false,
                'updated_at' => null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $branches];
    }

    public function getPullRequests(array $connection, array $repo): array
    {
        $response = $this->client($connection)->get($this->repositoryApiBase($connection, $repo).'/pullrequests', [
            'searchCriteria.status' => 'active',
            '$top' => 100,
            'api-version' => '7.1',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $pulls = collect($response->json('value') ?? [])
            ->map(fn (array $pull) => [
                'number' => $pull['pullRequestId'] ?? null,
                'title' => $pull['title'] ?? '',
                'state' => $this->normalizePullState((string) ($pull['status'] ?? 'active')),
                'draft' => $pull['isDraft'] ?? false,
                'html_url' => $pull['url'] ?? '',
                'updated_at' => $pull['creationDate'] ?? null,
                'author' => $pull['createdBy']['displayName'] ?? $pull['createdBy']['uniqueName'] ?? '',
                'comments' => 0,
                'review_comments' => 0,
                'head_ref' => $this->stripRefPrefix((string) ($pull['sourceRefName'] ?? '')),
                'base_ref' => $this->stripRefPrefix((string) ($pull['targetRefName'] ?? '')),
                'labels' => [],
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $pulls];
    }

    public function getRecentPullRequests(array $connection, int $limit = 10): array
    {
        $response = $this->client($connection)->get($this->projectApiBase($connection).'/git/pullrequests', [
            'searchCriteria.status' => 'all',
            '$top' => max(1, min(20, $limit * 2)),
            'api-version' => '7.1',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $username = strtolower(trim((string) ($connection['username'] ?? '')));
        $organization = (string) ($connection['organization'] ?? '');
        $projectName = (string) ($connection['project'] ?? '');

        $pulls = collect($response->json('value') ?? [])
            ->filter(function (array $pull) use ($username) {
                if ($username === '') {
                    return true;
                }

                $author = strtolower((string) ($pull['createdBy']['uniqueName'] ?? $pull['createdBy']['displayName'] ?? ''));

                return $author === $username;
            })
            ->sortByDesc(fn (array $pull) => $pull['creationDate'] ?? '')
            ->take($limit)
            ->map(fn (array $pull) => [
                'repo' => trim($organization.'/'.$projectName.'/'.($pull['repository']['name'] ?? ''), '/'),
                'number' => $pull['pullRequestId'] ?? null,
                'title' => $pull['title'] ?? '',
                'state' => $this->normalizePullState((string) ($pull['status'] ?? 'active')),
                'updated_at' => $pull['creationDate'] ?? null,
                'author' => $pull['createdBy']['displayName'] ?? $pull['createdBy']['uniqueName'] ?? '',
                'repo_id' => isset($pull['repository']['id']) ? (string) $pull['repository']['id'] : null,
                'project' => $projectName,
                'organization' => $organization,
            ])
            ->filter(fn (array $pull) => $pull['repo'] !== '' && !empty($pull['number']))
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $pulls];
    }

    public function getRecentCommits(array $connection, int $limit = 15): array
    {
        return ['ok' => false, 'status' => 501, 'message' => 'Select a repository to view its recent commits.'];
    }

    public function getPullDetails(array $connection, array $repo, string $pullNumber): array
    {
        $response = $this->client($connection)->get($this->repositoryApiBase($connection, $repo).'/pullrequests/'.$pullNumber, [
            'api-version' => '7.1',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $pull = $response->json();

        return [
            'ok' => true,
            'status' => 200,
            'data' => [
                'number' => $pull['pullRequestId'] ?? null,
                'title' => $pull['title'] ?? '',
                'body' => $pull['description'] ?? '',
                'state' => $this->normalizePullState((string) ($pull['status'] ?? 'active')),
                'draft' => $pull['isDraft'] ?? false,
                'merged_at' => ($pull['status'] ?? '') === 'completed' ? ($pull['closedDate'] ?? $pull['creationDate'] ?? null) : null,
                'author' => $pull['createdBy']['displayName'] ?? $pull['createdBy']['uniqueName'] ?? '',
                'changed_files' => 0,
                'additions' => 0,
                'deletions' => 0,
                'comments' => 0,
                'review_comments' => 0,
                'updated_at' => $pull['creationDate'] ?? null,
                'base_commit' => $pull['lastMergeTargetCommit']['commitId'] ?? null,
                'head_commit' => $pull['lastMergeSourceCommit']['commitId'] ?? null,
            ],
        ];
    }

    public function getPullIssueComments(array $connection, array $repo, string $pullNumber): array
    {
        $threadsResult = $this->getThreads($connection, $repo, $pullNumber);
        if (!$threadsResult['ok']) {
            return $threadsResult;
        }

        $comments = collect($threadsResult['data'])
            ->filter(fn (array $thread) => empty($thread['threadContext']['filePath']))
            ->flatMap(fn (array $thread) => $thread['comments'] ?? [])
            ->filter(fn (array $comment) => !empty($comment['content']))
            ->map(fn (array $comment) => [
                'author' => $comment['author']['displayName'] ?? $comment['author']['uniqueName'] ?? '',
                'body' => $comment['content'] ?? '',
                'updated_at' => $comment['lastUpdatedDate'] ?? $comment['publishedDate'] ?? null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $comments];
    }

    public function getPullReviewComments(array $connection, array $repo, string $pullNumber): array
    {
        $threadsResult = $this->getThreads($connection, $repo, $pullNumber);
        if (!$threadsResult['ok']) {
            return $threadsResult;
        }

        $comments = collect($threadsResult['data'])
            ->filter(fn (array $thread) => !empty($thread['threadContext']['filePath']))
            ->flatMap(function (array $thread) {
                $context = $thread['threadContext'] ?? [];
                $rightLine = $context['rightFileStart']['line'] ?? null;
                $leftLine = $context['leftFileStart']['line'] ?? null;

                return collect($thread['comments'] ?? [])
                    ->filter(fn (array $comment) => !empty($comment['content']))
                    ->map(fn (array $comment) => [
                        'author' => $comment['author']['displayName'] ?? $comment['author']['uniqueName'] ?? '',
                        'path' => ltrim((string) ($context['filePath'] ?? ''), '/'),
                        'line' => $rightLine ?? $leftLine,
                        'original_line' => $leftLine ?? $rightLine,
                        'side' => $rightLine ? 'RIGHT' : 'LEFT',
                        'original_side' => $rightLine ? 'RIGHT' : 'LEFT',
                        'body' => $comment['content'] ?? '',
                        'updated_at' => $comment['lastUpdatedDate'] ?? $comment['publishedDate'] ?? null,
                    ]);
            })
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $comments];
    }

    public function getPullDiff(array $connection, array $repo, string $pullNumber): array
    {
        $detailsResult = $this->getPullDetails($connection, $repo, $pullNumber);
        if (!$detailsResult['ok']) {
            return ['ok' => false, 'status' => $detailsResult['status'], 'data' => ''];
        }

        $details = $detailsResult['data'];
        $baseCommit = (string) ($details['base_commit'] ?? '');
        $headCommit = (string) ($details['head_commit'] ?? '');

        if ($baseCommit === '' || $headCommit === '') {
            return ['ok' => false, 'status' => 422, 'data' => ''];
        }

        return $this->buildDiffFromVersions($connection, $repo, $baseCommit, 'commit', $headCommit, 'commit');
    }

    public function getBranchDiff(array $connection, array $repo, string $base, string $head): array
    {
        return $this->buildDiffFromVersions($connection, $repo, $base, 'branch', $head, 'branch');
    }

    public function getCommitDiff(array $connection, array $repo, string $commit): array
    {
        $details = $this->client($connection)->get($this->repositoryApiBase($connection, $repo).'/commits/'.$commit, [
            'api-version' => '7.1',
        ]);

        if ($details->failed()) {
            return ['ok' => false, 'status' => $details->status(), 'data' => ''];
        }

        $parents = $details->json('parents') ?? [];
        $parentId = $parents[0]['commitId'] ?? null;
        if (!$parentId) {
            return ['ok' => false, 'status' => 422, 'message' => 'Cannot diff root commit without a parent.'];
        }

        return $this->buildDiffFromVersions($connection, $repo, $parentId, 'commit', $commit, 'commit');
    }

    public function getRecentMergeConflicts(array $connection, int $limit = 10): array
    {
        return ['ok' => false, 'status' => 501, 'message' => 'Merge conflict import is not supported for Azure DevOps yet.', 'data' => []];
    }

    public function getMergeConflicts(array $connection, array $repo, string $pullNumber): array
    {
        return ['ok' => false, 'status' => 501, 'message' => 'Merge conflict import is not supported for Azure DevOps yet.', 'data' => []];
    }

    private function buildDiffFromVersions(array $connection, array $repo, string $baseVersion, string $baseType, string $targetVersion, string $targetType): array
    {
        $diffResponse = $this->client($connection)->get($this->repositoryApiBase($connection, $repo).'/diffs/commits', [
            'baseVersion' => $baseVersion,
            'baseVersionType' => $baseType,
            'targetVersion' => $targetVersion,
            'targetVersionType' => $targetType,
            'api-version' => '7.1',
        ]);

        if ($diffResponse->failed()) {
            return ['ok' => false, 'status' => $diffResponse->status(), 'data' => ''];
        }

        $entries = $diffResponse->json('changes') ?? $diffResponse->json('changeEntries') ?? [];
        if (!is_array($entries) || $entries === []) {
            return ['ok' => true, 'status' => 200, 'data' => ''];
        }

        $diffParts = [];
        foreach ($entries as $entry) {
            $path = ltrim((string) ($entry['item']['path'] ?? ''), '/');
            if ($path === '') {
                continue;
            }

            $changeType = strtolower((string) ($entry['changeType'] ?? 'edit'));
            $oldContent = in_array($changeType, ['add', 'branch'], true)
                ? null
                : $this->fetchItemContent($connection, $repo, '/'.$path, $baseVersion, $baseType);
            $newContent = in_array($changeType, ['delete'], true)
                ? null
                : $this->fetchItemContent($connection, $repo, '/'.$path, $targetVersion, $targetType);

            $piece = $this->diffSinglePath($path, $oldContent, $newContent);
            if ($piece !== '') {
                $diffParts[] = $piece;
            }
        }

        return ['ok' => true, 'status' => 200, 'data' => implode("\n", $diffParts)];
    }

    private function fetchItemContent(array $connection, array $repo, string $path, string $version, string $versionType): ?string
    {
        $response = $this->client($connection)->get($this->repositoryApiBase($connection, $repo).'/items', [
            'path' => $path,
            'includeContent' => 'true',
            '$format' => 'json',
            'versionDescriptor.version' => $version,
            'versionDescriptor.versionType' => $versionType,
            'api-version' => '7.1',
        ]);

        if ($response->status() === 404) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $content = $response->json('content');
        if (!is_string($content)) {
            return null;
        }

        return $content;
    }

    private function diffSinglePath(string $relativePath, ?string $oldContent, ?string $newContent): string
    {
        $baseDir = storage_path('framework/cache/vcs-diffs/'.uniqid('azure-', true));
        $oldFile = $baseDir.'/old';
        $newFile = $baseDir.'/new';

        @mkdir($baseDir, 0775, true);

        if ($oldContent !== null) {
            file_put_contents($oldFile, $oldContent);
        }
        if ($newContent !== null) {
            file_put_contents($newFile, $newContent);
        }

        if ($oldContent === null && !file_exists($oldFile)) {
            touch($oldFile);
            unlink($oldFile);
        }
        if ($newContent === null && !file_exists($newFile)) {
            touch($newFile);
            unlink($newFile);
        }

        $process = new Process([
            'git',
            '--no-pager',
            'diff',
            '--no-index',
            '--no-ext-diff',
            '--src-prefix=a/',
            '--dst-prefix=b/',
            '--unified=3',
            $oldFile,
            $newFile,
        ]);
        $process->setTimeout(6);
        $process->run();

        $output = $process->getOutput();
        $this->cleanupDirectory($baseDir);

        if (!in_array($process->getExitCode(), [0, 1], true)) {
            return '';
        }

        $oldLabel = 'a'.ltrim($oldFile, '/');
        $newLabel = 'b'.ltrim($newFile, '/');
        $output = str_replace([$oldLabel, $newLabel], ['a/'.$relativePath, 'b/'.$relativePath], $output);
        $output = str_replace([$oldFile, $newFile], [$relativePath, $relativePath], $output);

        return trim($output);
    }

    private function cleanupDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $file = $path.'/'.$item;
            if (is_file($file) || is_link($file)) {
                @unlink($file);
            }
        }

        @rmdir($path);
    }

    private function getThreads(array $connection, array $repo, string $pullNumber): array
    {
        $response = $this->client($connection)->get($this->repositoryApiBase($connection, $repo).'/pullRequests/'.$pullNumber.'/threads', [
            'api-version' => '7.1',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        return ['ok' => true, 'status' => 200, 'data' => $response->json('value') ?? []];
    }

    private function client(array $connection)
    {
        $token = base64_encode(':'.(string) ($connection['token'] ?? ''));

        return Http::withHeaders([
            'Authorization' => 'Basic '.$token,
            'Accept' => 'application/json',
        ]);
    }

    private function projectApiBase(array $connection): string
    {
        return sprintf(
            'https://dev.azure.com/%s/%s/_apis',
            rawurlencode((string) ($connection['organization'] ?? '')),
            rawurlencode((string) ($connection['project'] ?? '')),
        );
    }

    private function repositoryApiBase(array $connection, array $repo): string
    {
        $project = rawurlencode((string) ($repo['project'] ?? $repo['provider_project'] ?? $connection['project'] ?? ''));
        $organization = rawurlencode((string) ($repo['organization'] ?? $repo['provider_organization'] ?? $connection['organization'] ?? ''));
        $repositoryId = rawurlencode((string) ($repo['repo_id'] ?? $repo['provider_repo_id'] ?? ''));

        return sprintf('https://dev.azure.com/%s/%s/_apis/git/repositories/%s', $organization, $project, $repositoryId);
    }

    private function normalizePullState(string $state): string
    {
        return match (strtolower($state)) {
            'active' => 'open',
            'completed' => 'merged',
            'abandoned' => 'closed',
            default => strtolower($state),
        };
    }

    private function stripRefPrefix(string $value): string
    {
        return str_starts_with($value, 'refs/heads/') ? substr($value, 11) : $value;
    }
}
