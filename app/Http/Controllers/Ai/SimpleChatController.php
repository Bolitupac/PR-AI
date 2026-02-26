<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\OpenAiSimpleChatService;
use App\Services\Ai\SimpleChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimpleChatController extends Controller
{
    public function __construct(
        private readonly SimpleChatService $simpleChatService,
        private readonly OpenAiSimpleChatService $openAiSimpleChatService
    )
    {
    }

    // Accepts one message and returns one AI reply.
    public function chat(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'provider' => ['nullable', 'string', 'in:gemini,openai'],
        ]);

        $provider = (string) ($payload['provider'] ?? 'gemini');
        $message = (string) $payload['message'];

        $reply = $provider === 'openai'
            ? $this->openAiSimpleChatService->reply($message)
            : $this->simpleChatService->reply($message);

        return response()->json([
            'provider' => $provider,
            'reply' => $reply,
        ]);
    }
}
