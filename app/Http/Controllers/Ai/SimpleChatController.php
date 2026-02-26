<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\SimpleChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimpleChatController extends Controller
{
    public function __construct(private readonly SimpleChatService $simpleChatService)
    {
    }

    // Accepts one message and returns one AI reply.
    public function chat(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $reply = $this->simpleChatService->reply((string) $payload['message']);

        return response()->json(['reply' => $reply]);
    }
}

