<?php

namespace App\Services\Git;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class RepoContextService
{
    public function resolveRepoName(): string
    {
        $repo = $this->resolveGithubRepoFullName();

        if ($repo) {
            $parts = explode('/', $repo);
            $name = end($parts);

            if (is_string($name) && $name !== '') {
                return $name;
            }
        }

        return basename((string) base_path()) ?: 'repo';
    }

    public function resolveGithubRepoFullName(): ?string
    {
        return Cache::remember('imports.github_repo_full_name', now()->addMinutes(10), function () {
            if (!is_dir(base_path('.git'))) {
                return null;
            }

            $process = new Process(['git', 'remote', 'get-url', 'origin']);
            $process->setWorkingDirectory(base_path());
            $process->setTimeout(2);
            $process->run();

            if (!$process->isSuccessful()) {
                return null;
            }

            $url = trim($process->getOutput());
            if ($url === '') {
                return null;
            }

            $normalized = preg_replace('/\.git$/', '', $url) ?? $url;

            if (str_contains($normalized, ':') && !str_contains($normalized, '://')) {
                $normalized = explode(':', $normalized, 2)[1] ?? $normalized;
            }

            if (str_contains($normalized, 'github.com/')) {
                $normalized = explode('github.com/', $normalized, 2)[1] ?? $normalized;
            }

            $normalized = trim($normalized, '/');

            if (!str_contains($normalized, '/')) {
                return null;
            }

            [$owner, $repo] = array_pad(explode('/', $normalized, 3), 2, null);

            if (!is_string($owner) || !is_string($repo) || $owner === '' || $repo === '') {
                return null;
            }

            return "{$owner}/{$repo}";
        });
    }
}
