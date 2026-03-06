<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\OpenAiSimpleChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SimpleChatController extends Controller
{
    public function __construct(private readonly OpenAiSimpleChatService $openAiSimpleChatService)
    {
    }

    // Accepts one message and returns one AI reply.
    public function chat(Request $request): JsonResponse
    {
        $allowedModels = (array) config('openai.chat_models', [config('openai.model', 'gpt-4o-mini')]);

        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'model' => ['nullable', 'string', Rule::in($allowedModels)],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:8000'],
        ]);

        $message = (string) $payload['message'];
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;
        $history = isset($payload['history']) && is_array($payload['history']) ? $payload['history'] : [];
        $activeAuditContext = trim((string) $request->session()->get('active_audit_context', ''));

        $messageForModel = $message;
        if ($activeAuditContext !== '') {
            $messageForModel =
                "You have active code audit context below.\n"
                ."Use it when relevant to the user's question. "
                ."Answer naturally like a human, but when you mention code location include file and line.\n\n"
                ."ACTIVE AUDIT CONTEXT:\n{$activeAuditContext}\n\n"
                ."USER QUESTION:\n{$message}";
        }

        $reply = $this->openAiSimpleChatService->replyWithHistory($messageForModel, $history, $selectedModel, Auth::user());
        if ($this->requiresEvidence($message) && !$this->hasEvidenceReference($reply)) {
            $strictMessage =
                "Answer naturally and directly.\n"
                ."If available, include concrete code evidence like file:line and a short snippet.\n"
                ."If not found in context, say that clearly.\n\n"
                ."Question: {$message}";

            if ($activeAuditContext !== '') {
                $strictMessage .= "\n\nActive context:\n{$activeAuditContext}";
            }

            $reply = $this->openAiSimpleChatService->replyWithHistory($strictMessage, $history, $selectedModel, Auth::user());
        }

        return response()->json([
            'provider' => 'openai',
            'model' => $selectedModel ?? (string) config('openai.model', 'gpt-4o-mini'),
            'reply' => $reply,
        ]);
    }

    // Streams chat response in SSE chunks so frontend can render incrementally.
    public function chatStream(Request $request): StreamedResponse
    {
        $allowedModels = (array) config('openai.chat_models', [config('openai.model', 'gpt-4o-mini')]);

        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'model' => ['nullable', 'string', Rule::in($allowedModels)],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:8000'],
        ]);

        $message = (string) $payload['message'];
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;
        $history = isset($payload['history']) && is_array($payload['history']) ? $payload['history'] : [];
        $activeAuditContext = trim((string) $request->session()->get('active_audit_context', ''));

        $messageForModel = $message;
        if ($activeAuditContext !== '') {
            $messageForModel =
                "You have active code audit context below.\n"
                ."Use it when relevant to the user's question. "
                ."Answer naturally like a human, but when you mention code location include file and line.\n\n"
                ."ACTIVE AUDIT CONTEXT:\n{$activeAuditContext}\n\n"
                ."USER QUESTION:\n{$message}";
        }

        return response()->stream(function () use ($messageForModel, $history, $selectedModel) {
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
            @ob_flush();
            flush();

            $this->openAiSimpleChatService->streamRawWithHistory(
                $messageForModel,
                $history,
                $selectedModel,
                Auth::user(),
                function (string $chunk): void {
                    echo $chunk;
                    @ob_flush();
                    flush();
                },
                function (string $message): void {
                    $json = json_encode(['message' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    echo "event: error\n";
                    echo 'data: '.($json ?: '{"message":"Request failed."}')."\n\n";
                    @ob_flush();
                    flush();
                }
            );
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
}
