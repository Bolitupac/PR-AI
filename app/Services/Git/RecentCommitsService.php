<?php

namespace App\Services\Git;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class RecentCommitsService
{
    public function __construct(private readonly RepoContextService $repoContextService)
    {
    }

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

            $repoName = $this->repoContextService->resolveRepoName();

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
}
