<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

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
                'body' => $comment['body'] ?? '',
                'updated_at' => $comment['updated_at'] ?? null,
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $comments];
    }

    /**
     * Fetches unified diff text comparing two branches.
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

        return ['ok' => true, 'status' => 200, 'data' => $response->body()];
    }
}
