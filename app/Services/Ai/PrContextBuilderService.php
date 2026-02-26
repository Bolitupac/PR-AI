<?php

namespace App\Services\Ai;

use App\Services\GitHubApiService;

class PrContextBuilderService
{
    public function __construct(private readonly GitHubApiService $githubApiService)
    {
    }

    // Builds AI context from GitHub PR endpoints plus optional raw diff input.
    public function build(string $encryptedToken, string $repo, string $prNumber, ?string $fallbackDiffText = null): array
    {
        $detailsResult = $this->githubApiService->getPullDetails($encryptedToken, $repo, $prNumber);
        $diffResult = $this->githubApiService->getPullDiff($encryptedToken, $repo, $prNumber);
        $issueCommentsResult = $this->githubApiService->getPullIssueComments($encryptedToken, $repo, $prNumber);
        $reviewCommentsResult = $this->githubApiService->getPullReviewComments($encryptedToken, $repo, $prNumber);

        $details = $detailsResult['ok'] ? $detailsResult['data'] : [];
        $fullDiff = $diffResult['ok'] ? (string) $diffResult['data'] : (string) ($fallbackDiffText ?? '');

        return [
            'repo' => $repo,
            'pr_number' => (int) $prNumber,
            'details' => $details,
            'diff_full' => $fullDiff,
            'diff_changes_only' => $this->extractChangesOnlyDiff($fullDiff),
            'issue_comments' => $issueCommentsResult['ok'] ? $issueCommentsResult['data'] : [],
            'review_comments' => $reviewCommentsResult['ok'] ? $reviewCommentsResult['data'] : [],
        ];
    }

    // Builds AI context from uploaded/editor diff when no PR is selected.
    public function buildFromRawDiff(?string $diffText): array
    {
        $fullDiff = (string) ($diffText ?? '');

        return [
            'repo' => null,
            'pr_number' => null,
            'details' => [],
            'diff_full' => $fullDiff,
            'diff_changes_only' => $this->extractChangesOnlyDiff($fullDiff),
            'issue_comments' => [],
            'review_comments' => [],
        ];
    }

    // Keeps only file/hunk headers and added/removed lines for focused analysis.
    private function extractChangesOnlyDiff(string $diffText): string
    {
        if (trim($diffText) === '') {
            return '';
        }

        $kept = [];

        foreach (preg_split('/\R/', $diffText) as $line) {
            if (
                str_starts_with($line, 'diff --git ') ||
                str_starts_with($line, '--- ') ||
                str_starts_with($line, '+++ ') ||
                str_starts_with($line, '@@') ||
                str_starts_with($line, '+') ||
                str_starts_with($line, '-')
            ) {
                $kept[] = $line;
            }
        }

        return implode("\n", $kept);
    }
}

