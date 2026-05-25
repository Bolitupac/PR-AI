<?php

namespace App\Services\Vcs;

interface VcsProviderInterface
{
    public function key(): string;

    public function label(): string;

    public function getProfile(array $connection): array;

    public function getRepos(array $connection): array;

    public function getBranches(array $connection, array $repo): array;

    public function getPullRequests(array $connection, array $repo): array;

    public function getRecentPullRequests(array $connection, int $limit = 10): array;

    public function getRecentCommits(array $connection, int $limit = 15): array;

    public function getPullDetails(array $connection, array $repo, string $pullNumber): array;

    public function getPullIssueComments(array $connection, array $repo, string $pullNumber): array;

    public function getPullReviewComments(array $connection, array $repo, string $pullNumber): array;

    public function getPullDiff(array $connection, array $repo, string $pullNumber): array;

    public function getBranchDiff(array $connection, array $repo, string $base, string $head): array;

    public function getCommitDiff(array $connection, array $repo, string $commit): array;

    /**
     * @return array{ok:bool,status:int,data:array<int,array<string,mixed>>,message?:string}
     */
    public function getRecentMergeConflicts(array $connection, int $limit = 10): array;

    /**
     * @return array{ok:bool,status:int,data:array<string,mixed>,message?:string}
     */
    public function getMergeConflicts(array $connection, array $repo, string $pullNumber): array;
}
