<?php

namespace App\Services\Vcs;

use App\Services\GitHubApiService;

class GitHubVcsProvider implements VcsProviderInterface
{
    public function __construct(private readonly GitHubApiService $gitHubApiService)
    {
    }

    public function key(): string
    {
        return 'github';
    }

    public function label(): string
    {
        return 'GitHub';
    }

    public function getProfile(array $connection): array
    {
        return [
            'username' => (string) ($connection['username'] ?? ''),
            'name' => (string) ($connection['name'] ?? $connection['username'] ?? 'GitHub User'),
            'avatar_url' => $connection['avatar_url'] ?? null,
        ];
    }

    public function getRepos(array $connection): array
    {
        return $this->gitHubApiService->getRepos((string) $connection['token']);
    }

    public function getBranches(array $connection, array $repo): array
    {
        return $this->gitHubApiService->getBranches((string) $connection['token'], (string) ($repo['repo'] ?? ''));
    }

    public function getPullRequests(array $connection, array $repo): array
    {
        return $this->gitHubApiService->getPullRequests((string) $connection['token'], (string) ($repo['repo'] ?? ''));
    }

    public function getRecentPullRequests(array $connection, int $limit = 10): array
    {
        $username = (string) ($connection['username'] ?? '');
        if ($username === '') {
            return ['ok' => false, 'status' => 422, 'data' => []];
        }

        return $this->gitHubApiService->getRecentAccountPullRequests((string) $connection['token'], $username, $limit);
    }

    public function getRecentCommits(array $connection, int $limit = 15): array
    {
        $username = (string) ($connection['username'] ?? '');
        if ($username === '') {
            return ['ok' => false, 'status' => 422, 'data' => []];
        }

        return $this->gitHubApiService->getRecentAccountCommits((string) $connection['token'], $username, $limit);
    }

    public function getPullDetails(array $connection, array $repo, string $pullNumber): array
    {
        return $this->gitHubApiService->getPullDetails((string) $connection['token'], (string) ($repo['repo'] ?? ''), $pullNumber);
    }

    public function getPullIssueComments(array $connection, array $repo, string $pullNumber): array
    {
        return $this->gitHubApiService->getPullIssueComments((string) $connection['token'], (string) ($repo['repo'] ?? ''), $pullNumber);
    }

    public function getPullReviewComments(array $connection, array $repo, string $pullNumber): array
    {
        return $this->gitHubApiService->getPullReviewComments((string) $connection['token'], (string) ($repo['repo'] ?? ''), $pullNumber);
    }

    public function getPullDiff(array $connection, array $repo, string $pullNumber): array
    {
        return $this->gitHubApiService->getPullDiff((string) $connection['token'], (string) ($repo['repo'] ?? ''), $pullNumber);
    }

    public function getBranchDiff(array $connection, array $repo, string $base, string $head): array
    {
        return $this->gitHubApiService->getBranchDiff((string) $connection['token'], (string) ($repo['repo'] ?? ''), $base, $head);
    }

    public function getCommitDiff(array $connection, array $repo, string $commit): array
    {
        return $this->gitHubApiService->getCommitDiff((string) $connection['token'], (string) ($repo['repo'] ?? ''), $commit);
    }

    public function getRecentMergeConflicts(array $connection, int $limit = 10): array
    {
        return $this->gitHubApiService->getRecentMergeConflicts(
            (string) $connection['token'],
            (string) ($connection['username'] ?? ''),
            $limit
        );
    }

    public function getMergeConflicts(array $connection, array $repo, string $pullNumber): array
    {
        return $this->gitHubApiService->getMergeConflicts(
            (string) $connection['token'],
            (string) ($repo['repo'] ?? ''),
            $pullNumber
        );
    }
}
