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
        $compareType = (string) ($input['compare_type'] ?? 'N/A');
        $baseBranch = (string) ($input['base_branch'] ?? 'N/A');
        $headBranch = (string) ($input['head_branch'] ?? 'N/A');
        $auditTitle = (string) ($input['audit_title'] ?? 'N/A');
        $auditKind = (string) ($input['audit_kind'] ?? 'N/A');
        $auditStatus = (string) ($input['audit_status'] ?? 'N/A');
        $prTitle = (string) ($input['pr_title'] ?? 'N/A');
        $prDescription = (string) ($input['pr_description'] ?? 'N/A');
        $linkedIssues = (string) ($input['linked_issues'] ?? 'N/A');
        $context = (string) ($input['context'] ?? 'N/A');
        $fileName = (string) ($input['file_name'] ?? 'N/A');
        $changedLines = (array) ($input['changed_lines'] ?? []);
        $issueComments = (array) ($input['issue_comments'] ?? []);
        $reviewComments = (array) ($input['review_comments'] ?? []);
        $diffText = (string) ($input['diff_text'] ?? '');

        $parts = [];
        $parts[] = 'AUDIT TITLE: '.$auditTitle;
        $parts[] = 'AUDIT KIND: '.$auditKind;
        $parts[] = 'AUDIT STATUS: '.$auditStatus;
        $parts[] = 'PR Title: '.$prTitle;
        $parts[] = 'PR Description: '.$prDescription;
        $parts[] = 'Linked Issues: '.$linkedIssues;
        $parts[] = 'Context: '.$context;
        $parts[] = 'Diff Data:';
        $parts[] = '';
        $parts[] = 'SOURCE: '.$source;
        $parts[] = 'REPO: '.$repo;
        $parts[] = 'PR NUMBER: '.$prNumber;
        $parts[] = 'COMPARE TYPE: '.$compareType;
        $parts[] = 'BASE BRANCH: '.$baseBranch;
        $parts[] = 'HEAD BRANCH: '.$headBranch;
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
        $parts[] = '';
        foreach ($this->buildAuditModeInstructions($auditKind, $auditStatus) as $instruction) {
            $parts[] = $instruction;
        }
        $parts[] = '';
        $parts[] = 'OUTPUT REQUIREMENT: Include one Mermaid diagram in a ```mermaid``` code block.';
        $parts[] = 'Use the audit title and audit kind as the canonical label for this review.';
        $parts[] = 'Start the response with the exact audit title as the first heading.';
        $parts[] = 'Choose the best type for the change (sequenceDiagram, flowchart TD, or classDiagram).';
        $parts[] = 'The diagram must show the impact of this change and indicate whether this is a branch vs main compare or a pull request audit.';
        $parts[] = 'After the visible review, append an [INLINE_COMMENTS] JSON block and close it with [/INLINE_COMMENTS].';
        $parts[] = 'The JSON block must be a valid array of up to 8 objects with exactly these keys: path, line, side, body.';
        $parts[] = 'Use only exact file paths and line numbers from CHANGED LINES above.';
        $parts[] = 'Use side RIGHT for new/current code comments and LEFT for old/removed code comments.';
        $parts[] = 'If there are no strong inline suggestions, return an empty array in the INLINE_COMMENTS block.';
        $parts[] = 'Do not mention the INLINE_COMMENTS block anywhere in the visible review prose.';

        return implode(PHP_EOL, $parts);
    }

    // Builds compact context for follow-up chat after an audit run.
    public function composeChatContext(array $input): string
    {
        $source = (string) ($input['source'] ?? 'unknown');
        $repo = (string) ($input['repo'] ?? 'N/A');
        $prNumber = (string) ($input['pr_number'] ?? 'N/A');
        $compareType = (string) ($input['compare_type'] ?? 'N/A');
        $baseBranch = (string) ($input['base_branch'] ?? 'N/A');
        $headBranch = (string) ($input['head_branch'] ?? 'N/A');
        $auditTitle = (string) ($input['audit_title'] ?? 'N/A');
        $auditKind = (string) ($input['audit_kind'] ?? 'N/A');
        $auditStatus = (string) ($input['audit_status'] ?? 'N/A');
        $prTitle = (string) ($input['pr_title'] ?? 'N/A');
        $prDescription = (string) ($input['pr_description'] ?? 'N/A');
        $linkedIssues = (string) ($input['linked_issues'] ?? 'N/A');
        $context = (string) ($input['context'] ?? 'N/A');
        $fileName = (string) ($input['file_name'] ?? 'N/A');
        $changedLines = (array) ($input['changed_lines'] ?? []);
        $issueComments = (array) ($input['issue_comments'] ?? []);
        $reviewComments = (array) ($input['review_comments'] ?? []);

        $parts = [];
        $parts[] = "AUDIT TITLE: {$auditTitle}";
        $parts[] = "AUDIT KIND: {$auditKind}";
        $parts[] = "AUDIT STATUS: {$auditStatus}";
        $parts[] = "PR Title: {$prTitle}";
        $parts[] = "PR Description: {$prDescription}";
        $parts[] = "Linked Issues: {$linkedIssues}";
        $parts[] = "Context: {$context}";
        $parts[] = "SOURCE: {$source}";
        $parts[] = "REPO: {$repo}";
        $parts[] = "PR: {$prNumber}";
        $parts[] = "COMPARE: {$compareType}";
        $parts[] = "BASE: {$baseBranch}";
        $parts[] = "HEAD: {$headBranch}";
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

    private function buildAuditModeInstructions(string $auditKind, string $auditStatus): array
    {
        $instructions = [
            'Keep the output structure consistent across all audit types: Review Status, Summary, Impact Map, Logic Flow, Walkthrough, Key Findings.',
        ];

        if ($auditKind === 'commit_audit') {
            $instructions[] = 'This is a historical commit audit. Treat it as a post-change review, not a merge decision.';
            $instructions[] = 'Focus on regression risk, hidden side effects, rollback concerns, and follow-up fixes.';
            $instructions[] = 'In prose, do not tell the user to merge or not merge this commit.';
            return $instructions;
        }

        if ($auditKind === 'branch_audit') {
            $instructions[] = 'This is a branch audit before integration.';
            $instructions[] = 'Focus on branch readiness, missing checks, integration risk against the base branch, and what should change before merge.';
            return $instructions;
        }

        if ($auditKind === 'pull_request_audit' && $auditStatus === 'merged') {
            $instructions[] = 'This pull request is already merged. Treat it as a post-merge review.';
            $instructions[] = 'Focus on production impact, regression risk, rollout concerns, and follow-up actions.';
            $instructions[] = 'In prose, do not frame the conclusion as merge or do-not-merge.';
            return $instructions;
        }

        if ($auditKind === 'pull_request_audit') {
            $instructions[] = 'This is a pull request audit before or during review.';
            $instructions[] = 'Focus on merge readiness, correctness, risk, and what should be fixed before approval.';
            return $instructions;
        }

        $instructions[] = 'Treat this as a general diff audit and focus on correctness, risk, and next actions.';
        return $instructions;
    }
}
