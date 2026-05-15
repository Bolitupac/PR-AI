<?php

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAiSimpleChatService
{
    public function __construct(private readonly AiKeyResolver $aiKeyResolver)
    {
    }

    //prompt here
    private const SYSTEM_PROMPT = 'You are a helpful assistant inside a PR review app. '
        .'Give clear, practical answers. Use prior chat context when available. '
        .'You can discuss both audit context and general questions.';

    // Sends one user message to OpenAI and returns plain text.
    public function reply(string $message, ?string $selectedModel = null, ?User $user = null): string
    {
        return $this->replyWithPrompt(self::SYSTEM_PROMPT, $message, $selectedModel, $user);
    }

    // Sends one user message plus short chat history to preserve context.
    public function replyWithHistory(string $message, array $history = [], ?string $selectedModel = null, ?User $user = null): string
    {
        $messages = $this->buildHistoryMessages($message, $history, $user);

        return $this->sendMessages($messages, $selectedModel, $user);
    }

    // Streams assistant tokens as they arrive from OpenAI chat completions SSE.
    public function streamReplyWithHistory(
        string $message,
        array $history,
        ?string $selectedModel,
        ?User $user,
        callable $onToken,
        ?callable $onError = null
    ): void {
        $apiKey = $this->aiKeyResolver->resolveFor($user);
        $baseUrl = (string) config('openai.base_url', 'https://api.openai.com/v1');
        $defaultModel = (string) config('openai.model', 'gpt-4o-mini');
        $allowedModels = (array) config('openai.chat_models', [$defaultModel]);
        $model = in_array((string) $selectedModel, $allowedModels, true) ? (string) $selectedModel : $defaultModel;
        $temperature = (float) config('openai.temperature', 0.3);
        $timeout = (int) config('openai.request_timeout', 30);
        $connectTimeout = (int) config('openai.connect_timeout', 20);
        $retries = (int) config('openai.retries', 2);
        $retrySleepMs = (int) config('openai.retry_sleep_ms', 600);

        if ($apiKey === '') {
            if ($onError) {
                $onError('AI request failed: OpenAI API key is missing.');
            }
            return;
        }

        $url = rtrim($baseUrl, '/').'/chat/completions';
        $messages = $this->buildHistoryMessages($message, $history, $user);

        try {
            $response = Http::withToken($apiKey)
                ->withOptions(['stream' => true])
                ->connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->retry($retries, $retrySleepMs, throw: false)
                ->send('POST', $url, [
                    'headers' => ['Accept' => 'text/event-stream'],
                    'json' => [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => $temperature,
                        'stream' => true,
                    ],
                ]);

            if ($response->failed()) {
                if ($onError) {
                    $onError('AI request failed: HTTP '.$response->status().' '.$response->body());
                }
                return;
            }

            $body = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (!$body->eof()) {
                $buffer .= $body->read(64);

                while (($newlinePos = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $newlinePos));
                    $buffer = substr($buffer, $newlinePos + 1);

                    if ($line === '' || !str_starts_with($line, 'data:')) {
                        continue;
                    }

                    $payload = trim(substr($line, 5));
                    if ($payload === '[DONE]') {
                        return;
                    }

                    $json = json_decode($payload, true);
                    if (!is_array($json)) {
                        continue;
                    }

                    $token = (string) data_get($json, 'choices.0.delta.content', '');
                    if ($token !== '') {
                        $onToken($token);
                    }
                }
            }
        } catch (Throwable $e) {
            if ($onError) {
                $parts = [
                    'AI request failed',
                    'Exception: '.get_class($e),
                    'Message: '.$e->getMessage(),
                ];
                $prev = $e->getPrevious();
                if ($prev) {
                    $parts[] = 'Previous: '.get_class($prev).' - '.$prev->getMessage();
                }
                $onError(implode(' | ', $parts));
            }
        }
    }

    // Proxies raw OpenAI SSE chunks to caller for true real-time UI streaming.
    public function streamRawWithHistory(
        string $message,
        array $history,
        ?string $selectedModel,
        ?User $user,
        callable $onChunk,
        ?callable $onError = null
    ): void {
        $apiKey = $this->aiKeyResolver->resolveFor($user);
        $baseUrl = (string) config('openai.base_url', 'https://api.openai.com/v1');
        $defaultModel = (string) config('openai.model', 'gpt-4o-mini');
        $allowedModels = (array) config('openai.chat_models', [$defaultModel]);
        $model = in_array((string) $selectedModel, $allowedModels, true) ? (string) $selectedModel : $defaultModel;
        $temperature = (float) config('openai.temperature', 0.3);
        $timeout = (int) config('openai.request_timeout', 30);
        $connectTimeout = (int) config('openai.connect_timeout', 20);
        $retries = (int) config('openai.retries', 2);
        $retrySleepMs = (int) config('openai.retry_sleep_ms', 600);

        if ($apiKey === '') {
            if ($onError) {
                $onError('AI request failed: OpenAI API key is missing.');
            }
            return;
        }

        $url = rtrim($baseUrl, '/').'/chat/completions';
        $messages = $this->buildHistoryMessages($message, $history, $user);

        try {
            $response = Http::withToken($apiKey)
                ->withOptions(['stream' => true])
                ->connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->retry($retries, $retrySleepMs, throw: false)
                ->send('POST', $url, [
                    'headers' => ['Accept' => 'text/event-stream'],
                    'json' => [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => $temperature,
                        'stream' => true,
                    ],
                ]);

            if ($response->failed()) {
                if ($onError) {
                    $onError('AI request failed: HTTP '.$response->status().' '.$response->body());
                }
                return;
            }

            $body = $response->toPsrResponse()->getBody();
            while (!$body->eof()) {
                $chunk = $body->read(256);
                if ($chunk === '') {
                    continue;
                }
                $onChunk($chunk);
            }
        } catch (Throwable $e) {
            if ($onError) {
                $parts = [
                    'AI request failed',
                    'Exception: '.get_class($e),
                    'Message: '.$e->getMessage(),
                ];
                $prev = $e->getPrevious();
                if ($prev) {
                    $parts[] = 'Previous: '.get_class($prev).' - '.$prev->getMessage();
                }
                $onError(implode(' | ', $parts));
            }
        }
    }

    // Streams assistant tokens for an explicit prepared messages array.
    public function streamWithMessages(
        array $messages,
        ?string $selectedModel,
        ?User $user,
        callable $onToken,
        ?callable $onError = null
    ): void {
        $apiKey = $this->aiKeyResolver->resolveFor($user);
        $baseUrl = (string) config('openai.base_url', 'https://api.openai.com/v1');
        $defaultModel = (string) config('openai.model', 'gpt-4o-mini');
        $allowedModels = (array) config('openai.chat_models', [$defaultModel]);
        $model = in_array((string) $selectedModel, $allowedModels, true) ? (string) $selectedModel : $defaultModel;
        $temperature = (float) config('openai.temperature', 0.3);
        $timeout = (int) config('openai.request_timeout', 30);
        $connectTimeout = (int) config('openai.connect_timeout', 20);
        $retries = (int) config('openai.retries', 2);
        $retrySleepMs = (int) config('openai.retry_sleep_ms', 600);

        if ($apiKey === '') {
            if ($onError) {
                $onError('AI request failed: OpenAI API key is missing.');
            }
            return;
        }

        $url = rtrim($baseUrl, '/').'/chat/completions';

        try {
            $response = Http::withToken($apiKey)
                ->withOptions(['stream' => true])
                ->connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->retry($retries, $retrySleepMs, throw: false)
                ->send('POST', $url, [
                    'headers' => ['Accept' => 'text/event-stream'],
                    'json' => [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => $temperature,
                        'stream' => true,
                    ],
                ]);

            if ($response->failed()) {
                if ($onError) {
                    $onError('AI request failed: HTTP '.$response->status().' '.$response->body());
                }
                return;
            }

            $body = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (!$body->eof()) {
                $buffer .= $body->read(64);

                while (($newlinePos = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $newlinePos));
                    $buffer = substr($buffer, $newlinePos + 1);

                    if ($line === '' || !str_starts_with($line, 'data:')) {
                        continue;
                    }

                    $payload = trim(substr($line, 5));
                    if ($payload === '[DONE]') {
                        return;
                    }

                    $json = json_decode($payload, true);
                    if (!is_array($json)) {
                        continue;
                    }

                    $token = (string) data_get($json, 'choices.0.delta.content', '');
                    if ($token !== '') {
                        $onToken($token);
                    }
                }
            }
        } catch (Throwable $e) {
            if ($onError) {
                $parts = [
                    'AI request failed',
                    'Exception: '.get_class($e),
                    'Message: '.$e->getMessage(),
                ];
                $prev = $e->getPrevious();
                if ($prev) {
                    $parts[] = 'Previous: '.get_class($prev).' - '.$prev->getMessage();
                }
                $onError(implode(' | ', $parts));
            }
        }
    }

    // Sends one prompt pair (system + user) and returns plain text.
    public function replyWithPrompt(string $systemPrompt, string $userPrompt, ?string $selectedModel = null, ?User $user = null): string
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
        ], $selectedModel, $user);
    }

    public function replyWithMessages(array $messages, ?string $selectedModel = null, ?User $user = null): string
    {
        return $this->sendMessages($messages, $selectedModel, $user);
    }

    // Sends prepared messages array to OpenAI and returns plain text.
    private function sendMessages(array $messages, ?string $selectedModel = null, ?User $user = null): string
    {
        $apiKey = $this->aiKeyResolver->resolveFor($user);
        $baseUrl = (string) config('openai.base_url', 'https://api.openai.com/v1');
        $defaultModel = (string) config('openai.model', 'gpt-4o-mini');
        $allowedModels = (array) config('openai.chat_models', [$defaultModel]);
        $model = in_array((string) $selectedModel, $allowedModels, true) ? (string) $selectedModel : $defaultModel;
        $temperature = (float) config('openai.temperature', 0.3);
        $timeout = (int) config('openai.request_timeout', 30);
        $connectTimeout = (int) config('openai.connect_timeout', 20);
        $retries = (int) config('openai.retries', 2);
        $retrySleepMs = (int) config('openai.retry_sleep_ms', 600);

        if ($apiKey === '') {
            return 'AI request failed: OpenAI API key is missing.';
        }

        $url = rtrim($baseUrl, '/').'/chat/completions';

        try {
            $response = Http::connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->retry($retries, $retrySleepMs, throw: false)
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

    private function buildHistoryMessages(string $message, array $history, ?User $user = null): array
    {
        $basePrompt = (string) config('openai.chat_system_prompt', self::SYSTEM_PROMPT);

        // Inject user's saved AI preferences into the system prompt
        $prefsExtra = '';
        if ($user !== null && is_array($user->ai_preferences) && count($user->ai_preferences) > 0) {
            $prefs = $user->ai_preferences;

            $personalityMap = [
                'balanced'   => 'You are a balanced code reviewer — thorough but not overwhelming.',
                'strict'     => 'You are strict and concise. Be direct, flag every issue, keep responses short and precise.',
                'mentor'     => 'You are a friendly mentor. Be encouraging, explain your reasoning, and guide the developer gently.',
                'architect'  => 'You are architecture-first. Focus on design patterns, system design, scalability, and structural concerns before style issues.',
            ];
            $verbosityMap = [
                'short'    => 'Keep your responses SHORT — one to three sentences unless detail is critical.',
                'medium'   => 'Keep your responses MEDIUM length — balanced between depth and brevity.',
                'detailed' => 'Give DETAILED responses — be comprehensive, include examples, explain reasoning fully.',
            ];
            $toneMap = [
                'supportive' => 'Use a supportive, positive tone.',
                'neutral'    => 'Use a neutral, professional tone.',
                'direct'     => 'Use a direct, no-nonsense tone.',
            ];

            $personality  = $personalityMap[$prefs['personality'] ?? '']  ?? '';
            $verbosity    = $verbosityMap[$prefs['verbosity']   ?? '']    ?? '';
            $tone         = $toneMap[$prefs['tone']             ?? '']    ?? '';
            $customPrompt = trim((string) ($prefs['custom_prompt'] ?? ''));

            $parts = array_filter([$personality, $verbosity, $tone, $customPrompt]);
            if (count($parts) > 0) {
                $prefsExtra = "\n\nUSER AI PREFERENCES:\n".implode("\n", $parts);
            }
        }

        $systemPrompt = $basePrompt.$prefsExtra;

        $messages = [
            [
                'role'    => 'system',
                'content' => $systemPrompt,
            ],
        ];

        foreach (array_slice($history, -20) as $item) {
            $role    = (string) ($item['role'] ?? '');
            $content = trim((string) ($item['content'] ?? ''));
            if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }
            $messages[] = [
                'role'    => $role,
                'content' => $content,
            ];
        }

        $messages[] = [
            'role'    => 'user',
            'content' => $message,
        ];

        return $messages;
    }
}
