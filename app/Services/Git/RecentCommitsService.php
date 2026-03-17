<?php

namespace App\Services\Git;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class RecentCommitsService
{
    /**
     * @return array<int, array{hash:string,message:string,author:string,repo:string,time:string}>
     */
    public function getRecentCommits(int $limit = 15): array
    {
        $limit = max(1, min(50, $limit));

        return Cache::remember("imports.recent_commits.{$limit}", now()->addSeconds(30), function () use ($limit) {
            if (!is_dir(base_path('.git'))) {
                return [];
            }

            $repoName = $this->resolveRepoName();

            $process = new Process([
                'git',
                '--no-pager',
                'log',
                '-n',
                (string) $limit,
                '--pretty=format:%h%x1f%an%x1f%at%x1f%s',
            ]);
            $process->setWorkingDirectory(base_path());
            $process->setTimeout(2);
            $process->run();

            if (!$process->isSuccessful()) {
                return [];
            }

            $lines = preg_split("/\\r?\\n/", trim($process->getOutput())) ?: [];
            $commits = [];

            foreach ($lines as $line) {
                if ($line === '') {
                    continue;
                }

                $parts = explode("\x1f", $line, 4);
                if (count($parts) !== 4) {
                    continue;
                }

                [$hash, $author, $timestamp, $message] = $parts;

                $time = 'unknown';
                if (is_numeric($timestamp)) {
                    $time = Carbon::createFromTimestamp((int) $timestamp)->diffForHumans();
                }

                $commits[] = [
                    'hash' => $hash,
                    'message' => $message,
                    'author' => $author,
                    'repo' => $repoName,
                    'time' => $time,
                ];
            }

            return $commits;
        });
    }

    private function resolveRepoName(): string
    {
        return Cache::remember('imports.repo_name', now()->addMinutes(10), function () {
            $fallback = basename((string) base_path()) ?: 'repo';

            if (!is_dir(base_path('.git'))) {
                return $fallback;
            }

            $process = new Process(['git', 'remote', 'get-url', 'origin']);
            $process->setWorkingDirectory(base_path());
            $process->setTimeout(2);
            $process->run();

            if (!$process->isSuccessful()) {
                return $fallback;
            }

            $url = trim($process->getOutput());
            if ($url === '') {
                return $fallback;
            }

            // Examples:
            // - git@github.com:Owner/Repo.git
            // - https://github.com/Owner/Repo.git
            // - https://github.com/Owner/Repo
            $normalized = preg_replace('/\\.git$/', '', $url) ?? $url;

            if (str_contains($normalized, ':') && !str_contains($normalized, '://')) {
                // SSH style: git@host:Owner/Repo
                $normalized = explode(':', $normalized, 2)[1] ?? $normalized;
            }

            $normalized = trim($normalized, '/');
            $parts = preg_split('#/#', $normalized) ?: [];
            $repo = end($parts);

            return $repo ? (string) $repo : $fallback;
        });
    }
}
