<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAiKeyValidator
{
    public function validate(string $apiKey): array
    {
        $trimmed = trim($apiKey);
        if ($trimmed === '') {
            return ['ok' => false, 'message' => 'API key is required.'];
        }

        if (!str_starts_with($trimmed, 'sk-')) {
            return ['ok' => false, 'message' => 'Invalid key format. OpenAI keys start with sk-.'];
        }

        $baseUrl = (string) config('openai.base_url', 'https://api.openai.com/v1');
        $model = (string) config('openai.model', 'gpt-4o-mini');
        $timeout = (int) config('openai.request_timeout', 30);
        $url = rtrim($baseUrl, '/') . '/chat/completions';

        try {
            $response = Http::timeout($timeout)->withToken($trimmed)->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Hi'],
                ],
                'max_tokens' => 5,
                'temperature' => 0,
            ]);
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Could not reach OpenAI. Check your network and try again.',
            ];
        }

        if ($response->successful()) {
            $data = $response->json();
            $reply = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
            if ($reply !== '') {
                return ['ok' => true, 'message' => 'API key is valid — AI responded.'];
            }
            return ['ok' => true, 'message' => 'API key is valid.'];
        }

        if ($response->status() === 401 || $response->status() === 403) {
            $body = $response->json();
            $errMsg = $body['error']['message'] ?? 'Invalid API key.';
            return ['ok' => false, 'message' => 'Not a valid OpenAI key: ' . $errMsg];
        }

        if ($response->status() === 429) {
            return ['ok' => false, 'message' => 'OpenAI rate limited. Try again in a moment.'];
        }

        return ['ok' => false, 'message' => 'Could not validate API key. Try again.'];
    }
}
