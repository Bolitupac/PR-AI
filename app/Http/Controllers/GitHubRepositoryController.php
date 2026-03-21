<?php

namespace App\Http\Controllers;

use App\Services\GitHubApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class GitHubRepositoryController extends Controller
{
    public function __construct(private readonly GitHubApiService $githubApiService)
    {
    }

    // Returns repository list for the authenticated user.
    public function repos(): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->github_access_token) {
            return response()->json(['repos' => []], 401);
        }

        $result = $this->githubApiService->getRepos($user->github_access_token);
        if (!$result['ok']) {
            return response()->json(['repos' => []], $result['status']);
        }

        return response()->json(['repos' => $result['data']]);
    }

    // Returns branches for the selected repository.
    public function branches(): JsonResponse
    {
        $user = Auth::user();
        $repo = request()->query('repo');

        if (!$user || !$user->github_access_token) {
            return response()->json(['branches' => []], 401);
        }

        if (!$repo || !str_contains($repo, '/')) {
            return response()->json(['branches' => []], 422);
        }

        $result = $this->githubApiService->getBranches($user->github_access_token, $repo);
        if (!$result['ok']) {
            return response()->json(['branches' => []], $result['status']);
        }

        return response()->json(['branches' => $result['data']]);
    }

    // Returns open pull requests for the selected repository.
    public function pullRequests(): JsonResponse
    {
        $user = Auth::user();
        $repo = request()->query('repo');

        if (!$user || !$user->github_access_token) {
            return response()->json(['pulls' => []], 401);
        }

        if (!$repo || !str_contains($repo, '/')) {
            return response()->json(['pulls' => []], 422);
        }

        $result = $this->githubApiService->getPullRequests($user->github_access_token, $repo);
        if (!$result['ok']) {
            return response()->json(['pulls' => []], $result['status']);
        }

        return response()->json(['pulls' => $result['data']]);
    }

    public function recentPullRequests(): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->github_access_token || !$user->github_username) {
            return response()->json(['pulls' => []], 401);
        }

        $result = $this->githubApiService->getRecentAccountPullRequests(
            $user->github_access_token,
            $user->github_username,
            10,
        );

        if (!$result['ok']) {
            return response()->json(['pulls' => []], $result['status']);
        }

        return response()->json(['pulls' => $result['data']]);
    }

    public function pullComments(): JsonResponse
    {
        $user = Auth::user();
        $repo = request()->query('repo');
        $prNumber = request()->query('pr_number');

        if (!$user || !$user->github_access_token) {
            return response()->json(['comments' => []], 401);
        }

        if (!$repo || !str_contains($repo, '/')) {
            return response()->json(['comments' => []], 422);
        }

        if (!$prNumber || !is_numeric((string) $prNumber)) {
            return response()->json(['comments' => []], 422);
        }

        $issueResult = $this->githubApiService->getPullIssueComments($user->github_access_token, $repo, (string) $prNumber);
        $reviewResult = $this->githubApiService->getPullReviewComments($user->github_access_token, $repo, (string) $prNumber);

        $comments = collect($issueResult['ok'] ? ($issueResult['data'] ?? []) : [])
            ->map(fn (array $comment) => [
                'kind' => 'discussion',
                'author' => $comment['author'] ?? '',
                'body' => $comment['body'] ?? '',
                'updated_at' => $comment['updated_at'] ?? null,
                'path' => null,
                'line' => null,
            ])
            ->concat(
                collect($reviewResult['ok'] ? ($reviewResult['data'] ?? []) : [])
                    ->map(fn (array $comment) => [
                        'kind' => 'review',
                        'author' => $comment['author'] ?? '',
                        'body' => $comment['body'] ?? '',
                        'updated_at' => $comment['updated_at'] ?? null,
                        'path' => $comment['path'] ?? null,
                        'line' => $comment['original_side'] === 'LEFT'
                            ? ($comment['original_line'] ?? $comment['line'] ?? null)
                            : ($comment['line'] ?? $comment['original_line'] ?? null),
                        'side' => $comment['original_side'] ?? $comment['side'] ?? 'RIGHT',
                    ])
            )
            ->sortByDesc(fn (array $comment) => $comment['updated_at'] ?? '')
            ->values()
            ->all();

        return response()->json(['comments' => $comments]);
    }

    // Returns lightweight metadata (branch + PR counts) for a repository.
    public function metadata(): JsonResponse
    {
        $user = Auth::user();
        $repo = request()->query('repo');

        if (!$user || !$user->github_access_token) {
            return response()->json(['ok' => false], 401);
        }

        if (!$repo || !str_contains($repo, '/')) {
            return response()->json(['ok' => false], 422);
        }

        $branches = $this->githubApiService->getBranches($user->github_access_token, $repo);
        $pulls = $this->githubApiService->getPullRequests($user->github_access_token, $repo);

        return response()->json([
            'ok' => true,
            'data' => [
                'branch_count' => count($branches['data'] ?? []),
                'pull_count' => count($pulls['data'] ?? []),
            ],
        ]);
    }

    // Returns raw diff text for a selected pull request.
    public function pullDiff(): Response|JsonResponse
    {
        $user = Auth::user();
        $repo = request()->query('repo');
        $prNumber = request()->query('pr_number');

        if (!$user || !$user->github_access_token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$repo || !str_contains($repo, '/')) {
            return response()->json(['message' => 'Invalid repo'], 422);
        }

        if (!$prNumber || !is_numeric((string) $prNumber)) {
            return response()->json(['message' => 'Invalid PR number'], 422);
        }

        $result = $this->githubApiService->getPullDiff($user->github_access_token, $repo, (string) $prNumber);
        if (!$result['ok']) {
            return response()->json(['message' => 'Failed to load diff'], $result['status']);
        }

        return response($result['data'], 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    /**
     * Returns raw diff text comparing two branches.
     */
    public function branchDiff(): Response|JsonResponse
    {
        $user = Auth::user();
        $repo = request()->query('repo');
        $base = request()->query('base');
        $head = request()->query('head');

        if (!$user || !$user->github_access_token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$repo || !str_contains($repo, '/')) {
            return response()->json(['message' => 'Invalid repo'], 422);
        }

        if (!$base || !$head) {
            return response()->json(['message' => 'Base and head branches are required'], 422);
        }

        $result = $this->githubApiService->getBranchDiff($user->github_access_token, $repo, $base, $head);
        if (!$result['ok']) {
            return response()->json(['message' => 'Failed to load branch diff'], $result['status']);
        }

        return response($result['data'], 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
