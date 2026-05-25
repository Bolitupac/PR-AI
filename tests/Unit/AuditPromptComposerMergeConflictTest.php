<?php

namespace Tests\Unit;

use App\Services\Ai\AuditPromptComposer;
use PHPUnit\Framework\TestCase;

class AuditPromptComposerMergeConflictTest extends TestCase
{
    public function test_metadata_only_merge_conflict_prompt_does_not_require_hunks(): void
    {
        $composer = new AuditPromptComposer();
        $prompt = $composer->compose([
            'audit_kind' => 'merge_conflict_audit',
            'repo' => 'org/repo',
            'pr_number' => '42',
            'base_branch' => 'main',
            'head_branch' => 'feature',
            'audit_title' => 'Merge conflict audit',
            'pr_title' => 'Conflicting change',
            'diff_text' => 'MERGE CONFLICT AUDIT (metadata only)',
            'conflict_payload' => [
                'conflict_source' => 'github_metadata_only',
                'has_hunks' => false,
                'files' => [],
                'mergeable_state' => 'dirty',
            ],
        ]);

        $this->assertStringContainsString('metadata_only', $prompt);
        $this->assertStringContainsString('Do NOT invent file paths', $prompt);
        $this->assertStringContainsString('<<<<<<< marker blocks', $prompt);
        $this->assertStringContainsString('[AGENT_FIX_PROMPT]', $prompt);
    }

    public function test_hunk_based_merge_conflict_prompt_lists_files_from_payload(): void
    {
        $composer = new AuditPromptComposer();
        $prompt = $composer->compose([
            'audit_kind' => 'merge_conflict_audit',
            'repo' => 'group/project',
            'pr_number' => '7',
            'diff_text' => 'diff --git a/a.txt b/a.txt',
            'conflict_payload' => [
                'conflict_source' => 'gitlab_api_hunks',
                'has_hunks' => true,
                'files' => [
                    ['path' => 'a.txt', 'hunks' => [['start_line' => 1, 'end_line' => 5]]],
                ],
            ],
        ]);

        $this->assertStringContainsString('provider_hunks', $prompt);
        $this->assertStringContainsString('List every conflicted file', $prompt);
        $this->assertStringNotContainsString('Do NOT invent file paths', $prompt);
    }

    public function test_standard_audit_includes_merge_conflict_risk_section(): void
    {
        $composer = new AuditPromptComposer();
        $prompt = $composer->compose([
            'audit_kind' => 'pull_request_audit',
            'diff_text' => 'diff --git a/x b/x',
            'changed_lines' => [],
        ]);

        $this->assertStringContainsString('Merge Conflict Risk', $prompt);
        $this->assertStringContainsString('[AGENT_FIX_PROMPT]', $prompt);
    }
}
