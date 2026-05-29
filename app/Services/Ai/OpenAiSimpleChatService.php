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

    private const SYSTEM_PROMPT = 'You are a helpful assistant inside a PR review app. '
        .'Give clear, practical answers. Use prior chat context when available. '
        .'You can discuss both audit context and general questions.';

    // Resolves config values for a given provider (openai or deepseek).
    private function resolveConfig(string $provider): array
    {
        $baseUrl = (string) config("{$provider}.base_url", 'https://api.openai.com/v1');
        $defaultModel = (string) config("{$provider}.model", 'gpt-4o-mini');
        $allowedModels = (array) config("{$provider}.chat_models", [$defaultModel]);
        $temperature = (float) config("{$provider}.temperature", 0.3);
        $timeout = (int) config("{$provider}.request_timeout", 30);
        $connectTimeout = (int) config("{$provider}.connect_timeout", 20);
        $retries = (int) config("{$provider}.retries", 2);
        $retrySleepMs = (int) config("{$provider}.retry_sleep_ms", 600);

        return compact('baseUrl', 'defaultModel', 'allowedModels', 'temperature', 'timeout', 'connectTimeout', 'retries', 'retrySleepMs');
    }

    // Resolves which model to use from the selected value.
    private function resolveModel(?string $selectedModel, array $config): string
    {
        return in_array((string) $selectedModel, $config['allowedModels'], true)
            ? (string) $selectedModel
            : $config['defaultModel'];
    }

    // Sends one user message to the AI and returns plain text.
    public function reply(string $message, ?string $selectedModel = null, ?User $user = null, string $provider = 'openai'): string
    {
        return $this->replyWithPrompt(self::SYSTEM_PROMPT, $message, $selectedModel, $user, $provider);
    }

    // Sends one user message plus short chat history to preserve context.
    public function replyWithHistory(string $message, array $history = [], ?string $selectedModel = null, ?User $user = null, string $provider = 'openai'): string
    {
        $messages = $this->buildHistoryMessages($message, $history, $user, $provider);

        return $this->sendMessages($messages, $selectedModel, $user, $provider);
    }

    // Streams assistant tokens as they arrive from the AI chat completions SSE.
    public function streamReplyWithHistory(
        string $message,
        array $history,
        ?string $selectedModel,
        ?User $user,
        callable $onToken,
        ?callable $onError = null,
        string $provider = 'openai'
    ): void {
        $config = $this->resolveConfig($provider);
        $apiKey = $this->aiKeyResolver->resolveFor($user, $provider);

        if ($apiKey === '') {
            if ($onError) {
                $onError('AI request failed: API key is missing for ' . $provider . '.');
            }
            return;
        }

        $url = rtrim($config['baseUrl'], '/') . '/chat/completions';
        $messages = $this->buildHistoryMessages($message, $history, $user, $provider);
        $model = $this->resolveModel($selectedModel, $config);

        try {
            $response = Http::withToken($apiKey)
                ->withOptions(['stream' => true])
                ->connectTimeout($config['connectTimeout'])
                ->timeout($config['timeout'])
                ->retry($config['retries'], $config['retrySleepMs'], throw: false)
                ->send('POST', $url, [
                    'headers' => ['Accept' => 'text/event-stream'],
                    'json' => [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => $config['temperature'],
                        'stream' => true,
                    ],
                ]);

            if ($response->failed()) {
                if ($onError) {
                    $onError('AI request failed: HTTP ' . $response->status() . ' ' . $response->body());
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
                    'Exception: ' . get_class($e),
                    'Message: ' . $e->getMessage(),
                ];
                $prev = $e->getPrevious();
                if ($prev) {
                    $parts[] = 'Previous: ' . get_class($prev) . ' - ' . $prev->getMessage();
                }
                $onError(implode(' | ', $parts));
            }
        }
    }

    // Proxies raw AI SSE chunks to caller for true real-time UI streaming.
    public function streamRawWithHistory(
        string $message,
        array $history,
        ?string $selectedModel,
        ?User $user,
        callable $onChunk,
        ?callable $onError = null,
        string $provider = 'openai'
    ): void {
        $config = $this->resolveConfig($provider);
        $apiKey = $this->aiKeyResolver->resolveFor($user, $provider);

        if ($apiKey === '') {
            if ($onError) {
                $onError('AI request failed: API key is missing for ' . $provider . '.');
            }
            return;
        }

        $url = rtrim($config['baseUrl'], '/') . '/chat/completions';
        $messages = $this->buildHistoryMessages($message, $history, $user, $provider);
        $model = $this->resolveModel($selectedModel, $config);

        try {
            $response = Http::withToken($apiKey)
                ->withOptions(['stream' => true])
                ->connectTimeout($config['connectTimeout'])
                ->timeout($config['timeout'])
                ->retry($config['retries'], $config['retrySleepMs'], throw: false)
                ->send('POST', $url, [
                    'headers' => ['Accept' => 'text/event-stream'],
                    'json' => [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => $config['temperature'],
                        'stream' => true,
                    ],
                ]);

            if ($response->failed()) {
                if ($onError) {
                    $onError('AI request failed: HTTP ' . $response->status() . ' ' . $response->body());
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
                    'Exception: ' . get_class($e),
                    'Message: ' . $e->getMessage(),
                ];
                $prev = $e->getPrevious();
                if ($prev) {
                    $parts[] = 'Previous: ' . get_class($prev) . ' - ' . $prev->getMessage();
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
        ?callable $onError = null,
        string $provider = 'openai'
    ): void {
        $config = $this->resolveConfig($provider);
        $apiKey = $this->aiKeyResolver->resolveFor($user, $provider);

        if ($apiKey === '') {
            if ($onError) {
                $onError('AI request failed: API key is missing for ' . $provider . '.');
            }
            return;
        }

        $url = rtrim($config['baseUrl'], '/') . '/chat/completions';
        $model = $this->resolveModel($selectedModel, $config);

        try {
            $response = Http::withToken($apiKey)
                ->withOptions(['stream' => true])
                ->connectTimeout($config['connectTimeout'])
                ->timeout($config['timeout'])
                ->retry($config['retries'], $config['retrySleepMs'], throw: false)
                ->send('POST', $url, [
                    'headers' => ['Accept' => 'text/event-stream'],
                    'json' => [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => $config['temperature'],
                        'stream' => true,
                    ],
                ]);

            if ($response->failed()) {
                if ($onError) {
                    $onError('AI request failed: HTTP ' . $response->status() . ' ' . $response->body());
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
                    'Exception: ' . get_class($e),
                    'Message: ' . $e->getMessage(),
                ];
                $prev = $e->getPrevious();
                if ($prev) {
                    $parts[] = 'Previous: ' . get_class($prev) . ' - ' . $prev->getMessage();
                }
                $onError(implode(' | ', $parts));
            }
        }
    }

    // Sends one prompt pair (system + user) and returns plain text.
    public function replyWithPrompt(string $systemPrompt, string $userPrompt, ?string $selectedModel = null, ?User $user = null, string $provider = 'openai'): string
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
        ], $selectedModel, $user, $provider);
    }

    public function replyWithMessages(array $messages, ?string $selectedModel = null, ?User $user = null, string $provider = 'openai'): string
    {
        return $this->sendMessages($messages, $selectedModel, $user, $provider);
    }

    // Sends prepared messages array to the AI and returns plain text.
    private function sendMessages(array $messages, ?string $selectedModel = null, ?User $user = null, string $provider = 'openai'): string
    {
        $config = $this->resolveConfig($provider);
        $apiKey = $this->aiKeyResolver->resolveFor($user, $provider);

        if ($apiKey === '') {
            return 'AI request failed: API key is missing for ' . $provider . '.';
        }

        $url = rtrim($config['baseUrl'], '/') . '/chat/completions';
        $model = $this->resolveModel($selectedModel, $config);

        try {
            $response = Http::connectTimeout($config['connectTimeout'])
                ->timeout($config['timeout'])
                ->retry($config['retries'], $config['retrySleepMs'], throw: false)
                ->withToken($apiKey)
                ->post($url, [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $config['temperature'],
                ]);

            if ($response->failed()) {
                return 'AI request failed: HTTP ' . $response->status() . ' ' . $response->body();
            }

            $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            return $text !== '' ? $text : 'No response from AI.';
        } catch (Throwable $e) {
            $parts = [
                'AI request failed',
                'Exception: ' . get_class($e),
                'Message: ' . $e->getMessage(),
            ];

            $prev = $e->getPrevious();
            if ($prev) {
                $parts[] = 'Previous: ' . get_class($prev) . ' - ' . $prev->getMessage();
            }

            return implode(' | ', $parts);
        }
    }

    private function buildHistoryMessages(string $message, array $history, ?User $user = null, string $provider = 'openai'): array
    {
        $basePrompt = (string) config("{$provider}.chat_system_prompt", self::SYSTEM_PROMPT);

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
                $prefsExtra = "\n\nUSER AI PREFERENCES:\n" . implode("\n", $parts);
            }
        }

        $systemPrompt = $basePrompt . $prefsExtra;

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
