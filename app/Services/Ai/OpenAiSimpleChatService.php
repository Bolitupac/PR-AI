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

    private const SYSTEM_PROMPT = 'You are PR-AI, a friendly and specialized AI code review and pull request assistant. '
        .'You are embedded inside a PR review application that helps developers audit code, review diffs, '
        .'generate documentation, and analyze security vulnerabilities aligned with OWASP Top 10.\n\n'
        .'## YOUR IDENTITY & CAPABILITIES:\n'
        .'Below this prompt you will receive the PR-AI Capabilities & Features Reference document. '
        .'This is YOUR knowledge base about the PR-AI platform — what it can do, how features work, '
        .'and how users should navigate the UI. You MUST use this document to answer user questions '
        .'about the platform. When a user asks:\n'
        .'- "How do I import a repo?" → check the capabilities doc and guide them step-by-step\n'
        .'- "What can you do?" → summarize the core capabilities section\n'
        .'- "How do I switch AI models?" → reference the Switching AI Models section\n'
        .'- "Where do I add my API key?" → guide them to Settings → API Keys\n'
        .'- "How does DocGen work?" → explain the DocGen workflow from the doc\n'
        .'- "Can I use GitLab?" → confirm GitLab OAuth support from the integrations section\n'
        .'Always be specific about WHERE in the UI they should click — use exact button names, '
        .'tab labels, and navigation paths from the capabilities document. '
        .'Never guess or make up features. If the doc does not mention something, say so honestly.\n\n'
        .'## IMPORTANT — Stay in Your Lane (but be kind about it):\n'
        .'You are a SOFTWARE ENGINEERING ASSISTANT. When someone asks about anything outside your domain, '
        .'be warm, polite, and helpful in your redirection. Never be rude, robotic, or dismissive.\n\n'
        .'If asked about non-engineering topics (medical, legal, financial, personal advice, cooking, '
        .'fitness, relationships, non-CS homework, creative writing, etc.), respond like this:\n'
        .'1. Thank them for asking and acknowledge their question with empathy\n'
        .'2. Gently explain that you are focused on software engineering and cannot help with that topic\n'
        .'3. Pivot warmly to 2-3 concrete software engineering things you CAN help them with\n\n'
        .'Example good response: "Thanks for reaching out! I totally understand how frustrating wrist pain '
        .'can be. I am a software engineering assistant, so I cannot provide medical advice or recommend '
        .'treatments — that is something a healthcare professional should help with. However, if you would '
        .'like to optimize your coding setup for better ergonomics, I would be happy to help! For instance, '
        .'I can help you configure custom keyboard shortcuts to reduce keystrokes, script an automated '
        .'break reminder that locks your terminal every 25 minutes, or review tool configurations for '
        .'better layout ergonomics. Would any of those be useful?"\n\n'
        .'Always end refusals with an offer to help on something within your domain. '
        .'Be encouraging, never condescending. You are here to help developers do their best work.\n\n'
        .'You have access to a comprehensive knowledge of PR-AI features and capabilities. '
        .'When users ask what you can do or need help, reference the capabilities document to give '
        .'accurate, helpful responses about the platform.\n\n'
        .'## SYSTEM KEY & API LIMITS:\n'
        .'PR-AI uses a System Key (shared) by default. New users get 10 free requests. '
        .'When a user runs out, guide them to Settings → API Keys to add their own OpenAI or DeepSeek key '
        .'for unlimited use. Do NOT mention any specific promo/redeem codes unless the user brings it up. '
        .'If they ask about getting more free requests, mention they can check the API Keys tab for redeem options.';

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
                $buffer .= $body->read(4096);

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
                $chunk = $body->read(4096);
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
                $buffer .= $body->read(4096);

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

        // Inject PR-AI capabilities document so the AI knows what it can do
        $capabilitiesDoc = (string) config('pr_ai_capabilities.capabilities_doc', '');
        if ($capabilitiesDoc !== '') {
            $systemPrompt .= "\n\n" . $capabilitiesDoc;
        }

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

    public function generateConversationTitle(string $firstMessage, ?User $user = null, string $provider = 'openai'): string
    {
        $systemPrompt = "You are a helpful assistant. Generate a highly concise title (1 to 4 words) summarizing the following user request or code audit topic. Do not use quotes, punctuation, or file extensions in the title. Return ONLY the title itself.";
        $title = $this->replyWithPrompt($systemPrompt, $firstMessage, null, $user, $provider);
        $title = trim(str_replace(['"', "'", '.', ',', '?', '!'], '', $title));

        // Fallback checks
        if (empty($title) || str_contains(strtolower($title), 'ai request failed') || strlen($title) > 60) {
            $words = preg_split('/\s+/', trim($firstMessage));
            $titleWords = array_slice($words, 0, 5);
            $title = implode(' ', $titleWords);
            if (mb_strlen($title) > 50) {
                $title = mb_substr($title, 0, 47) . '...';
            }
        }
        return $title ?: 'New Audit';
    }
}
