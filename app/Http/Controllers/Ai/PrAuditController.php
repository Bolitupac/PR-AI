<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiResponseValidator;
use App\Services\Ai\GeminiAuditService;
use App\Services\Ai\PrContextBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrAuditController extends Controller
{
    public function __construct(
        private readonly PrContextBuilderService $contextBuilder,
        private readonly GeminiAuditService $geminiAuditService,
        private readonly AiResponseValidator $validator
    ) {
    }

    // Runs a first-pass audit for repo PR context or raw diff context.
    public function audit(Request $request): JsonResponse
    {
        $request->validate([
            'repo' => ['nullable', 'string'],
            'pr_number' => ['nullable', 'integer'],
            'diff_text' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $repo = (string) $request->input('repo', '');
        $prNumber = $request->input('pr_number');
        $diffText = $request->input('diff_text');

        $context = $this->buildContext($user?->github_access_token, $repo, $prNumber, $diffText);
        if (($context['diff_changes_only'] ?? '') === '') {
            return response()->json($this->validator->fallback('audit', 'No diff content available to audit.'), 422);
        }

        $raw = $this->geminiAuditService->runAudit($context);
        $normalized = $this->validator->normalize($raw, 'audit');
        if ($this->hasInvalidJsonError($normalized)) {
            $raw = $this->geminiAuditService->runAudit($context);
            $normalized = $this->validator->normalize($raw, 'audit');
        }

        return response()->json([
            'context' => [
                'repo' => $context['repo'],
                'pr_number' => $context['pr_number'],
            ],
            'result' => $normalized,
        ]);
    }

    // Runs follow-up chat against the same PR/diff context.
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'question' => ['required', 'string', 'min:1'],
            'repo' => ['nullable', 'string'],
            'pr_number' => ['nullable', 'integer'],
            'diff_text' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $question = (string) $request->input('question');
        $repo = (string) $request->input('repo', '');
        $prNumber = $request->input('pr_number');
        $diffText = $request->input('diff_text');

        $context = $this->buildContext($user?->github_access_token, $repo, $prNumber, $diffText);
        if (($context['diff_changes_only'] ?? '') === '') {
            return response()->json($this->validator->fallback('chat', 'No diff content available for chat context.'), 422);
        }

        $raw = $this->geminiAuditService->runChat($context, $question);
        $normalized = $this->validator->normalize($raw, 'chat');
        if ($this->hasInvalidJsonError($normalized)) {
            $raw = $this->geminiAuditService->runChat($context, $question);
            $normalized = $this->validator->normalize($raw, 'chat');
        }

        return response()->json([
            'context' => [
                'repo' => $context['repo'],
                'pr_number' => $context['pr_number'],
            ],
            'result' => $normalized,
        ]);
    }

    // Chooses GitHub-enriched context when repo+PR exist, else raw diff context.
    private function buildContext(?string $encryptedToken, string $repo, mixed $prNumber, mixed $diffText): array
    {
        if ($encryptedToken && $repo !== '' && $prNumber) {
            return $this->contextBuilder->build($encryptedToken, $repo, (string) $prNumber, is_string($diffText) ? $diffText : null);
        }

        return $this->contextBuilder->buildFromRawDiff(is_string($diffText) ? $diffText : null);
    }

    // Checks if the response failed schema parsing.
    private function hasInvalidJsonError(array $normalized): bool
    {
        $errors = $normalized['errors'] ?? [];
        if (!is_array($errors) || !count($errors)) {
            return false;
        }

        return in_array('Model returned invalid JSON.', $errors, true);
    }
}
