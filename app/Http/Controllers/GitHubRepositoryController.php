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
}
