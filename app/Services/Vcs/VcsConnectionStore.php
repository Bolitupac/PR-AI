<?php

namespace App\Services\Vcs;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class VcsConnectionStore
{
    public const SESSION_KEY = 'vcs_connections';

    public function get(string $provider, ?User $user, Request $request): ?array
    {
        return match ($provider) {
            'github' => $this->githubConnection($user),
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
        ];
    }

    private function sessionConnection(string $provider, Request $request): ?array
    {
        $connection = Arr::get($request->session()->get(self::SESSION_KEY, []), $provider);

        return is_array($connection) && !empty($connection['token']) ? $connection : null;
    }
}
