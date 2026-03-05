<?php

namespace App\Http\Controllers\Ai\Voice;

use App\Http\Controllers\Controller;
use App\Services\Ai\Voice\OpenAiTranscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranscriptionController extends Controller
{
    public function __construct(private readonly OpenAiTranscriptionService $openAiTranscriptionService)
    {
    }

    public function transcribe(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'audio' => ['required', 'file', 'max:10240', 'mimes:webm,ogg,wav,mp3,mpeg'],
            'language' => ['nullable', 'string', 'max:10'],
            'model' => ['nullable', 'string', 'max:120'],
        ]);

        $audio = $payload['audio'];
        if ($audio->getSize() <= 0) {
            return response()->json(['message' => 'Mic audio is empty.'], 422);
        }

        $model = isset($payload['model']) ? (string) $payload['model'] : 'gpt-4o-mini-transcribe';
        $language = isset($payload['language']) ? (string) $payload['language'] : 'en';

        $result = $this->openAiTranscriptionService->transcribe($audio, Auth::user(), $model, $language);
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'provider' => $result['provider'],
            'model' => $result['model'],
            'text' => $result['text'],
        ]);
    }
}
