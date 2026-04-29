<?php

namespace App\Http\Controllers\Vcs;

use App\Http\Controllers\Controller;
use App\Services\Vcs\VcsConnectionStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VcsConnectionController extends Controller
{
    public function __construct(private readonly VcsConnectionStore $vcsConnectionStore)
    {
    }

    public function store(Request $request, string $provider): RedirectResponse
    {
        if (!in_array($provider, ['gitlab', 'bitbucket', 'azure'], true)) {
            abort(404);
        }

        $payload = match ($provider) {
            'gitlab' => $request->validate([
                'token' => ['required', 'string', 'max:500'],
                'base_url' => ['nullable', 'url', 'max:255'],
                'username' => ['nullable', 'string', 'max:255'],
            ]),
            'bitbucket' => $request->validate([
                'token' => ['required', 'string', 'max:500'],
                'workspace' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255'],
            ]),
            'azure' => $request->validate([
                'token' => ['required', 'string', 'max:500'],
                'organization' => ['required', 'string', 'max:255'],
                'project' => ['required', 'string', 'max:255'],
                'username' => ['nullable', 'string', 'max:255'],
            ]),
        };

        $connection = ['token' => trim((string) $payload['token'])];

        if ($provider === 'gitlab') {
            $connection['base_url'] = trim((string) ($payload['base_url'] ?? 'https://gitlab.com')) ?: 'https://gitlab.com';
            $connection['username'] = trim((string) ($payload['username'] ?? ''));
        }

        if ($provider === 'bitbucket') {
            $connection['workspace'] = trim((string) $payload['workspace']);
            $connection['username'] = trim((string) $payload['username']);
        }

        if ($provider === 'azure') {
            $connection['organization'] = trim((string) $payload['organization']);
            $connection['project'] = trim((string) $payload['project']);
            $connection['username'] = trim((string) ($payload['username'] ?? ''));
        }

        $this->vcsConnectionStore->put($provider, $connection, $request);

        return back()->with('vcs_connection_message', ucfirst($provider).' connection saved.');
    }

    public function destroy(Request $request, string $provider): RedirectResponse
    {
        if (!in_array($provider, ['gitlab', 'bitbucket', 'azure'], true)) {
            abort(404);
        }

        $this->vcsConnectionStore->forget($provider, $request);

        return back()->with('vcs_connection_message', ucfirst($provider).' connection removed.');
    }
}
