<?php

namespace App\Services\Ai\Voice;

use App\Models\User;
use App\Services\Ai\AiKeyResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAiTranscriptionService
{
    public function __construct(private readonly AiKeyResolver $aiKeyResolver)
    {
    }

    public function transcribe(
        UploadedFile $audio,
        ?User $user = null,
        string $model = 'gpt-4o-mini-transcribe',
        string $language = 'en'
    ): array {
        $apiKey = $this->aiKeyResolver->resolveFor($user);
        $baseUrl = (string) config('openai.base_url', 'https://api.openai.com/v1');
        $timeout = (int) config('openai.request_timeout', 30);

        if ($apiKey === '') {
            return ['ok' => false, 'message' => 'OpenAI API key is missing.'];
        }

        $url = rtrim($baseUrl, '/').'/audio/transcriptions';

        try {
            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->attach('file', file_get_contents($audio->getRealPath()), $audio->getClientOriginalName())
                ->post($url, [
                    'model' => $model,
                    'language' => $language,
                ]);

            if ($response->failed()) {
                return ['ok' => false, 'message' => 'Transcription failed: HTTP '.$response->status().' '.$response->body()];
            }

            $text = trim((string) data_get($response->json(), 'text', ''));
            if ($text === '') {
                return ['ok' => false, 'message' => 'No speech recognized from recording.'];
            }

            return [
                'ok' => true,
                'provider' => 'openai',
                'model' => $model,
                'text' => $text,
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Transcription failed: '.$e->getMessage()];
        }
    }
}
