<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Services\Ai\OpenAiSimpleChatService;
use App\Services\DocGen\DocGenIntentDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SimpleChatController extends Controller
{
    public function __construct(
        private readonly OpenAiSimpleChatService $openAiSimpleChatService,
        private readonly DocGenIntentDetector $docGenIntentDetector,
    ) {
    }

    // Accepts one message and returns one AI reply.
    public function chat(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'model' => ['nullable', 'string', 'max:120'],
            'provider' => ['nullable', 'string', 'in:openai,deepseek'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:8000'],
            'docgen_mode_active' => ['nullable', 'boolean'],
            'conversation_id' => ['nullable', 'integer', 'exists:chat_conversations,id'],
        ]);

        $message = (string) $payload['message'];
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;
        $selectedProvider = isset($payload['provider']) ? (string) $payload['provider'] : 'openai';
        $history = isset($payload['history']) && is_array($payload['history']) ? $payload['history'] : [];
        $activeAuditContext = trim((string) $request->session()->get('active_audit_context', ''));
        $docGenModeActive = (bool) ($payload['docgen_mode_active'] ?? false);

        $conversation = $this->getOrCreateConversation($request, $payload['conversation_id'] ?? null, $selectedProvider, $selectedModel, $message);
        $conversation->messages()->create(['role' => 'user', 'content' => $message]);

        if ($this->docGenIntentDetector->matches($message)) {
            $reply = 'This looks like a document-generation request. Turn on DocGen mode in Apps, then send the prompt again so I can return it in document format.';
            $conversation->messages()->create(['role' => 'assistant', 'content' => $reply]);

            return response()->json([
                'provider' => $selectedProvider,
                'model' => $selectedModel ?? (string) config("{$selectedProvider}.model", 'gpt-4o-mini'),
                'reply' => $reply,
                'conversation_id' => $conversation->id,
            ]);
        }

        $messageForModel = $this->prependDocGenModeTag($message, $docGenModeActive);
        if ($activeAuditContext !== '') {
            $messageForModel =
                $this->buildDocGenModeInstruction($docGenModeActive)
                ."You have active code audit context below.\n"
                ."Use it when relevant to the user's question. "
                ."Answer naturally like a human, but when you mention code location include file and line.\n\n"
                ."ACTIVE AUDIT CONTEXT:\n{$activeAuditContext}\n\n"
                ."USER QUESTION:\n{$message}";
        }

        $messageForModel = $this->augmentForInlineComments($messageForModel, $message, $activeAuditContext);

        $reply = $this->openAiSimpleChatService->replyWithHistory($messageForModel, $history, $selectedModel, Auth::user(), $selectedProvider);
        if ($this->requiresEvidence($message) && !$this->hasEvidenceReference($reply)) {
            $strictMessage =
                $this->buildDocGenModeInstruction($docGenModeActive)
                ."Answer naturally and directly.\n"
                ."If available, include concrete code evidence like file:line and a short snippet.\n"
                ."If not found in context, say that clearly.\n\n"
                ."Question: {$message}";

            if ($activeAuditContext !== '') {
                $strictMessage .= "\n\nActive context:\n{$activeAuditContext}";
            }

            $reply = $this->openAiSimpleChatService->replyWithHistory($strictMessage, $history, $selectedModel, Auth::user(), $selectedProvider);
        }

        $conversation->messages()->create(['role' => 'assistant', 'content' => $reply]);

        return response()->json([
            'provider' => $selectedProvider,
            'model' => $selectedModel ?? (string) config("{$selectedProvider}.model", 'gpt-4o-mini'),
            'reply' => $reply,
            'conversation_id' => $conversation->id,
        ]);
    }

    public function inlineComments(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'model' => ['nullable', 'string', 'max:120'],
            'provider' => ['nullable', 'string', 'in:openai,deepseek'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:8000'],
            'docgen_mode_active' => ['nullable', 'boolean'],
        ]);

        $message = (string) $payload['message'];
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;
        $selectedProvider = isset($payload['provider']) ? (string) $payload['provider'] : 'openai';
        $history = isset($payload['history']) && is_array($payload['history']) ? $payload['history'] : [];
        $activeAuditContext = trim((string) $request->session()->get('active_audit_context', ''));
        $docGenModeActive = (bool) ($payload['docgen_mode_active'] ?? false);

        if ($activeAuditContext === '') {
            return response()->json(['reply' => '[INLINE_COMMENTS][][/INLINE_COMMENTS]']);
        }

        $messageForModel = $this->buildDocGenModeInstruction($docGenModeActive)
            .$this->buildInlineCommentOnlyPrompt($message, $activeAuditContext);
        $reply = $this->openAiSimpleChatService->replyWithHistory($messageForModel, $history, $selectedModel, Auth::user(), $selectedProvider);

        return response()->json([
            'provider' => $selectedProvider,
            'model' => $selectedModel ?? (string) config("{$selectedProvider}.model", 'gpt-4o-mini'),
            'reply' => $reply,
        ]);
    }

    public function followUps(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_message' => ['required', 'string', 'max:5000'],
            'assistant_reply' => ['required', 'string', 'max:20000'],
            'model' => ['nullable', 'string', 'max:120'],
            'provider' => ['nullable', 'string', 'in:openai,deepseek'],
            'docgen_mode_active' => ['nullable', 'boolean'],
        ]);

        $userMessage = trim((string) $payload['user_message']);
        $assistantReply = trim((string) $payload['assistant_reply']);
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;
        $selectedProvider = isset($payload['provider']) ? (string) $payload['provider'] : 'openai';
        $activeAuditContext = trim((string) $request->session()->get('active_audit_context', ''));
        $docGenModeActive = (bool) ($payload['docgen_mode_active'] ?? false);

        if ($userMessage === '' || $assistantReply === '') {
            return response()->json(['suggestions' => []]);
        }

        $reply = $this->openAiSimpleChatService->replyWithPrompt(
            $this->buildDocGenModeInstruction($docGenModeActive).$this->followUpsSystemPrompt(),
            $this->buildFollowUpsPrompt($userMessage, $assistantReply, $activeAuditContext),
            $selectedModel,
            Auth::user(),
            $selectedProvider
        );

        return response()->json([
            'suggestions' => $this->extractFollowUps($reply),
            'provider' => $selectedProvider,
            'model' => $selectedModel ?? (string) config("{$selectedProvider}.model", 'gpt-4o-mini'),
        ]);
    }

    // Streams chat response in SSE chunks so frontend can render incrementally.
    public function chatStream(Request $request): StreamedResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'model' => ['nullable', 'string', 'max:120'],
            'provider' => ['nullable', 'string', 'in:openai,deepseek'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:8000'],
            'docgen_mode_active' => ['nullable', 'boolean'],
            'conversation_id' => ['nullable', 'integer', 'exists:chat_conversations,id'],
        ]);

        $message = (string) $payload['message'];
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;
        $selectedProvider = isset($payload['provider']) ? (string) $payload['provider'] : 'openai';
        $history = isset($payload['history']) && is_array($payload['history']) ? $payload['history'] : [];
        $activeAuditContext = trim((string) $request->session()->get('active_audit_context', ''));
        $docGenModeActive = (bool) ($payload['docgen_mode_active'] ?? false);

        $conversation = $this->getOrCreateConversation($request, $payload['conversation_id'] ?? null, $selectedProvider, $selectedModel, $message);
        $conversation->messages()->create(['role' => 'user', 'content' => $message]);

        if ($this->docGenIntentDetector->matches($message)) {
            $reply = 'This looks like a document-generation request. Turn on DocGen mode in Apps, then send the prompt again so I can return it in document format.';
            $conversation->messages()->create(['role' => 'assistant', 'content' => $reply]);

            return response()->stream(function () use ($reply, $conversation) {
                echo ':' . str_repeat(' ', 1024) . "\n\n";
                echo "event: conversation_id\n";
                echo 'data: '.json_encode(['id' => $conversation->id])."\n\n";
                @ob_flush();
                flush();
                echo "event: token\n";
                echo 'data: '.json_encode(['text' => $reply], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n";
                echo "event: done\n";
                echo 'data: '.json_encode(['reply' => $reply, 'conversation_id' => $conversation->id], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n";
                @ob_flush();
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache, no-transform',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $messageForModel = $this->prependDocGenModeTag($message, $docGenModeActive);
        if ($activeAuditContext !== '') {
            $messageForModel =
                $this->buildDocGenModeInstruction($docGenModeActive)
                ."You have active code audit context below.\n"
                ."Use it when relevant to the user's question. "
                ."Answer naturally like a human, but when you mention code location include file and line.\n\n"
                ."ACTIVE AUDIT CONTEXT:\n{$activeAuditContext}\n\n"
                ."USER QUESTION:\n{$message}";
        }

        $messageForModel = $this->augmentForInlineComments($messageForModel, $message, $activeAuditContext);

        // Capture user before the stream closure — Auth::user() becomes unavailable
        // after session_write_close() when using the database session driver.
        $streamUser = Auth::user();

        return response()->stream(function () use ($messageForModel, $history, $selectedModel, $selectedProvider, $streamUser, $conversation) {
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

            $this->openAiSimpleChatService->streamReplyWithHistory(
                $messageForModel,
                $history,
                $selectedModel,
                $streamUser,
                function (string $token) use (&$fullReply): void {
                    $fullReply .= $token;
                    $json = json_encode(['text' => $token], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    echo "event: token\n";
                    echo 'data: '.($json ?: '{"text":""}')."\n\n";
                    @ob_flush();
                    flush();
                },
                function (string $message): void {
                    $json = json_encode(['message' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    echo "event: error\n";
                    echo 'data: '.($json ?: '{"message":"Request failed."}')."\n\n";
                    @ob_flush();
                    flush();
                },
                $selectedProvider
            );

            if (trim($fullReply) !== '') {
                $conversation->messages()->create(['role' => 'assistant', 'content' => $fullReply]);
            }

            $donePayload = json_encode([
                'reply' => $fullReply,
                'conversation_id' => $conversation->id,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            echo "event: done\n";
            echo 'data: '.($donePayload ?: '{"reply":""}')."\n\n";
            @ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function requiresEvidence(string $message): bool
    {
        return (bool) preg_match('/\b(where|which line|line number|line|function|is .* in the diff|exists in the diff|in the diff)\b/i', $message);
    }

    private function hasEvidenceReference(string $reply): bool
    {
        return (bool) preg_match('/[A-Za-z0-9_\/\.\-]+\.[A-Za-z0-9_+-]+:\d+(?:-\d+)?/', $reply);
    }

    private function prependDocGenModeTag(string $message, bool $docGenModeActive): string
    {
        return ($docGenModeActive ? "[DOCGEN_MODE:ACTIVE]\n" : "[DOCGEN_MODE:INACTIVE]\n").$message;
    }

    private function buildDocGenModeInstruction(bool $docGenModeActive): string
    {
        if (!$docGenModeActive) {
            return "[DOCGEN_MODE:INACTIVE]\n"
                ."DocGen mode is not active for this request. Answer normally unless the user explicitly enables document mode.\n\n";
        }

        return "[DOCGEN_MODE:ACTIVE]\n"
            ."DocGen mode is active for this request.\n"
            ."If the user asks for a document, report, proposal, spec, summary, plan, or other structured write-up, prefer long-form document generation over short chat answers.\n"
            ."When generating such content, be more complete, structured, and detailed than usual.\n\n";
    }

    private function augmentForInlineComments(string $messageForModel, string $rawMessage, string $activeAuditContext): string
    {
        if ($activeAuditContext === '') {
            return $messageForModel;
        }

        $wantsInlineComments = $this->wantsInlineComments($rawMessage);

        if (!$wantsInlineComments) {
            return $messageForModel;
        }

        return $messageForModel
            ."\n\nINLINE COMMENT MODE:\n"
            ."The UI supports rendering hidden inline comments directly beside diff lines.\n"
            ."When the user asks you to comment on code, annotate lines, highlight risky lines, suggest edits on lines, or review the diff, you MUST do two things in this order:\n"
            ."1. Give a short normal visible answer first.\n"
            ."2. Then append an [INLINE_COMMENTS] JSON block at the very end so the UI can render the comments beside the diff.\n"
            ."The [INLINE_COMMENTS] block must be valid JSON and must be the last thing in the reply.\n"
            ."Use a JSON array of up to 8 objects with exactly these keys only: path, line, side, body.\n"
            ."Use only exact file paths and exact line numbers that already exist in ACTIVE AUDIT CONTEXT.\n"
            ."Use side RIGHT for new/current code comments and LEFT for old/removed code comments.\n"
            ."Each body should be a concise code review comment, not a full essay.\n"
            ."Do not wrap the JSON in markdown fences.\n"
            ."Do not mention INLINE_COMMENTS in the visible prose.\n"
            ."If the user asked for comments and there are line-specific issues, you must return at least 1 inline comment object.\n"
            ."Only return an empty array if there is truly nothing worth commenting on.\n"
            ."Example format:\n"
            ."[INLINE_COMMENTS]\n"
            ."[{\"path\":\"resources/js/app.js\",\"line\":42,\"side\":\"RIGHT\",\"body\":\"Guard this branch against null input before calling map().\"}]\n"
            ."[/INLINE_COMMENTS]";
    }

    private function wantsInlineComments(string $message): bool
    {
        return (bool) preg_match(
            '/\b(comment|inline comment|leave comments|annotate|review the code|point out lines|auto edit|suggest edits|fix this|what should change|high risk lines|risky lines)\b/i',
            $message
        );
    }

    private function buildInlineCommentOnlyPrompt(string $message, string $activeAuditContext): string
    {
        return "You are generating line-specific inline code review comments for a diff UI.\n"
            ."Use the active audit context below.\n"
            ."Return only one [INLINE_COMMENTS] block and nothing else.\n"
            ."The block must contain valid JSON array data with up to 8 objects.\n"
            ."Each object must have exactly these keys: path, line, side, body.\n"
            ."Use only exact file paths and exact line numbers from ACTIVE AUDIT CONTEXT.\n"
            ."Use side RIGHT for new/current code comments and LEFT for old/removed code comments.\n"
            ."If the user asked for comments on risky lines, include the strongest line-specific issues.\n"
            ."If there is nothing worth commenting on, return an empty array.\n\n"
            ."ACTIVE AUDIT CONTEXT:\n{$activeAuditContext}\n\n"
            ."USER REQUEST:\n{$message}\n\n"
            ."[INLINE_COMMENTS]\n"
            ."[{\"path\":\"resources/js/app.js\",\"line\":42,\"side\":\"RIGHT\",\"body\":\"Guard this branch against null input before calling map().\"}]\n"
            ."[/INLINE_COMMENTS]";
    }

    private function followUpsSystemPrompt(): string
    {
        return "You generate short suggested next prompts for a coding assistant UI.\n"
            ."Return only a JSON array of strings.\n"
            ."Generate between 0 and 3 suggestions.\n"
            ."Each suggestion must sound like the exact next thing the user would type.\n"
            ."Keep each suggestion under 80 characters.\n"
            ."Make them specific to the assistant reply, not generic.\n"
            ."Do not repeat the user's original prompt.\n"
            ."Do not include numbering, markdown, or explanation.\n"
            ."If there is no useful next prompt, return an empty array [].";
    }

    private function buildFollowUpsPrompt(string $userMessage, string $assistantReply, string $activeAuditContext): string
    {
        $prompt = "USER MESSAGE:\n{$userMessage}\n\nASSISTANT REPLY:\n{$assistantReply}";

        if ($activeAuditContext !== '') {
            $prompt .= "\n\nACTIVE AUDIT CONTEXT IS AVAILABLE.\n"
                ."Prefer suggestions that continue the current code audit when relevant.";
        }

        return $prompt;
    }

    /**
     * @return array<int, string>
     */
    private function extractFollowUps(string $reply): array
    {
        $trimmed = trim($reply);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (!is_array($decoded)) {
            if (preg_match('/\[[\s\S]*\]/', $trimmed, $matches) === 1) {
                $decoded = json_decode($matches[0], true);
            }
        }

        if (!is_array($decoded)) {
            return [];
        }

        $suggestions = [];
        foreach ($decoded as $item) {
            $text = trim((string) $item);
            if ($text === '' || mb_strlen($text) > 80) {
                continue;
            }
            if (!in_array($text, $suggestions, true)) {
                $suggestions[] = $text;
            }
            if (count($suggestions) >= 3) {
                break;
            }
        }

        return $suggestions;
    }

    private function getOrCreateConversation(Request $request, ?int $conversationId, string $provider, ?string $model, string $firstUserMessage): ChatConversation
    {
        $user = Auth::user();
        if ($conversationId) {
            $conversation = $user->conversations()->find($conversationId);
            if ($conversation) {
                return $conversation;
            }
        }

        // Generate a friendly title from the user's first message (first 5 words)
        $words = preg_split('/\s+/', trim($firstUserMessage));
        $titleWords = array_slice($words, 0, 5);
        $title = implode(' ', $titleWords);
        if (mb_strlen($title) > 50) {
            $title = mb_substr($title, 0, 47) . '...';
        }
        if (empty($title)) {
            $title = 'New Chat';
        }

        return $user->conversations()->create([
            'title' => $title,
            'provider' => $provider,
            'model' => $model ?? (string) config("{$provider}.model", 'gpt-4o-mini'),
            'active_audit_context' => $request->session()->get('active_audit_context'),
        ]);
    }
}
