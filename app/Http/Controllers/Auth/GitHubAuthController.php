<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GitHubAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['read:user', 'repo'])
            ->stateless()
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->stateless()->user();

        $user = User::updateOrCreate(
            ['github_id' => (string) $githubUser->id],
            [
                'name' => $githubUser->name ?: $githubUser->nickname ?: 'GitHub User',
                'email' => $githubUser->email,
                'password' => Hash::make(Str::random(32)),
                'github_username' => $githubUser->nickname,
                'github_access_token' => Crypt::encryptString($githubUser->token),
                'github_refresh_token' => $githubUser->refreshToken
                    ? Crypt::encryptString($githubUser->refreshToken)
                    : null,
                'github_token_expires_at' => $githubUser->expiresIn
                    ? now()->addSeconds($githubUser->expiresIn)
                    : null,
            ]
        );

        Auth::login($user, true);

        return redirect('/auditor');
    }

    public function repos(): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->github_access_token) {
            return response()->json(['repos' => []], 401);
        }

        $token = Crypt::decryptString($user->github_access_token);
        $response = Http::withToken($token)->get('https://api.github.com/user/repos', [
            'per_page' => 100,
            'sort' => 'updated',
        ]);

        if ($response->failed()) {
            return response()->json(['repos' => []], $response->status());
        }

        $repos = collect($response->json())
            ->map(fn ($repo) => [
                'name' => $repo['name'] ?? '',
                'full_name' => $repo['full_name'] ?? '',
            ])
            ->values();

        return response()->json(['repos' => $repos]);
    }

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

        $token = Crypt::decryptString($user->github_access_token);
        $response = Http::withToken($token)->get("https://api.github.com/repos/{$repo}/pulls", [
            'state' => 'open',
            'per_page' => 100,
            'sort' => 'updated',
            'direction' => 'desc',
        ]);

        if ($response->failed()) {
            return response()->json(['pulls' => []], $response->status());
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
            ->values();

        return response()->json(['pulls' => $pulls]);
    }
}
