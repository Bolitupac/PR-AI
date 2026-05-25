<?php

namespace App\Services\Vcs;

class MergeConflictParser
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseHunks(string $content): array
    {
        if ($content === '' || !str_contains($content, '<<<<<<<')) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $hunks = [];
        $current = null;
        $lineNumber = 0;

        foreach ($lines as $line) {
            $lineNumber++;

            if (preg_match('/^<<<<<<<(?:\s+(.*))?$/', $line, $matches)) {
                if ($current !== null) {
                    $hunks[] = $this->finalizeHunk($current);
                }
                $current = [
                    'start_line' => $lineNumber,
                    'end_line' => $lineNumber,
                    'ours_lines' => [],
                    'theirs_lines' => [],
                    'base_lines' => [],
                    'raw_marker_block' => $line."\n",
                    'ours_label' => trim((string) ($matches[1] ?? 'HEAD')),
                    'theirs_label' => '',
                ];
                continue;
            }

            if ($current === null) {
                continue;
            }

            $current['raw_marker_block'] .= $line."\n";
            $current['end_line'] = $lineNumber;

            if ($line === '=======') {
                $current['in_theirs'] = true;
                continue;
            }

            if (preg_match('/^>>>>>>>(?:\s+(.*))?$/', $line, $matches)) {
                $current['theirs_label'] = trim((string) ($matches[1] ?? ''));
                $hunks[] = $this->finalizeHunk($current);
                $current = null;
                continue;
            }

            if (!empty($current['in_theirs'])) {
                $current['theirs_lines'][] = $line;
            } else {
                $current['ours_lines'][] = $line;
            }
        }

        if ($current !== null) {
            $hunks[] = $this->finalizeHunk($current);
        }

        return array_values(array_map(function (array $hunk, int $index) {
            $hunk['index'] = $index + 1;

            return $hunk;
        }, $hunks, array_keys($hunks)));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseFiles(array $files): array
    {
        $parsed = [];

        foreach ($files as $file) {
            $path = (string) ($file['path'] ?? '');
            $content = (string) ($file['content'] ?? '');
            if ($path === '') {
                continue;
            }

            $hunks = $this->parseHunks($content);
            if ($hunks === []) {
                continue;
            }

            $parsed[] = [
                'path' => $path,
                'conflict_count' => count($hunks),
                'hunks' => $hunks,
                'merged_with_markers' => $content,
            ];
        }

        return $parsed;
    }

    /**
     * @param  array<string, mixed>  $hunk
     * @return array<string, mixed>
     */
    private function finalizeHunk(array $hunk): array
    {
        return [
            'start_line' => (int) ($hunk['start_line'] ?? 1),
            'end_line' => (int) ($hunk['end_line'] ?? 1),
            'ours_snippet' => implode("\n", $hunk['ours_lines'] ?? []),
            'theirs_snippet' => implode("\n", $hunk['theirs_lines'] ?? []),
            'base_snippet' => implode("\n", $hunk['base_lines'] ?? []),
            'ours_label' => (string) ($hunk['ours_label'] ?? 'HEAD'),
            'theirs_label' => (string) ($hunk['theirs_label'] ?? ''),
            'raw_marker_block' => rtrim((string) ($hunk['raw_marker_block'] ?? '')),
        ];
    }
}
