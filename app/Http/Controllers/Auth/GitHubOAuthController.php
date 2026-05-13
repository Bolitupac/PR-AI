<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;
use Laravel\Socialite\Facades\Socialite;

class GitHubOAuthController extends Controller
{
    // Redirects user to GitHub OAuth with required scopes.
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['read:user', 'repo'])
            ->redirect();
    }

    // Handles OAuth callback, upserts local user, and logs them in.
    public function callback(): RedirectResponse
    {
        try {
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

            return redirect()->route('auditor.index');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->with('auth_error', 'GitHub sign-in could not be completed. Please try again.');
        }
    }
}
