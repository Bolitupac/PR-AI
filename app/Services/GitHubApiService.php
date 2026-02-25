<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class GitHubApiService
{
    // Fetches current user's repositories from GitHub.
    public function getRepos(string $encryptedToken): array
    {
        $token = Crypt::decryptString($encryptedToken);
        $response = Http::withToken($token)->get('https://api.github.com/user/repos', [
            'per_page' => 100,
            'sort' => 'updated',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $repos = collect($response->json())
            ->map(fn ($repo) => [
                'name' => $repo['name'] ?? '',
                'full_name' => $repo['full_name'] ?? '',
            ])
            ->values()
            ->all();

        return ['ok' => true, 'status' => 200, 'data' => $repos];
    }

    // Fetches open pull requests for a selected repository.
    public function getPullRequests(string $encryptedToken, string $repo): array
    {
        $token = Crypt::decryptString($encryptedToken);
        $response = Http::withToken($token)->get("https://api.github.com/repos/{$repo}/pulls", [
            'state' => 'open',
            'per_page' => 100,
            'sort' => 'updated',
            'direction' => 'desc',
        ]);

        if ($response->failed()) {
            return ['ok' => false, 'status' => $response->status(), 'data' => []];
        }

        $pulls = collect($response->json())
            ->map(fn ($pr) => [
                'number' => $pr['number'] ?? null,
                'title' => $pr['title'] ?? '',
                'state' => $pr['state'] ?? '',
                'html_url' => $pr['html_url'] ?? '',
                'updated_at' => $pr['updated_at'] ?? null,
                'author' => $pr['user']['login'] ?? '',
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
}
