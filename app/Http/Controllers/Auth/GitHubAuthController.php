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
}
