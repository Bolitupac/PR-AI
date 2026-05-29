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
        $timeout = (int) config('deepseek.request_timeout', 30);
        $url = rtrim($baseUrl, '/') . '/models';

        try {
            $response = Http::timeout($timeout)->withToken($trimmed)->get($url);
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Could not validate API key right now. Try again.'];
        }

        if ($response->successful()) {
            return ['ok' => true, 'message' => 'API key is valid.'];
        }

        if ($response->status() === 401) {
            return ['ok' => false, 'message' => 'Invalid API key.'];
        }

        return ['ok' => false, 'message' => 'Could not validate API key right now. Try again.'];
    }
}
