<?php

namespace App\Services\Ai;

class AuditPromptComposer
{
    // Builds a compact but informative user prompt for PR auditing.
    public function compose(array $input): string
    {
        $source = (string) ($input['source'] ?? 'unknown');
        $repo = (string) ($input['repo'] ?? 'N/A');
        $prNumber = (string) ($input['pr_number'] ?? 'N/A');
        $fileName = (string) ($input['file_name'] ?? 'N/A');
        $changedLines = (array) ($input['changed_lines'] ?? []);
        $issueComments = (array) ($input['issue_comments'] ?? []);
        $reviewComments = (array) ($input['review_comments'] ?? []);
        $diffText = (string) ($input['diff_text'] ?? '');

        $parts = [];
        $parts[] = 'SOURCE: '.$source;
        $parts[] = 'REPO: '.$repo;
        $parts[] = 'PR NUMBER: '.$prNumber;
        $parts[] = 'FILE NAME: '.$fileName;
        $parts[] = '';
        $parts[] = 'CHANGED LINES (file | type | side line | content):';

        if (empty($changedLines)) {
            $parts[] = '- No changed lines parsed.';
        } else {
            foreach (array_slice($changedLines, 0, 700) as $line) {
                $parts[] = sprintf(
                    '- %s | %s | %s line %s | %s',
                    $line['file'] ?? 'unknown-file',
                    $line['type'] ?? 'changed',
                    $line['side'] ?? 'new',
                    $line['line'] ?? 'n/a',
                    $line['content'] ?? ''
                );
            }
        }

        $parts[] = '';
        $parts[] = 'PR COMMENTS:';
        if (empty($issueComments)) {
            $parts[] = '- No PR comments.';
        } else {
            foreach (array_slice($issueComments, 0, 200) as $comment) {
                $parts[] = sprintf(
                    '- @%s (%s): %s',
                    $comment['author'] ?? 'unknown',
                    $comment['updated_at'] ?? 'unknown-time',
                    preg_replace('/\s+/', ' ', trim((string) ($comment['body'] ?? '')))
                );
            }
        }

        $parts[] = '';
        $parts[] = 'LINE COMMENTS:';
        if (empty($reviewComments)) {
            $parts[] = '- No line comments.';
        } else {
            foreach (array_slice($reviewComments, 0, 250) as $comment) {
                $parts[] = sprintf(
                    '- @%s (%s) at %s:%s -> %s',
                    $comment['author'] ?? 'unknown',
                    $comment['updated_at'] ?? 'unknown-time',
                    $comment['path'] ?? 'unknown-file',
                    $comment['line'] ?? 'n/a',
                    preg_replace('/\s+/', ' ', trim((string) ($comment['body'] ?? '')))
                );
            }
        }

        $parts[] = '';
        $parts[] = 'RAW DIFF:';
        $parts[] = $diffText;

        return implode(PHP_EOL, $parts);
    }

    // Builds compact context for follow-up chat after an audit run.
    public function composeChatContext(array $input): string
    {
        $source = (string) ($input['source'] ?? 'unknown');
        $repo = (string) ($input['repo'] ?? 'N/A');
        $prNumber = (string) ($input['pr_number'] ?? 'N/A');
        $fileName = (string) ($input['file_name'] ?? 'N/A');
        $changedLines = (array) ($input['changed_lines'] ?? []);
        $issueComments = (array) ($input['issue_comments'] ?? []);
        $reviewComments = (array) ($input['review_comments'] ?? []);

        $parts = [];
        $parts[] = "SOURCE: {$source}";
        $parts[] = "REPO: {$repo}";
        $parts[] = "PR: {$prNumber}";
        $parts[] = "FILE: {$fileName}";
        $parts[] = '';
        $parts[] = 'CHANGED LINES (sample):';

        if (empty($changedLines)) {
            $parts[] = '- none parsed';
        } else {
            foreach (array_slice($changedLines, 0, 220) as $line) {
                $parts[] = sprintf(
                    '- %s:%s (%s) %s',
                    $line['file'] ?? 'unknown-file',
                    $line['line'] ?? 'n/a',
                    $line['type'] ?? 'changed',
                    $line['content'] ?? ''
                );
            }
        }

        $parts[] = '';
        $parts[] = 'COMMENTS (sample):';

        foreach (array_slice($issueComments, 0, 40) as $comment) {
            $parts[] = sprintf(
                '- @%s: %s',
                $comment['author'] ?? 'unknown',
                preg_replace('/\s+/', ' ', trim((string) ($comment['body'] ?? '')))
            );
        }

        foreach (array_slice($reviewComments, 0, 60) as $comment) {
            $parts[] = sprintf(
                '- @%s at %s:%s -> %s',
                $comment['author'] ?? 'unknown',
                $comment['path'] ?? 'unknown-file',
                $comment['line'] ?? 'n/a',
                preg_replace('/\s+/', ' ', trim((string) ($comment['body'] ?? '')))
            );
        }

        $context = implode(PHP_EOL, $parts);

        // Keep session payload small enough for reliable storage.
        return mb_substr($context, 0, 28000);
    }
}
