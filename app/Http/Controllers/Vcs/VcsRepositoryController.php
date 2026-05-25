<?php

namespace App\Http\Controllers\Vcs;

use App\Http\Controllers\Controller;
use App\Services\Vcs\VcsProviderManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;

class VcsRepositoryController extends Controller
{
    public function __construct(private readonly VcsProviderManager $vcsProviderManager)
    {
    }

    public function repos(Request $request, string $provider): JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Log in or connect the provider to load repositories.');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection] = $resolved;
        $result = $service->getRepos($connection);
        if (!$result['ok']) {
            return response()->json([
                'repos' => [],
                'message' => $this->vcsProviderManager->failureMessage($provider, $result['status'], 'Could not load repositories.'),
                'connect_url' => $this->vcsProviderManager->connectTarget($provider),
                'auth_required' => true,
            ], $result['status']);
        }

        return response()->json([
            'repos' => $result['data'],
            'message' => empty($result['data']) ? 'No repositories found for this connection.' : null,
        ]);
    }

    public function branches(Request $request, string $provider): JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Connect the provider to load branches.', 'branches');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection, $repo] = $resolved;
        if ($repo['repo'] === '') {
            return response()->json(['branches' => [], 'message' => 'Select a valid repository first.'], 422);
        }

        $result = $service->getBranches($connection, $repo);
        if (!$result['ok']) {
            return response()->json([
                'branches' => [],
                'message' => $this->vcsProviderManager->failureMessage($provider, $result['status'], 'Could not load branches.'),
                'connect_url' => $this->vcsProviderManager->connectTarget($provider),
                'auth_required' => true,
            ], $result['status']);
        }

        return response()->json(['branches' => $result['data']]);
    }

    public function metadata(Request $request, string $provider): JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Connect the provider to load metadata.');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection, $repo] = $resolved;
        if ($repo['repo'] === '') {
            return response()->json(['ok' => false, 'message' => 'Select a valid repository first.'], 422);
        }

        $branches = $service->getBranches($connection, $repo);
        $pulls = $service->getPullRequests($connection, $repo);

        return response()->json([
            'ok' => true,
            'data' => [
                'branch_count' => count($branches['data'] ?? []),
                'pull_count' => count($pulls['data'] ?? []),
            ],
        ]);
    }

    public function pullRequests(Request $request, string $provider): JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Connect the provider to load pull requests.', 'pulls');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection, $repo] = $resolved;
        if ($repo['repo'] === '') {
            return response()->json(['pulls' => [], 'message' => 'Select a valid repository first.'], 422);
        }

        $result = $service->getPullRequests($connection, $repo);
        if (!$result['ok']) {
            return response()->json([
                'pulls' => [],
                'message' => $this->vcsProviderManager->failureMessage($provider, $result['status'], 'Could not load pull requests.'),
                'connect_url' => $this->vcsProviderManager->connectTarget($provider),
                'auth_required' => true,
            ], $result['status']);
        }

        return response()->json(['pulls' => $result['data']]);
    }

    public function recentPullRequests(Request $request, string $provider): JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Connect the provider to load recent pull requests.', 'pulls');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection] = $resolved;
        $result = $service->getRecentPullRequests($connection, 10);
        if (!$result['ok']) {
            return response()->json([
                'pulls' => [],
                'message' => $this->vcsProviderManager->failureMessage($provider, $result['status'], 'Could not load recent pull requests.'),
                'connect_url' => $this->vcsProviderManager->connectTarget($provider),
                'auth_required' => true,
            ], $result['status']);
        }

        return response()->json(['pulls' => $result['data']]);
    }

    public function recentCommits(Request $request, string $provider): JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Connect the provider to load recent commits.', 'commits');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection] = $resolved;
        $result = $service->getRecentCommits($connection, 15);
        if (!$result['ok']) {
            $status = (int) ($result['status'] ?? 500);
            $payload = [
                'commits' => [],
                'message' => $result['message']
                    ?? $this->vcsProviderManager->failureMessage($provider, $status, 'Could not load recent commits.'),
                'connect_url' => $this->vcsProviderManager->connectTarget($provider),
                'auth_required' => $status === 401,
            ];

            return response()->json($payload, $status);
        }

        return response()->json(['commits' => $result['data']]);
    }

    public function commitDiff(Request $request, string $provider): Response|JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Connect the provider to load commit diffs.');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection, $repo] = $resolved;
        $commit = trim((string) $request->query('commit', ''));

        if ($repo['repo'] === '') {
            return response()->json(['message' => 'Select a valid repository first.'], 422);
        }

        if (!preg_match('/^[0-9a-f]{7,40}$/i', $commit)) {
            return response()->json(['message' => 'Invalid commit hash'], 422);
        }

        $result = $service->getCommitDiff($connection, $repo, $commit);
        if (!$result['ok']) {
            return response()->json([
                'message' => $this->vcsProviderManager->failureMessage($provider, $result['status'], 'Failed to load commit diff.'),
                'connect_url' => $this->vcsProviderManager->connectTarget($provider),
                'auth_required' => true,
            ], $result['status']);
        }

        return response($result['data'], 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function pullComments(Request $request, string $provider): JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Connect the provider to load pull request comments.', 'comments');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection, $repo] = $resolved;
        $pullNumber = trim((string) $request->query('pr_number', ''));
        if ($repo['repo'] === '') {
            return response()->json(['comments' => [], 'message' => 'Select a valid repository first.'], 422);
        }
        if ($pullNumber === '' || !is_numeric($pullNumber)) {
            return response()->json(['comments' => [], 'message' => 'Choose a valid pull request first.'], 422);
        }

        $issueResult = $service->getPullIssueComments($connection, $repo, $pullNumber);
        $reviewResult = $service->getPullReviewComments($connection, $repo, $pullNumber);

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

    public function pullDiff(Request $request, string $provider): Response|JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Connect the provider to load pull request diffs.');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection, $repo] = $resolved;
        $pullNumber = trim((string) $request->query('pr_number', ''));
        if ($repo['repo'] === '') {
            return response()->json(['message' => 'Invalid repo'], 422);
        }
        if ($pullNumber === '' || !is_numeric($pullNumber)) {
            return response()->json(['message' => 'Invalid pull request number'], 422);
        }

        $result = $service->getPullDiff($connection, $repo, $pullNumber);
        if (!$result['ok']) {
            return response()->json([
                'message' => $this->vcsProviderManager->failureMessage($provider, $result['status'], 'Failed to load pull request diff.'),
                'connect_url' => $this->vcsProviderManager->connectTarget($provider),
                'auth_required' => true,
            ], $result['status']);
        }

        return response($result['data'], 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function branchDiff(Request $request, string $provider): Response|JsonResponse
    {
        $resolved = $this->resolveProvider($provider, $request, 'Connect the provider to load branch diffs.');
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$service, $connection, $repo] = $resolved;
        $base = trim((string) $request->query('base', ''));
        $head = trim((string) $request->query('head', ''));
        if ($repo['repo'] === '') {
            return response()->json(['message' => 'Invalid repo'], 422);
        }
        if ($base === '' || $head === '') {
            return response()->json(['message' => 'Base and head branches are required'], 422);
        }

        $result = $service->getBranchDiff($connection, $repo, $base, $head);
        if (!$result['ok']) {
            return response()->json([
                'message' => $this->vcsProviderManager->failureMessage($provider, $result['status'], 'Failed to load branch diff.'),
                'connect_url' => $this->vcsProviderManager->connectTarget($provider),
                'auth_required' => true,
            ], $result['status']);
        }

        return response($result['data'], 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    private function resolveProvider(string $provider, Request $request, string $message, string $payloadKey = 'repos'): array|JsonResponse
    {
        try {
            $service = $this->vcsProviderManager->provider($provider);
        } catch (InvalidArgumentException) {
            return response()->json([$payloadKey => [], 'message' => 'Unsupported VCS provider.'], 404);
        }

        $connection = $this->vcsProviderManager->resolveConnection($provider, $request);
        if (!$connection) {
            return response()->json([
                $payloadKey => [],
                'message' => $message,
                'connect_url' => $this->vcsProviderManager->connectTarget($provider),
                'auth_required' => true,
            ], 401);
        }

        return [$service, $connection, $this->vcsProviderManager->repoPayload($provider, $request)];
    }
}
