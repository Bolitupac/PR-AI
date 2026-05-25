<?php

namespace App\Services\Vcs;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class VcsConnectionStore
{
    public const SESSION_KEY = 'vcs_connections';

    public function get(string $provider, ?User $user, Request $request): ?array
    {
        return match ($provider) {
            'github' => $this->githubConnection($user),
            'gitlab' => $this->gitlabConnection($user, $request),
            default => $this->sessionConnection($provider, $request),
        };
    }

    public function put(string $provider, array $connection, Request $request): void
    {
        if ($provider === 'github') {
            return;
        }

        $connections = $request->session()->get(self::SESSION_KEY, []);
        $connections[$provider] = $connection;
        $request->session()->put(self::SESSION_KEY, $connections);
    }

    public function forget(string $provider, Request $request): void
    {
        if ($provider === 'github') {
            return;
        }

        if ($provider === 'gitlab') {
            $this->forgetGitLabOAuth($request->user());
        }

        $connections = $request->session()->get(self::SESSION_KEY, []);
        unset($connections[$provider]);
        $request->session()->put(self::SESSION_KEY, $connections);
    }

    public function has(string $provider, ?User $user, Request $request): bool
    {
        return $this->get($provider, $user, $request) !== null;
    }

    private function githubConnection(?User $user): ?array
    {
        if (!$user?->github_access_token) {
            return null;
        }

        return [
            'token' => (string) $user->github_access_token,
            'username' => (string) ($user->github_username ?? ''),
            'name' => (string) ($user->name ?? $user->github_username ?? 'GitHub User'),
            'avatar_url' => $user->github_username
                ? sprintf('https://github.com/%s.png', rawurlencode((string) $user->github_username))
                : null,
            'auth' => 'oauth',
        ];
    }

    private function gitlabConnection(?User $user, Request $request): ?array
    {
        $oauth = $this->gitlabOAuthConnection($user);
        if ($oauth !== null) {
            return $oauth;
        }

        $session = $this->sessionConnection('gitlab', $request);
        if ($session === null) {
            return null;
        }

        $session['auth'] = 'pat';

        return $session;
    }

    private function gitlabOAuthConnection(?User $user): ?array
    {
        if (!$user?->gitlab_access_token) {
            return null;
        }

        try {
            $token = Crypt::decryptString((string) $user->gitlab_access_token);
        } catch (\Throwable) {
            return null;
        }

        $baseUrl = trim((string) ($user->gitlab_base_url ?? '')) ?: 'https://gitlab.com';

        return [
            'token' => $token,
            'username' => (string) ($user->gitlab_username ?? ''),
            'name' => (string) ($user->name ?? $user->gitlab_username ?? 'GitLab User'),
            'avatar_url' => $user->gitlab_avatar_url,
            'base_url' => $baseUrl,
            'auth' => 'oauth',
        ];
    }

    private function forgetGitLabOAuth(?User $user): void
    {
        if (!$user) {
            return;
        }

        $user->forceFill([
            'gitlab_id' => null,
            'gitlab_username' => null,
            'gitlab_avatar_url' => null,
            'gitlab_base_url' => null,
            'gitlab_access_token' => null,
            'gitlab_refresh_token' => null,
            'gitlab_token_expires_at' => null,
        ])->save();
    }

    private function sessionConnection(string $provider, Request $request): ?array
    {
        $connection = Arr::get($request->session()->get(self::SESSION_KEY, []), $provider);

        return is_array($connection) && !empty($connection['token']) ? $connection : null;
    }
}
