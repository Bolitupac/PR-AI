<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\OpenAiSimpleChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimpleChatController extends Controller
{
    public function __construct(private readonly OpenAiSimpleChatService $openAiSimpleChatService)
    {
    }

    // Accepts one message and returns one AI reply.
    public function chat(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = (string) $payload['message'];

        $reply = $this->openAiSimpleChatService->reply($message);

        return response()->json([
            'provider' => 'openai',
            'reply' => $reply,
        ]);
    }
}
