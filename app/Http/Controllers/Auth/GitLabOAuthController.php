<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GitLabOAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('gitlab')
            ->scopes(['read_user', 'read_api', 'read_repository'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $gitlabUser = Socialite::driver('gitlab')->stateless()->user();
            $baseUrl = rtrim((string) config('services.gitlab.instance_uri', 'https://gitlab.com'), '/');

            if (Auth::check()) {
                $user = Auth::user();
                $user->update([
                    'gitlab_id' => (string) $gitlabUser->id,
                    'gitlab_username' => $gitlabUser->nickname,
                    'gitlab_avatar_url' => $gitlabUser->avatar,
                    'gitlab_base_url' => $baseUrl,
                    'gitlab_access_token' => Crypt::encryptString($gitlabUser->token),
                    'gitlab_refresh_token' => $gitlabUser->refreshToken
                        ? Crypt::encryptString($gitlabUser->refreshToken)
                        : null,
                    'gitlab_token_expires_at' => $gitlabUser->expiresIn
                        ? now()->addSeconds($gitlabUser->expiresIn)
                        : null,
                ]);

                return redirect()->route('auditor.index')->with('vcs_connection_message', 'GitLab connected successfully!');
            }

            $user = User::where('gitlab_id', (string) $gitlabUser->id)
                ->orWhere('email', $gitlabUser->email)
                ->first();

            if ($user) {
                $user->update([
                    'gitlab_id' => (string) $gitlabUser->id,
                    'gitlab_username' => $gitlabUser->nickname,
                    'gitlab_avatar_url' => $gitlabUser->avatar,
                    'gitlab_base_url' => $baseUrl,
                    'gitlab_access_token' => Crypt::encryptString($gitlabUser->token),
                    'gitlab_refresh_token' => $gitlabUser->refreshToken
                        ? Crypt::encryptString($gitlabUser->refreshToken)
                        : null,
                    'gitlab_token_expires_at' => $gitlabUser->expiresIn
                        ? now()->addSeconds($gitlabUser->expiresIn)
                        : null,
                ]);
            } else {
                $user = User::create([
                    'gitlab_id' => (string) $gitlabUser->id,
                    'name' => $gitlabUser->name ?: $gitlabUser->nickname ?: 'GitLab User',
                    'email' => $gitlabUser->email,
                    'password' => Hash::make(Str::random(32)),
                    'gitlab_username' => $gitlabUser->nickname,
                    'gitlab_avatar_url' => $gitlabUser->avatar,
                    'gitlab_base_url' => $baseUrl,
                    'gitlab_access_token' => Crypt::encryptString($gitlabUser->token),
                    'gitlab_refresh_token' => $gitlabUser->refreshToken
                        ? Crypt::encryptString($gitlabUser->refreshToken)
                        : null,
                    'gitlab_token_expires_at' => $gitlabUser->expiresIn
                        ? now()->addSeconds($gitlabUser->expiresIn)
                        : null,
                ]);
            }

            Auth::login($user, true);

            return redirect()->route('auditor.index');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->with('auth_error', 'GitLab sign-in could not be completed. Please try again.');
        }
    }
}
