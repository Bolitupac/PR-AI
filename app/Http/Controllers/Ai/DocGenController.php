<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\OpenAiSimpleChatService;
use App\Services\DocGen\DocGenExportService;
use App\Services\DocGen\DocGenPromptComposer;
use App\Services\DocGen\DocGenResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocGenController extends Controller
{
    public function __construct(
        private readonly OpenAiSimpleChatService $chatService,
        private readonly DocGenPromptComposer $promptComposer,
        private readonly DocGenExportService $exportService,
        private readonly DocGenResponseFormatter $responseFormatter,
    ) {
    }

    public function chat(Request $request)
    {
        $allowedModels = (array) config('openai.chat_models', [config('openai.model', 'gpt-4o-mini')]);
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'model' => ['nullable', 'string', Rule::in($allowedModels)],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:8000'],
            'docgen_mode_active' => ['nullable', 'boolean'],
        ]);

        $messages = $this->promptComposer->buildMessages(
            (string) $payload['message'],
            (array) ($payload['history'] ?? []),
            trim((string) $request->session()->get('active_audit_context', '')),
            (bool) ($payload['docgen_mode_active'] ?? true)
        );

        $reply = $this->chatService->replyWithMessages($messages, $payload['model'] ?? null, Auth::user());
        $reply = $this->responseFormatter->normalize($reply);

        return response()->json([
            'provider' => 'openai',
            'model' => $payload['model'] ?? (string) config('openai.model', 'gpt-4o-mini'),
            'reply' => $reply,
        ]);
    }

    public function chatStream(Request $request): StreamedResponse
    {
        $allowedModels = (array) config('openai.chat_models', [config('openai.model', 'gpt-4o-mini')]);
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'model' => ['nullable', 'string', Rule::in($allowedModels)],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:8000'],
            'docgen_mode_active' => ['nullable', 'boolean'],
        ]);

        $messages = $this->promptComposer->buildMessages(
            (string) $payload['message'],
            (array) ($payload['history'] ?? []),
            trim((string) $request->session()->get('active_audit_context', '')),
            (bool) ($payload['docgen_mode_active'] ?? true)
        );

        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;

        return response()->stream(function () use ($messages, $selectedModel) {
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

            $fullReply = '';

            $this->chatService->streamWithMessages(
                $messages,
                $selectedModel,
                Auth::user(),
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
                }
            );

            $normalizedReply = $this->responseFormatter->normalize($fullReply);
            $donePayload = json_encode(['reply' => $normalizedReply], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

    public function export(Request $request)
    {
        $payload = $request->validate([
            'format' => ['required', 'string', Rule::in(['pdf', 'docx', 'md', 'txt', 'html', 'json', 'yaml', 'csv', 'xlsx', 'pptx', 'tex'])],
            'title' => ['nullable', 'string', 'max:200'],
            'markdown' => ['required', 'string', 'max:50000'],
        ]);

        $export = $this->exportService->export(
            (string) $payload['format'],
            (string) $payload['markdown'],
            isset($payload['title']) ? (string) $payload['title'] : null
        );

        return response($export['content'], 200, [
            'Content-Type' => $export['mime'],
            'Content-Disposition' => 'attachment; filename="'.$export['filename'].'"',
        ]);
    }
}
