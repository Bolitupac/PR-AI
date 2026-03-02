<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAiSimpleChatService
{
    //prompt here
    private const SYSTEM_PROMPT = 'You are a helpful assistant inside a PR review app. '
        .'Give clear, practical answers. Use prior chat context when available. '
        .'You can discuss both audit context and general questions.';

    // Sends one user message to OpenAI and returns plain text.
    public function reply(string $message, ?string $selectedModel = null): string
    {
        return $this->replyWithPrompt(self::SYSTEM_PROMPT, $message, $selectedModel);
    }

    // Sends one user message plus short chat history to preserve context.
    public function replyWithHistory(string $message, array $history = [], ?string $selectedModel = null): string
    {
        $systemPrompt = (string) config('openai.chat_system_prompt', self::SYSTEM_PROMPT);

        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
        ];

        foreach (array_slice($history, -20) as $item) {
            $role = (string) ($item['role'] ?? '');
            $content = trim((string) ($item['content'] ?? ''));
            if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }
            $messages[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        return $this->sendMessages($messages, $selectedModel);
    }

    // Sends one prompt pair (system + user) and returns plain text.
    public function replyWithPrompt(string $systemPrompt, string $userPrompt, ?string $selectedModel = null): string
    {
        return $this->sendMessages([
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
            [
                'role' => 'user',
                'content' => $userPrompt,
            ],
        ], $selectedModel);
    }

    // Sends prepared messages array to OpenAI and returns plain text.
    private function sendMessages(array $messages, ?string $selectedModel = null): string
    {
        $apiKey = (string) config('openai.api_key');
        $baseUrl = (string) config('openai.base_url', 'https://api.openai.com/v1');
        $defaultModel = (string) config('openai.model', 'gpt-4o-mini');
        $allowedModels = (array) config('openai.chat_models', [$defaultModel]);
        $model = in_array((string) $selectedModel, $allowedModels, true) ? (string) $selectedModel : $defaultModel;
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
                    'messages' => $messages,
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
