<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\OpenAiSimpleChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        ]);

        $message = (string) $payload['message'];
        $selectedModel = isset($payload['model']) ? (string) $payload['model'] : null;

        $reply = $this->openAiSimpleChatService->reply($message, $selectedModel);

        return response()->json([
            'provider' => 'openai',
            'model' => $selectedModel ?? (string) config('openai.model', 'gpt-4o-mini'),
            'reply' => $reply,
        ]);
    }
}
