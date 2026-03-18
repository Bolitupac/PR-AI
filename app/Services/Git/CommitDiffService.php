<?php

namespace App\Services\Git;

use Symfony\Component\Process\Process;

class CommitDiffService
{
    public function getCommitDiff(string $commitHash): array
    {
        if (!is_dir(base_path('.git'))) {
            return ['ok' => false, 'status' => 404, 'data' => ''];
        }

        $process = new Process([
            'git',
            '--no-pager',
            'show',
            '--format=',
            '--no-ext-diff',
            '--unified=3',
            $commitHash,
            '--',
        ]);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(4);
        $process->run();

        if (!$process->isSuccessful()) {
            return ['ok' => false, 'status' => 422, 'data' => ''];
        }

        return ['ok' => true, 'status' => 200, 'data' => $process->getOutput()];
    }
}
