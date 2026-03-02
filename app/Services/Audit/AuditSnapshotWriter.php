<?php

namespace App\Services\Audit;

use Illuminate\Support\Facades\Storage;

class AuditSnapshotWriter
{
    // Writes snapshot text to storage/app/audit/newfile.txt.
    public function write(string $content): string
    {
        $relativePath = 'audit/newfile.txt';
        Storage::disk('local')->put($relativePath, $content);

        return Storage::disk('local')->path($relativePath);
    }

    // Builds a readable debug snapshot from source data.
    public function buildContent(array $payload): string
    {
        $lines = [];
        $lines[] = '=== PR-AI AUDIT SNAPSHOT ===';
        $lines[] = 'timestamp: '.now()->toIso8601String();
        $lines[] = 'source: '.($payload['source'] ?? 'unknown');
        $lines[] = 'repo: '.($payload['repo'] ?? 'N/A');
        $lines[] = 'pr_number: '.($payload['pr_number'] ?? 'N/A');
        $lines[] = 'uploaded_file: '.($payload['file_name'] ?? 'N/A');
        $lines[] = '';
        $lines[] = '--- CHANGED LINES (with numbers) ---';

        $changedLines = $this->extractChangedLines((string) ($payload['diff_text'] ?? ''));
        if (empty($changedLines)) {
            $lines[] = 'No changed lines parsed from diff.';
        } else {
            foreach ($changedLines as $entry) {
                $lines[] = sprintf(
                    '%s | %s | %s line %s | %s',
                    $entry['file'],
                    $entry['type'],
                    $entry['side'],
                    $entry['line'],
                    $entry['content']
                );
            }
        }

        $lines[] = '';
        $lines[] = '--- PR COMMENTS ---';
        $issueComments = $payload['issue_comments'] ?? [];
        if (empty($issueComments)) {
            $lines[] = 'No PR comments.';
        } else {
            foreach ($issueComments as $comment) {
                $lines[] = sprintf(
                    '@%s | %s | %s',
                    $comment['author'] ?? 'unknown',
                    $comment['updated_at'] ?? 'unknown-time',
                    preg_replace('/\s+/', ' ', trim((string) ($comment['body'] ?? '')))
                );
            }
        }

        $lines[] = '';
        $lines[] = '--- LINE COMMENTS ---';
        $reviewComments = $payload['review_comments'] ?? [];
        if (empty($reviewComments)) {
            $lines[] = 'No line comments.';
        } else {
            foreach ($reviewComments as $comment) {
                $lines[] = sprintf(
                    '@%s | %s | %s:%s | %s',
                    $comment['author'] ?? 'unknown',
                    $comment['updated_at'] ?? 'unknown-time',
                    $comment['path'] ?? 'unknown-file',
                    $comment['line'] ?? 'n/a',
                    preg_replace('/\s+/', ' ', trim((string) ($comment['body'] ?? '')))
                );
            }
        }

        $lines[] = '';
        $lines[] = '--- SYSTEM PROMPT ---';
        $lines[] = (string) ($payload['prompt_system'] ?? 'N/A');

        $lines[] = '';
        $lines[] = '--- USER PROMPT ---';
        $lines[] = (string) ($payload['prompt_user'] ?? 'N/A');

        $lines[] = '';
        $lines[] = '--- AI RESPONSE ---';
        $lines[] = (string) ($payload['ai_response'] ?? 'N/A');

        $lines[] = '';
        $lines[] = '--- RAW DIFF ---';
        $lines[] = (string) ($payload['diff_text'] ?? '');
        $lines[] = '';
        $lines[] = '=== END SNAPSHOT ===';

        return implode(PHP_EOL, $lines);
    }

    // Parses unified diff and extracts only added/removed lines with line numbers.
    public function extractChangedLines(string $diffText): array
    {
        $rows = preg_split("/\r\n|\n|\r/", $diffText) ?: [];
        $results = [];
        $currentFile = 'unknown-file';
        $oldLine = 0;
        $newLine = 0;

        foreach ($rows as $line) {
            if (str_starts_with($line, 'diff --git')) {
                if (preg_match('/^diff --git a\/(.+?) b\/(.+)$/', $line, $m)) {
                    $currentFile = $m[2];
                }
                $oldLine = 0;
                $newLine = 0;
                continue;
            }

            if (str_starts_with($line, '@@')) {
                if (preg_match('/@@ -(\d+)(?:,\d+)? \+(\d+)(?:,\d+)? @@/', $line, $m)) {
                    $oldLine = (int) $m[1];
                    $newLine = (int) $m[2];
                }
                continue;
            }

            if (str_starts_with($line, '+++') || str_starts_with($line, '---') || $line === '\ No newline at end of file') {
                continue;
            }

            if (str_starts_with($line, '+')) {
                $results[] = [
                    'file' => $currentFile,
                    'type' => 'added',
                    'side' => 'new',
                    'line' => $newLine,
                    'content' => substr($line, 1),
                ];
                $newLine++;
                continue;
            }

            if (str_starts_with($line, '-')) {
                $results[] = [
                    'file' => $currentFile,
                    'type' => 'removed',
                    'side' => 'old',
                    'line' => $oldLine,
                    'content' => substr($line, 1),
                ];
                $oldLine++;
                continue;
            }

            if (str_starts_with($line, ' ')) {
                $oldLine++;
                $newLine++;
            }
        }

        return $results;
    }
}
