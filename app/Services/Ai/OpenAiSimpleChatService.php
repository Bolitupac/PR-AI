<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAiSimpleChatService
{
    // Sends one user message to OpenAI and returns plain text.
    public function reply(string $message): string
    {
        $apiKey = (string) config('openai.api_key');
        $baseUrl = (string) config('openai.base_url', 'https://api.openai.com/v1');
        $model = (string) config('openai.model', 'gpt-4o-mini');
        $temperature = (float) config('openai.temperature', 0.3);
        $timeout = (int) config('openai.request_timeout', 30);

        if ($apiKey === '') {
            return 'AI request failed: OpenAI API key is missing.';
        }

        $url = rtrim($baseUrl, '/').'/chat/completions';

        try {
            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->post($url, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                    'temperature' => $temperature,
                ]);

            if ($response->failed()) {
                return 'AI request failed: HTTP '.$response->status().' '.$response->body();
            }

            $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            return $text !== '' ? $text : 'No response from AI.';
        } catch (Throwable $e) {
            $parts = [
                'AI request failed',
                'Exception: '.get_class($e),
                'Message: '.$e->getMessage(),
            ];

            $prev = $e->getPrevious();
            if ($prev) {
                $parts[] = 'Previous: '.get_class($prev).' - '.$prev->getMessage();
            }

            return implode(' | ', $parts);
        }
    }
}

