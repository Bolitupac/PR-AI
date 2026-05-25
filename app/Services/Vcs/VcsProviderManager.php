<?php

namespace App\Services\Vcs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class VcsProviderManager
{
    /**
     * @var array<string, VcsProviderInterface>
     */
    private array $providers;

    public function __construct(
        GitHubVcsProvider $github,
        GitLabVcsProvider $gitLab,
        BitbucketVcsProvider $bitbucket,
        AzureDevOpsVcsProvider $azure,
        private readonly VcsConnectionStore $connectionStore,
    ) {
        $this->providers = [
            $github->key() => $github,
            $gitLab->key() => $gitLab,
            $bitbucket->key() => $bitbucket,
            $azure->key() => $azure,
        ];
    }

    public function provider(string $provider): VcsProviderInterface
    {
        if (!isset($this->providers[$provider])) {
            throw new InvalidArgumentException('Unsupported VCS provider.');
        }

        return $this->providers[$provider];
    }

    /**
     * @return array<string, VcsProviderInterface>
     */
    public function all(): array
    {
        return $this->providers;
    }

    public function resolveConnection(string $provider, Request $request): ?array
    {
        return $this->connectionStore->get($provider, Auth::user(), $request);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function providerSummaries(Request $request): array
    {
        $user = Auth::user();

        return collect($this->providers)
            ->map(function (VcsProviderInterface $provider, string $key) use ($request, $user) {
                $connection = $this->connectionStore->get($key, $user, $request);
                $profile = $connection ? $provider->getProfile($connection) : [];

                return [
                    'key' => $key,
                    'name' => $provider->label(),
                    'connected' => $connection !== null,
                    'state' => $connection ? 'Connected' : ($key === 'github' ? 'Sign in required' : 'Not connected'),
                    'profile' => $profile,
                    'connection_meta' => $connection ? collect($connection)->except(['token'])->all() : [],
                    'connect_url' => match ($key) {
                        'github' => route('github.redirect'),
                        'gitlab' => route('gitlab.redirect'),
                        default => route('vcs.connections.store', ['provider' => $key]),
                    },
                    'disconnect_url' => match ($key) {
                        'github' => route('logout'),
                        'gitlab' => route('vcs.connections.destroy', ['provider' => 'gitlab']),
                        default => route('vcs.connections.destroy', ['provider' => $key]),
                    },
                ];
            })
            ->values()
            ->all();
    }

    public function defaultProviderKey(Request $request): string
    {
        foreach ($this->providerSummaries($request) as $provider) {
            if (!empty($provider['connected'])) {
                return (string) $provider['key'];
            }
        }

        return 'github';
    }

    /**
     * @return array{provider:string,repo:string,repo_id:?string,project:?string,workspace:?string,organization:?string,repo_slug:?string}
     */
    public function repoPayload(string $provider, Request $request): array
    {
        return [
            'provider' => $provider,
            'repo' => trim((string) $request->input('repo', $request->query('repo', ''))),
            'repo_id' => $this->nullableString($request->input('repo_id', $request->query('repo_id'))),
            'project' => $this->nullableString($request->input('project', $request->query('project'))),
            'workspace' => $this->nullableString($request->input('workspace', $request->query('workspace'))),
            'organization' => $this->nullableString($request->input('organization', $request->query('organization'))),
            'repo_slug' => $this->nullableString($request->input('repo_slug', $request->query('repo_slug'))),
        ];
    }

    public function connectTarget(string $provider): string
    {
        return match ($provider) {
            'github' => route('github.redirect'),
            'gitlab' => route('gitlab.redirect'),
            default => '#settings-vcs',
        };
    }

    public function failureMessage(string $provider, int $status, string $fallback): string
    {
        $label = $this->provider($provider)->label();

        return match ($status) {
            401 => $label.' authorization is invalid or expired. Reconnect and try again.',
            403 => $label.' refused the request. This is usually a scope, permission, or rate-limit issue.',
            404 => $label.' could not find that repository or pull request.',
            default => $fallback,
        };
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
