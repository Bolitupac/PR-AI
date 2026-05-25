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
        $conflictPayload = (array) ($input['conflict_payload'] ?? []);

        if (($input['audit_kind'] ?? '') === 'merge_conflict_audit') {
            return $this->composeMergeConflictAudit($input, $conflictPayload);
        }

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
        $parts[] = '';
        $parts[] = 'MERGE CONFLICT RISK: Always include a section titled "Merge Conflict Risk" with risk level (low, medium, high, or active_conflicts).';
        $parts[] = 'Explain whether merging this change into the base branch could conflict (overlapping edits, same files/lines, incompatible refactors).';
        $parts[] = 'If the raw diff contains <<<<<<< conflict markers, set risk to active_conflicts and explain each affected file and line range.';
        $parts[] = '';
        $parts[] = 'After the visible review, append [AGENT_FIX_PROMPT] JSON with keys: title, prompt, files (array of {path, lines, action}). Close with [/AGENT_FIX_PROMPT].';
        $parts[] = 'The prompt field must be a long, copy-paste-ready instruction for Cursor/Codex including exact code snippets and git commands when fixes are needed.';
        $parts[] = 'Do not mention the AGENT_FIX_PROMPT block in visible prose.';

        return implode(PHP_EOL, $parts);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $conflictPayload
     */
    private function composeMergeConflictAudit(array $input, array $conflictPayload): string
    {
        $repo = (string) ($input['repo'] ?? 'N/A');
        $prNumber = (string) ($input['pr_number'] ?? 'N/A');
        $baseBranch = (string) ($input['base_branch'] ?? 'N/A');
        $headBranch = (string) ($input['head_branch'] ?? 'N/A');
        $auditTitle = (string) ($input['audit_title'] ?? 'Merge conflict audit');
        $prTitle = (string) ($input['pr_title'] ?? 'N/A');
        $diffText = (string) ($input['diff_text'] ?? '');

        $parts = [];
        $parts[] = 'AUDIT KIND: merge_conflict_audit';
        $parts[] = 'AUDIT TITLE: '.$auditTitle;
        $parts[] = 'REPO: '.$repo;
        $parts[] = 'PR/MR NUMBER: '.$prNumber;
        $parts[] = 'PR TITLE: '.$prTitle;
        $parts[] = 'BASE BRANCH: '.$baseBranch;
        $parts[] = 'HEAD BRANCH: '.$headBranch;
        $parts[] = '';
        $parts[] = 'MERGE CONFLICT PAYLOAD (structured):';
        $parts[] = json_encode($conflictPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
        $parts[] = '';
        $parts[] = 'RAW CONFLICT DIFF TEXT:';
        $parts[] = $diffText;
        $parts[] = '';
        $parts[] = 'INSTRUCTIONS:';
        $parts[] = '1. Explain what caused these merge conflicts in plain language (branch divergence, parallel edits, etc.).';
        $parts[] = '2. List every conflicted file with exact line ranges and quote the OURS vs THEIRS snippets from the payload.';
        $parts[] = '3. Provide numbered remediation steps for the human (git merge --abort, fetch, rebase/merge, resolve, add, commit, push).';
        $parts[] = '4. Include a Mermaid flowchart TD diagram showing the conflict resolution flow (base, head, conflict hunks, resolution).';
        $parts[] = '5. Start the visible response with the audit title as the first heading.';
        $parts[] = '';
        $parts[] = 'After the visible review, append [AGENT_FIX_PROMPT] with JSON: {title, prompt, files:[{path, lines, action}]}.';
        $parts[] = 'The prompt must be VERY detailed for an AI coding agent (Cursor/Codex): include repository, branches, per-file code blocks with conflict markers, desired outcome, acceptance criteria, and git commands.';
        $parts[] = 'Close with [/AGENT_FIX_PROMPT]. Do not mention this block in visible prose.';

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
        $base = [
            'FRAMEWORK: Apply OWASP Top 10 (2021) and VAPT methodology throughout. Produce a comprehensive, long-form report — do not truncate or summarise the findings sections.',
            'MANDATORY SECTIONS: Review Status → Executive Summary → OWASP Top 10 Coverage Analysis → VAPT Findings → Impact Map → Logic Flow → Detailed Walkthrough → Remediation Roadmap.',
            'OWASP: Assess ALL 10 categories (A01–A10) even if they are N/A. Populate every row of the OWASP table with a specific note.',
            'VAPT: Group findings as Critical / High / Medium / Low / Informational. Every finding must include Location, Description, Proof of Concept, Impact, and Remediation.',
            'DEPTH: A short report is a failure. Write detailed prose under each section. The VAPT Findings section alone should be comprehensive for any change that touches security-sensitive code.',
        ];

        if ($auditKind === 'commit_audit') {
            return array_merge($base, [
                'AUDIT CONTEXT: This is a historical commit audit — post-change review, not a merge decision.',
                'FOCUS: Regression risk, hidden side effects, rollback concerns, and follow-up fixes.',
                'VAPT FOCUS: Check for secrets accidentally committed, logic regressions that weaken existing security controls, and any new attack surface introduced by this commit.',
                'Do not frame findings as merge/do-not-merge. Use post-deployment risk framing.',
            ]);
        }

        if ($auditKind === 'branch_audit') {
            return array_merge($base, [
                'AUDIT CONTEXT: This is a branch audit before integration with the base branch.',
                'FOCUS: Branch readiness, missing checks, integration risk, and what must change before merge.',
                'VAPT FOCUS: Assess the full attack surface introduced by this branch compared to the base. Treat every new endpoint, middleware bypass, or data access path as a potential VAPT finding.',
                'Pay special attention to A01 (access control) and A05 (security misconfiguration) for branch-level changes.',
            ]);
        }

        if ($auditKind === 'pull_request_audit' && $auditStatus === 'merged') {
            return array_merge($base, [
                'AUDIT CONTEXT: This pull request is already merged — post-merge security review.',
                'FOCUS: Production impact, regression risk, rollout concerns, and follow-up security actions.',
                'VAPT FOCUS: Identify any vulnerabilities that made it to production. Prioritise immediate remediation for Critical and High findings.',
                'Frame findings as production risk and required follow-up actions, not merge decisions.',
            ]);
        }

        if ($auditKind === 'merge_conflict_audit') {
            return [
                'AUDIT CONTEXT: Active merge conflicts must be resolved before integration.',
                'FOCUS: Conflict root cause, affected lines, safe resolution strategy, and agent-ready fix instructions.',
                'Include remediation git commands and warn about force-push risks when relevant.',
            ];
        }

        if ($auditKind === 'pull_request_audit') {
            return array_merge($base, [
                'AUDIT CONTEXT: This is a pull request audit before or during review.',
                'FOCUS: Merge readiness, correctness, security risk, and required fixes before approval.',
                'VAPT FOCUS: Conduct a full white-box penetration test against the changed code. Trace every new data input from source to sink. Check for injection, auth bypass, privilege escalation, and data exposure.',
                'Pay special attention to A03 (injection), A01 (access control), and A07 (auth failures) for PR-level changes touching API or auth code.',
                'Always assess merge conflict risk even if markers are not present.',
            ]);
        }

        return array_merge($base, [
            'AUDIT CONTEXT: General diff audit — treat as a focused security review.',
            'VAPT FOCUS: Apply source-to-sink analysis for any new user input paths. Report all deviations from secure coding standards.',
        ]);
    }
}
