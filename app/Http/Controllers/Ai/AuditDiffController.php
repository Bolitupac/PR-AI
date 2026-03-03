<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AuditPromptComposer;
use App\Services\Ai\OpenAiSimpleChatService;
use App\Services\Audit\AuditSnapshotWriter;
use App\Services\GitHubApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditDiffController extends Controller
{
    public function __construct(
        private readonly OpenAiSimpleChatService $openAiSimpleChatService,
        private readonly AuditPromptComposer $auditPromptComposer,
        private readonly AuditSnapshotWriter $auditSnapshotWriter,
        private readonly GitHubApiService $gitHubApiService
    ) {
    }

    // Audits a selected diff automatically and returns formatted AI output.
    public function audit(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'source' => ['required', 'string', 'in:github,upload,editor'],
            'repo' => ['nullable', 'string', 'max:255'],
            'pr_number' => ['nullable', 'integer'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'diff_text' => ['required', 'string', 'max:2000000'],
            'model' => ['nullable', 'string', 'max:120'],
        ]);

        $source = (string) $payload['source'];
        $repo = (string) ($payload['repo'] ?? '');
        $prNumber = isset($payload['pr_number']) ? (string) $payload['pr_number'] : '';
        $diffText = (string) $payload['diff_text'];
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;

        $issueComments = [];
        $reviewComments = [];

        if ($source === 'github' && $repo !== '' && $prNumber !== '') {
            $user = Auth::user();
            if ($user?->github_access_token) {
                $issueResult = $this->gitHubApiService->getPullIssueComments($user->github_access_token, $repo, $prNumber);
                if ($issueResult['ok']) {
                    $issueComments = $issueResult['data'];
                }

                $reviewResult = $this->gitHubApiService->getPullReviewComments($user->github_access_token, $repo, $prNumber);
                if ($reviewResult['ok']) {
                    $reviewComments = $reviewResult['data'];
                }
            }
        }

        $changedLines = $this->auditSnapshotWriter->extractChangedLines($diffText);
        $chatContext = $this->auditPromptComposer->composeChatContext([
            'source' => $source,
            'repo' => $repo !== '' ? $repo : null,
            'pr_number' => $prNumber !== '' ? $prNumber : null,
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
            'file_name' => $payload['file_name'] ?? null,
            'changed_lines' => $changedLines,
            'issue_comments' => $issueComments,
            'review_comments' => $reviewComments,
            'diff_text' => $diffText,
        ]);

        $systemPrompt = (string) config('audit_ai.system_prompt');
        $reply = $this->openAiSimpleChatService->replyWithPrompt($systemPrompt, $userPrompt, $selectedModel);
        $meta = $this->extractMeta($reply);

        $debugText = $this->auditSnapshotWriter->buildContent([
            'source' => $source,
            'repo' => $repo !== '' ? $repo : null,
            'pr_number' => $prNumber !== '' ? $prNumber : null,
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
            'provider' => 'openai',
            'reply' => $reply,
            'meta' => $meta,
            'debug_path' => $debugPath,
        ]);
    }

    // Extracts risk score and change type markers from model response.
    private function extractMeta(string $reply): array
    {
        $changeType = 'neutral';
        $riskScore = null;
        $riskLevel = 'medium';
        $suggestion = 'review_then_merge';

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

        return [
            'change_type' => $changeType,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'suggestion' => $suggestion,
        ];
    }
}
