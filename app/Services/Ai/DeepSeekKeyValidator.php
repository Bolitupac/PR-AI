<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Throwable;

class DeepSeekKeyValidator
{
    public function validate(string $apiKey): array
    {
        $trimmed = trim($apiKey);
        if ($trimmed === '') {
            return ['ok' => false, 'message' => 'API key is required.'];
        }

        if (!str_starts_with($trimmed, 'sk-')) {
            return ['ok' => false, 'message' => 'Invalid key format. DeepSeek keys start with sk-.'];
        }

        $baseUrl = (string) config('deepseek.base_url', 'https://api.deepseek.com/v1');
        $model = (string) config('deepseek.model', 'deepseek-chat');
        $timeout = (int) config('deepseek.request_timeout', 30);
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
                'message' => 'Could not reach DeepSeek. Check your network and try again.',
            ];
        }

        if ($response->successful()) {
            $data = $response->json();
            $reply = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
            if ($reply !== '') {
                return ['ok' => true, 'message' => 'API key is valid — DeepSeek responded.'];
            }
            return ['ok' => true, 'message' => 'API key is valid.'];
        }

        if ($response->status() === 401 || $response->status() === 403) {
            $body = $response->json();
            $errMsg = $body['error']['message'] ?? 'Invalid API key.';
            return ['ok' => false, 'message' => 'Not a valid DeepSeek key: ' . $errMsg];
        }

        if ($response->status() === 429) {
            return ['ok' => false, 'message' => 'DeepSeek rate limited. Try again in a moment.'];
        }

        return ['ok' => false, 'message' => 'Could not validate API key. Try again.'];
    }
}
