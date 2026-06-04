<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AuditPromptComposer;
use App\Services\Ai\OpenAiSimpleChatService;
use App\Services\Audit\AuditSnapshotWriter;
use App\Services\Vcs\VcsProviderManager;
use App\Models\ChatConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditDiffController extends Controller
{
    public function __construct(
        private readonly OpenAiSimpleChatService $openAiSimpleChatService,
        private readonly AuditPromptComposer $auditPromptComposer,
        private readonly AuditSnapshotWriter $auditSnapshotWriter,
        private readonly VcsProviderManager $vcsProviderManager
    ) {
    }

    // Audits a selected diff automatically and returns formatted AI output.
    public function audit(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'source' => ['required', 'string', 'in:github,gitlab,bitbucket,azure,upload,editor,import,paste'],
            'repo' => ['nullable', 'string', 'max:255'],
            'repo_id' => ['nullable', 'string', 'max:255'],
            'project' => ['nullable', 'string', 'max:255'],
            'workspace' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'repo_slug' => ['nullable', 'string', 'max:255'],
            'pr_number' => ['nullable', 'integer'],
            'compare_type' => ['nullable', 'string', 'max:120'],
            'base_branch' => ['nullable', 'string', 'max:255'],
            'head_branch' => ['nullable', 'string', 'max:255'],
            'audit_title' => ['nullable', 'string', 'max:255'],
            'audit_kind' => ['nullable', 'string', 'max:120'],
            'audit_status' => ['nullable', 'string', 'max:120'],
            'pr_title' => ['nullable', 'string', 'max:255'],
            'pr_description' => ['nullable', 'string', 'max:4000'],
            'linked_issues' => ['nullable', 'string', 'max:4000'],
            'context' => ['nullable', 'string', 'max:4000'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'diff_text' => ['required', 'string', 'max:10000000'],
            'conflict_payload' => ['nullable', 'array'],
            'model' => ['nullable', 'string', 'max:120'],
            'provider' => ['nullable', 'string', 'in:openai,deepseek'],
            'conversation_id' => ['nullable', 'integer', 'exists:chat_conversations,id'],
        ]);

        $source = (string) $payload['source'];
        $repo = (string) ($payload['repo'] ?? '');
        $prNumber = isset($payload['pr_number']) ? (string) $payload['pr_number'] : '';
        $compareType = (string) ($payload['compare_type'] ?? '');
        $baseBranch = (string) ($payload['base_branch'] ?? '');
        $headBranch = (string) ($payload['head_branch'] ?? '');
        $auditTitle = (string) ($payload['audit_title'] ?? '');
        $auditKind = (string) ($payload['audit_kind'] ?? '');
        $auditStatus = (string) ($payload['audit_status'] ?? '');
        $conflictPayload = (array) ($payload['conflict_payload'] ?? []);
        $diffText = $this->truncateDiffIfNeeded((string) $payload['diff_text'], $auditTitle);
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;
        $selectedProvider = isset($payload['provider']) ? (string) $payload['provider'] : 'openai';
        $conversationId = isset($payload['conversation_id']) ? (int) $payload['conversation_id'] : null;
        $prTitle = (string) ($payload['pr_title'] ?? '');
        $prDescription = (string) ($payload['pr_description'] ?? '');
        $linkedIssues = (string) ($payload['linked_issues'] ?? '');
        $contextNote = (string) ($payload['context'] ?? '');

        $issueComments = [];
        $reviewComments = [];
        $pullDetails = [];
        [$pullDetails, $issueComments, $reviewComments] = $this->loadPullContext($request, $source, $payload, $repo, $prNumber);
        if ($prTitle === '') {
            $prTitle = (string) ($pullDetails['title'] ?? '');
        }
        if ($prDescription === '') {
            $prDescription = (string) ($pullDetails['body'] ?? '');
        }

        $auditKind = $this->resolveAuditKind($auditKind, $source, $compareType, $prNumber);
        $auditStatus = $this->resolveAuditStatus($auditStatus, $auditKind, $source, $compareType, $pullDetails);
        $auditTitle = $this->resolveAuditTitle($auditTitle, $auditKind, $repo, $prNumber, $prTitle, $headBranch, (string) ($payload['file_name'] ?? ''), (string) ($payload['commit_hash'] ?? ''));

        $changedLines = $this->auditSnapshotWriter->extractChangedLines($diffText);
        $chatContext = $this->auditPromptComposer->composeChatContext([
            'source' => $source,
            'repo' => $repo !== '' ? $repo : null,
            'pr_number' => $prNumber !== '' ? $prNumber : null,
            'compare_type' => $compareType !== '' ? $compareType : null,
            'base_branch' => $baseBranch !== '' ? $baseBranch : null,
            'head_branch' => $headBranch !== '' ? $headBranch : null,
            'audit_title' => $auditTitle !== '' ? $auditTitle : null,
            'audit_kind' => $auditKind !== '' ? $auditKind : null,
            'audit_status' => $auditStatus !== '' ? $auditStatus : null,
            'pr_title' => $prTitle !== '' ? $prTitle : null,
            'pr_description' => $prDescription !== '' ? $prDescription : null,
            'linked_issues' => $linkedIssues !== '' ? $linkedIssues : null,
            'context' => $contextNote !== '' ? $contextNote : null,
            'file_name' => $payload['file_name'] ?? null,
            'changed_lines' => $changedLines,
            'issue_comments' => $issueComments,
            'review_comments' => $reviewComments,
        ]);
        $request->session()->put('active_audit_context', $chatContext);

        $userPrompt = $this->auditPromptComposer->compose([
            'source' => $source,
            'repo' => $repo !== '' ? $repo : null,
            'pr_number' => $prNumber !== '' ? $prNumber : null,
            'compare_type' => $compareType !== '' ? $compareType : null,
            'base_branch' => $baseBranch !== '' ? $baseBranch : null,
            'head_branch' => $headBranch !== '' ? $headBranch : null,
            'audit_title' => $auditTitle !== '' ? $auditTitle : null,
            'audit_kind' => $auditKind !== '' ? $auditKind : null,
            'audit_status' => $auditStatus !== '' ? $auditStatus : null,
            'pr_title' => $prTitle !== '' ? $prTitle : null,
            'pr_description' => $prDescription !== '' ? $prDescription : null,
            'linked_issues' => $linkedIssues !== '' ? $linkedIssues : null,
            'context' => $contextNote !== '' ? $contextNote : null,
            'file_name' => $payload['file_name'] ?? null,
            'changed_lines' => $changedLines,
            'issue_comments' => $issueComments,
            'review_comments' => $reviewComments,
            'diff_text' => $diffText,
            'conflict_payload' => $conflictPayload,
        ]);

        $systemPrompt = (string) config('audit_ai.system_prompt');

        $conversation = null;
        if ($conversationId) {
            $conversation = Auth::user()->conversations()->find($conversationId);
        }
        if (!$conversation) {
            $conversation = Auth::user()->conversations()->create([
                'title' => $auditTitle ?: 'Auto Audit',
                'provider' => $selectedProvider,
                'model' => $selectedModel ?? (string) config("{$selectedProvider}.model", 'gpt-4o-mini'),
                'active_audit_context' => $chatContext,
            ]);
        }
        $conversation->messages()->create([
            'role' => 'user',
            'content' => "Auto-audit of " . ($auditTitle ?: "diff"),
        ]);

        $reply = $this->openAiSimpleChatService->replyWithPrompt($systemPrompt, $userPrompt, $selectedModel, Auth::user(), $selectedProvider);

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $reply,
        ]);

        $meta = array_merge($this->extractMeta($reply), [
            'audit_kind' => $auditKind,
            'audit_status' => $auditStatus,
            'audit_title' => $auditTitle,
        ]);

        $debugText = $this->auditSnapshotWriter->buildContent([
            'source' => $source,
            'repo' => $repo !== '' ? $repo : null,
            'pr_number' => $prNumber !== '' ? $prNumber : null,
            'compare_type' => $compareType !== '' ? $compareType : null,
            'base_branch' => $baseBranch !== '' ? $baseBranch : null,
            'head_branch' => $headBranch !== '' ? $headBranch : null,
            'audit_title' => $auditTitle !== '' ? $auditTitle : null,
            'audit_kind' => $auditKind !== '' ? $auditKind : null,
            'audit_status' => $auditStatus !== '' ? $auditStatus : null,
            'pr_title' => $prTitle !== '' ? $prTitle : null,
            'pr_description' => $prDescription !== '' ? $prDescription : null,
            'linked_issues' => $linkedIssues !== '' ? $linkedIssues : null,
            'context' => $contextNote !== '' ? $contextNote : null,
            'file_name' => $payload['file_name'] ?? null,
            'diff_text' => $diffText,
            'issue_comments' => $issueComments,
            'review_comments' => $reviewComments,
            'prompt_system' => $systemPrompt,
            'prompt_user' => $userPrompt,
            'ai_response' => $reply,
        ]);
        $debugPath = $this->auditSnapshotWriter->write($debugText);

        return response()->json([
            'provider' => $selectedProvider,
            'reply' => $reply,
            'meta' => $meta,
            'debug_path' => $debugPath,
            'conversation_id' => $conversation->id,
        ]);
    }

    // Streams audit response tokens while still returning final meta/debug info via a done event.
    public function auditStream(Request $request): StreamedResponse
    {
        $payload = $request->validate([
            'source' => ['required', 'string', 'in:github,gitlab,bitbucket,azure,upload,editor,import,paste'],
            'repo' => ['nullable', 'string', 'max:255'],
            'repo_id' => ['nullable', 'string', 'max:255'],
            'project' => ['nullable', 'string', 'max:255'],
            'workspace' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'repo_slug' => ['nullable', 'string', 'max:255'],
            'pr_number' => ['nullable', 'integer'],
            'compare_type' => ['nullable', 'string', 'max:120'],
            'base_branch' => ['nullable', 'string', 'max:255'],
            'head_branch' => ['nullable', 'string', 'max:255'],
            'audit_title' => ['nullable', 'string', 'max:255'],
            'audit_kind' => ['nullable', 'string', 'max:120'],
            'audit_status' => ['nullable', 'string', 'max:120'],
            'pr_title' => ['nullable', 'string', 'max:255'],
            'pr_description' => ['nullable', 'string', 'max:4000'],
            'linked_issues' => ['nullable', 'string', 'max:4000'],
            'context' => ['nullable', 'string', 'max:4000'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'diff_text' => ['required', 'string', 'max:10000000'],
            'conflict_payload' => ['nullable', 'array'],
            'model' => ['nullable', 'string', 'max:120'],
            'provider' => ['nullable', 'string', 'in:openai,deepseek'],
            'conversation_id' => ['nullable', 'integer', 'exists:chat_conversations,id'],
        ]);

        $source = (string) $payload['source'];
        $repo = (string) ($payload['repo'] ?? '');
        $prNumber = isset($payload['pr_number']) ? (string) $payload['pr_number'] : '';
        $compareType = (string) ($payload['compare_type'] ?? '');
        $baseBranch = (string) ($payload['base_branch'] ?? '');
        $headBranch = (string) ($payload['head_branch'] ?? '');
        $auditTitle = (string) ($payload['audit_title'] ?? '');
        $auditKind = (string) ($payload['audit_kind'] ?? '');
        $auditStatus = (string) ($payload['audit_status'] ?? '');
        $conflictPayload = (array) ($payload['conflict_payload'] ?? []);
        $diffText = $this->truncateDiffIfNeeded((string) $payload['diff_text'], (string) ($payload['audit_title'] ?? ''));
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;
        $selectedProvider = isset($payload['provider']) ? (string) $payload['provider'] : 'openai';
        $conversationId = isset($payload['conversation_id']) ? (int) $payload['conversation_id'] : null;
        $prTitle = (string) ($payload['pr_title'] ?? '');
        $prDescription = (string) ($payload['pr_description'] ?? '');
        $linkedIssues = (string) ($payload['linked_issues'] ?? '');
        $contextNote = (string) ($payload['context'] ?? '');

        $issueComments = [];
        $reviewComments = [];
        $pullDetails = [];
        [$pullDetails, $issueComments, $reviewComments] = $this->loadPullContext($request, $source, $payload, $repo, $prNumber);
        if ($prTitle === '') {
            $prTitle = (string) ($pullDetails['title'] ?? '');
        }
        if ($prDescription === '') {
            $prDescription = (string) ($pullDetails['body'] ?? '');
        }

        $auditKind = $this->resolveAuditKind($auditKind, $source, $compareType, $prNumber);
        $auditStatus = $this->resolveAuditStatus($auditStatus, $auditKind, $source, $compareType, $pullDetails);
        $auditTitle = $this->resolveAuditTitle($auditTitle, $auditKind, $repo, $prNumber, $prTitle, $headBranch, (string) ($payload['file_name'] ?? ''), (string) ($payload['commit_hash'] ?? ''));

        $changedLines = $this->auditSnapshotWriter->extractChangedLines($diffText);
        $chatContext = $this->auditPromptComposer->composeChatContext([
            'source' => $source,
            'repo' => $repo !== '' ? $repo : null,
            'pr_number' => $prNumber !== '' ? $prNumber : null,
            'compare_type' => $compareType !== '' ? $compareType : null,
            'base_branch' => $baseBranch !== '' ? $baseBranch : null,
            'head_branch' => $headBranch !== '' ? $headBranch : null,
            'audit_title' => $auditTitle !== '' ? $auditTitle : null,
            'audit_kind' => $auditKind !== '' ? $auditKind : null,
            'audit_status' => $auditStatus !== '' ? $auditStatus : null,
            'pr_title' => $prTitle !== '' ? $prTitle : null,
            'pr_description' => $prDescription !== '' ? $prDescription : null,
            'linked_issues' => $linkedIssues !== '' ? $linkedIssues : null,
            'context' => $contextNote !== '' ? $contextNote : null,
            'file_name' => $payload['file_name'] ?? null,
            'changed_lines' => $changedLines,
            'issue_comments' => $issueComments,
            'review_comments' => $reviewComments,
        ]);
        $request->session()->put('active_audit_context', $chatContext);

        $userPrompt = $this->auditPromptComposer->compose([
            'source' => $source,
            'repo' => $repo !== '' ? $repo : null,
            'pr_number' => $prNumber !== '' ? $prNumber : null,
            'compare_type' => $compareType !== '' ? $compareType : null,
            'base_branch' => $baseBranch !== '' ? $baseBranch : null,
            'head_branch' => $headBranch !== '' ? $headBranch : null,
            'audit_title' => $auditTitle !== '' ? $auditTitle : null,
            'audit_kind' => $auditKind !== '' ? $auditKind : null,
            'audit_status' => $auditStatus !== '' ? $auditStatus : null,
            'pr_title' => $prTitle !== '' ? $prTitle : null,
            'pr_description' => $prDescription !== '' ? $prDescription : null,
            'linked_issues' => $linkedIssues !== '' ? $linkedIssues : null,
            'context' => $contextNote !== '' ? $contextNote : null,
            'file_name' => $payload['file_name'] ?? null,
            'changed_lines' => $changedLines,
            'issue_comments' => $issueComments,
            'review_comments' => $reviewComments,
            'diff_text' => $diffText,
            'conflict_payload' => $conflictPayload,
        ]);

        $systemPrompt = (string) config('audit_ai.system_prompt');

        $conversation = null;
        if ($conversationId) {
            $conversation = Auth::user()->conversations()->find($conversationId);
        }
        if (!$conversation) {
            $conversation = Auth::user()->conversations()->create([
                'title' => $auditTitle ?: 'Auto Audit',
                'provider' => $selectedProvider,
                'model' => $selectedModel ?? (string) config("{$selectedProvider}.model", 'gpt-4o-mini'),
                'active_audit_context' => $chatContext,
            ]);
        }
        $conversation->messages()->create([
            'role' => 'user',
            'content' => "Auto-audit of " . ($auditTitle ?: "diff"),
        ]);

        // Capture user before the stream closure — Auth::user() becomes unavailable
        // after session_write_close() when using the database session driver.
        $streamUser = Auth::user();

        return response()->stream(function () use ($source, $repo, $prNumber, $compareType, $baseBranch, $headBranch, $auditTitle, $auditKind, $auditStatus, $prTitle, $prDescription, $linkedIssues, $contextNote, $payload, $diffText, $conflictPayload, $issueComments, $reviewComments, $systemPrompt, $userPrompt, $selectedModel, $selectedProvider, $streamUser, $conversation): void {
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');
            @set_time_limit(0);
            @ignore_user_abort(true);
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            @ob_implicit_flush(true);

            if (function_exists('session_write_close')) {
                @session_write_close();
            }

            echo ':' . str_repeat(' ', 1024) . "\n\n";
            echo "event: conversation_id\n";
            echo 'data: '.json_encode(['id' => $conversation->id])."\n\n";
            @ob_flush();
            flush();

            $fullReply = '';

            $this->openAiSimpleChatService->streamWithMessages(
                [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                $selectedModel,
                $streamUser,
                function (string $token) use (&$fullReply): void {
                    $fullReply .= $token;
                    $json = json_encode(['text' => $token], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    echo "event: token\n";
                    echo 'data: ' . ($json ?: '{"text":""}') . "\n\n";
                    @ob_flush();
                    flush();
                },
                function (string $message): void {
                    $json = json_encode(['message' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    echo "event: error\n";
                    echo 'data: ' . ($json ?: '{"message":"Audit failed."}') . "\n\n";
                    @ob_flush();
                    flush();
                },
                $selectedProvider
            );

            $meta = array_merge($this->extractMeta($fullReply), [
                'audit_kind' => $auditKind,
                'audit_status' => $auditStatus,
                'audit_title' => $auditTitle,
            ]);
            $debugText = $this->auditSnapshotWriter->buildContent([
                'source' => $source,
                'repo' => $repo !== '' ? $repo : null,
                'pr_number' => $prNumber !== '' ? $prNumber : null,
                'compare_type' => $compareType !== '' ? $compareType : null,
                'base_branch' => $baseBranch !== '' ? $baseBranch : null,
                'head_branch' => $headBranch !== '' ? $headBranch : null,
                'audit_title' => $auditTitle !== '' ? $auditTitle : null,
                'audit_kind' => $auditKind !== '' ? $auditKind : null,
                'audit_status' => $auditStatus !== '' ? $auditStatus : null,
                'pr_title' => $prTitle !== '' ? $prTitle : null,
                'pr_description' => $prDescription !== '' ? $prDescription : null,
                'linked_issues' => $linkedIssues !== '' ? $linkedIssues : null,
                'context' => $contextNote !== '' ? $contextNote : null,
                'file_name' => $payload['file_name'] ?? null,
                'diff_text' => $diffText,
                'issue_comments' => $issueComments,
                'review_comments' => $reviewComments,
                'prompt_system' => $systemPrompt,
                'prompt_user' => $userPrompt,
                'ai_response' => $fullReply,
            ]);
            $debugPath = $this->auditSnapshotWriter->write($debugText);

            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $fullReply,
            ]);

            $donePayload = json_encode([
                'meta' => $meta,
                'debug_path' => $debugPath,
                'conversation_id' => $conversation->id,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            echo "event: done\n";
            echo 'data: ' . ($donePayload ?: '{"meta":null}') . "\n\n";
            @ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    // Extracts risk score and change type markers from model response.
    /**
     * @return array{0:array<string,mixed>,1:array<int,mixed>,2:array<int,mixed>}
     */
    private function loadPullContext(Request $request, string $source, array $payload, string $repo, string $prNumber): array
    {
        if (!$this->isVcsSource($source) || $repo === '' || $prNumber === '') {
            return [[], [], []];
        }

        $connection = $this->vcsProviderManager->resolveConnection($source, $request);
        if (!$connection) {
            return [[], [], []];
        }

        $provider = $this->vcsProviderManager->provider($source);
        $repoPayload = [
            'repo' => $repo,
            'repo_id' => $payload['repo_id'] ?? null,
            'project' => $payload['project'] ?? null,
            'workspace' => $payload['workspace'] ?? null,
            'organization' => $payload['organization'] ?? null,
            'repo_slug' => $payload['repo_slug'] ?? null,
        ];

        $pullDetailsResult = $provider->getPullDetails($connection, $repoPayload, $prNumber);
        $pullDetails = $pullDetailsResult['ok'] ? ($pullDetailsResult['data'] ?? []) : [];

        $issueResult = $provider->getPullIssueComments($connection, $repoPayload, $prNumber);
        $reviewResult = $provider->getPullReviewComments($connection, $repoPayload, $prNumber);

        return [
            $pullDetails,
            $issueResult['ok'] ? ($issueResult['data'] ?? []) : [],
            $reviewResult['ok'] ? ($reviewResult['data'] ?? []) : [],
        ];
    }

    private function isVcsSource(string $source): bool
    {
        return in_array($source, ['github', 'gitlab', 'bitbucket', 'azure'], true);
    }

    private function resolveAuditKind(string $auditKind, string $source, string $compareType, string $prNumber): string
    {
        if ($auditKind !== '') {
            return $auditKind;
        }

        if ($prNumber !== '') {
            return 'pull_request_audit';
        }

        return match ($compareType) {
            'branch_vs_main' => 'branch_audit',
            'commit' => 'commit_audit',
            default => match ($source) {
                'editor' => 'editor_diff_audit',
                'paste' => 'pasted_diff_audit',
                default => 'diff_audit',
            },
        };
    }

    private function resolveAuditStatus(string $auditStatus, string $auditKind, string $source, string $compareType, array $pullDetails): string
    {
        if ($auditKind === 'pull_request_audit' && !empty($pullDetails)) {
            if (!empty($pullDetails['merged_at'])) {
                return 'merged';
            }

            if (!empty($pullDetails['draft'])) {
                return 'draft';
            }

            $state = strtolower((string) ($pullDetails['state'] ?? ''));
            if (in_array($state, ['open', 'closed'], true)) {
                return $state;
            }
        }

        if ($auditStatus !== '') {
            return $auditStatus;
        }

        if ($auditKind === 'commit_audit' || $compareType === 'commit' || $source === 'import') {
            return 'historical';
        }

        if ($auditKind === 'branch_audit' || $compareType === 'branch_vs_main') {
            return 'active';
        }

        return 'adhoc';
    }

    private function resolveAuditTitle(
        string $auditTitle,
        string $auditKind,
        string $repo,
        string $prNumber,
        string $prTitle,
        string $headBranch,
        string $fileName,
        string $commitHash
    ): string {
        if ($auditTitle !== '') {
            return $auditTitle;
        }

        return match ($auditKind) {
            'pull_request_audit' => trim("{$repo} pull request audit ".($prTitle !== '' ? $prTitle : "#{$prNumber}")),
            'branch_audit' => trim("{$repo} branch audit {$headBranch}"),
            'commit_audit' => trim("{$repo} commit audit {$commitHash}"),
            default => trim($fileName !== '' ? "{$fileName} {$auditKind}" : "{$repo} audit"),
        };
    }

    /**
     * Truncates diff to a safe token budget for the AI model.
     * Keeps file headers so context is preserved. Appends a notice.
     */
    private function truncateDiffIfNeeded(string $diffText, string $auditTitle = '', int $maxChars = 120000): string
    {
        if (strlen($diffText) <= $maxChars) {
            return $diffText;
        }

        // Truncate at a line boundary to avoid breaking mid-hunk
        $truncated = substr($diffText, 0, $maxChars);
        $lastNewline = strrpos($truncated, "\n");
        if ($lastNewline !== false) {
            $truncated = substr($truncated, 0, $lastNewline);
        }

        $originalKb = round(strlen($diffText) / 1024);
        $keptKb = round(strlen($truncated) / 1024);
        $notice = "\n\n# ⚠️ Diff Truncated\n";
        $notice .= "# This diff was {$originalKb}KB which exceeds the AI context limit.\n";
        $notice .= "# Only the first {$keptKb}KB has been sent for analysis.\n";
        $notice .= "# For full analysis, audit individual pull requests or specific files.\n";

        return $truncated . $notice;
    }

    private function extractMeta(string $reply): array
    {
        $changeType = 'neutral';
        $riskScore = null;
        $riskLevel = 'medium';
        $suggestion = 'review_then_merge';
        $securityScore = null;
        $scalabilityScore = null;
        $reliabilityScore = null;

        // OWASP Top 10 fields (default: na)
        $owaspFields = [
            'owasp_broken_access_control'     => 'na',
            'owasp_cryptographic_failures'    => 'na',
            'owasp_injection'                 => 'na',
            'owasp_insecure_design'           => 'na',
            'owasp_security_misconfiguration' => 'na',
            'owasp_vulnerable_components'     => 'na',
            'owasp_auth_failures'             => 'na',
            'owasp_integrity_failures'        => 'na',
            'owasp_logging_failures'          => 'na',
            'owasp_ssrf'                      => 'na',
        ];

        // VAPT severity counts (default: 0)
        $vaptCritical = 0;
        $vaptHigh = 0;
        $vaptMedium = 0;
        $vaptLow = 0;
        $vaptInfo = 0;

        if (preg_match('/\[AUDIT_META\](.*?)\[\/AUDIT_META\]/is', $reply, $metaBlockMatch)) {
            $metaBlock = (string) $metaBlockMatch[1];

            if (preg_match('/change_type\s*=\s*(upgrade|downgrade|neutral)/i', $metaBlock, $m)) {
                $changeType = strtolower((string) $m[1]);
            }

            if (preg_match('/risk_score\s*=\s*(\d{1,3})/i', $metaBlock, $m)) {
                $riskScore = max(0, min(100, (int) $m[1]));
            }

            if (preg_match('/risk_level\s*=\s*(low|medium|high|critical)/i', $metaBlock, $m)) {
                $riskLevel = strtolower((string) $m[1]);
            }

            if (preg_match('/suggestion\s*=\s*(merge|dont_merge|review_then_merge)/i', $metaBlock, $m)) {
                $suggestion = strtolower((string) $m[1]);
            }

            if (preg_match('/security_score\s*=\s*(\d{1,3})/i', $metaBlock, $m)) {
                $securityScore = max(0, min(100, (int) $m[1]));
            }

            if (preg_match('/scalability_score\s*=\s*(\d{1,3})/i', $metaBlock, $m)) {
                $scalabilityScore = max(0, min(100, (int) $m[1]));
            }

            if (preg_match('/reliability_score\s*=\s*(\d{1,3})/i', $metaBlock, $m)) {
                $reliabilityScore = max(0, min(100, (int) $m[1]));
            }

            // Parse OWASP Top 10 fields
            foreach (array_keys($owaspFields) as $field) {
                if (preg_match('/' . preg_quote($field, '/') . '\s*=\s*(pass|review|fail|na)/i', $metaBlock, $m)) {
                    $owaspFields[$field] = strtolower((string) $m[1]);
                }
            }

            // Parse VAPT severity counts
            if (preg_match('/vapt_critical_count\s*=\s*(\d+)/i', $metaBlock, $m)) {
                $vaptCritical = max(0, (int) $m[1]);
            }
            if (preg_match('/vapt_high_count\s*=\s*(\d+)/i', $metaBlock, $m)) {
                $vaptHigh = max(0, (int) $m[1]);
            }
            if (preg_match('/vapt_medium_count\s*=\s*(\d+)/i', $metaBlock, $m)) {
                $vaptMedium = max(0, (int) $m[1]);
            }
            if (preg_match('/vapt_low_count\s*=\s*(\d+)/i', $metaBlock, $m)) {
                $vaptLow = max(0, (int) $m[1]);
            }
            if (preg_match('/vapt_info_count\s*=\s*(\d+)/i', $metaBlock, $m)) {
                $vaptInfo = max(0, (int) $m[1]);
            }
        }

        if (preg_match('/Change Type:\s*(upgrade|downgrade|neutral)/i', $reply, $m)) {
            $changeType = strtolower((string) $m[1]);
        }

        if (preg_match('/Risk Score:\s*(\d{1,3})\s*\/\s*100/i', $reply, $m)) {
            $riskScore = max(0, min(100, (int) $m[1]));
        }

        if (preg_match('/Risk Level:\s*(low|medium|high|critical)/i', $reply, $m)) {
            $riskLevel = strtolower((string) $m[1]);
        } elseif (is_int($riskScore)) {
            $riskLevel = $riskScore >= 80 ? 'critical' : ($riskScore >= 60 ? 'high' : ($riskScore >= 35 ? 'medium' : 'low'));
        }

        if (!is_int($securityScore) && preg_match('/Security Score:\s*(\d{1,3})\s*\/\s*100/i', $reply, $m)) {
            $securityScore = max(0, min(100, (int) $m[1]));
        }

        if (!is_int($scalabilityScore) && preg_match('/Scalability Score:\s*(\d{1,3})\s*\/\s*100/i', $reply, $m)) {
            $scalabilityScore = max(0, min(100, (int) $m[1]));
        }

        if (!is_int($reliabilityScore) && preg_match('/Reliability Score:\s*(\d{1,3})\s*\/\s*100/i', $reply, $m)) {
            $reliabilityScore = max(0, min(100, (int) $m[1]));
        }

        return array_merge([
            'change_type'       => $changeType,
            'risk_score'        => $riskScore,
            'risk_level'        => $riskLevel,
            'suggestion'        => $suggestion,
            'security_score'    => $securityScore,
            'scalability_score' => $scalabilityScore,
            'reliability_score' => $reliabilityScore,
            'vapt_critical_count' => $vaptCritical,
            'vapt_high_count'     => $vaptHigh,
            'vapt_medium_count'   => $vaptMedium,
            'vapt_low_count'      => $vaptLow,
            'vapt_info_count'     => $vaptInfo,
        ], $owaspFields);
    }
}
