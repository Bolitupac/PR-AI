<?php

namespace App\Services\Vcs;

class UnifiedDiffBuilder
{
    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    public function fromEntries(array $entries): string
    {
        $chunks = [];

        foreach ($entries as $entry) {
            $oldPath = $this->normalizePath((string) ($entry['old_path'] ?? $entry['path'] ?? ''));
            $newPath = $this->normalizePath((string) ($entry['new_path'] ?? $entry['path'] ?? ''));
            $diff = (string) ($entry['diff'] ?? '');
            $newFile = (bool) ($entry['new_file'] ?? false);
            $deletedFile = (bool) ($entry['deleted_file'] ?? false);

            if ($oldPath === '' && $newPath === '') {
                continue;
            }

            $from = $newFile ? '/dev/null' : 'a/'.($oldPath !== '' ? $oldPath : $newPath);
            $to = $deletedFile ? '/dev/null' : 'b/'.($newPath !== '' ? $newPath : $oldPath);
            $displayOld = $oldPath !== '' ? $oldPath : $newPath;
            $displayNew = $newPath !== '' ? $newPath : $oldPath;

            $header = [
                sprintf('diff --git a/%s b/%s', $displayOld, $displayNew),
                sprintf('--- %s', $from),
                sprintf('+++ %s', $to),
            ];

            $body = rtrim($diff, "\n");
            if ($body === '') {
                $body = '@@ -0,0 +0,0 @@';
            }

            $chunks[] = implode("\n", $header)."\n".$body;
        }

        return implode("\n", $chunks);
    }

    public function normalizePath(string $path): string
    {
        return ltrim($path, '/');
    }
}
