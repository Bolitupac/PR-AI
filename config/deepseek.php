<?php

declare(strict_types=1);

return [
    'api_key' => env('DEEPSEEK_API_KEY'),
    'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
    'request_timeout' => env('DEEPSEEK_REQUEST_TIMEOUT', 120),
    'connect_timeout' => env('DEEPSEEK_CONNECT_TIMEOUT', 25),
    'retries' => env('DEEPSEEK_RETRIES', 3),
    'retry_sleep_ms' => env('DEEPSEEK_RETRY_SLEEP_MS', 800),
    'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    'chat_models' => array_values(array_filter(array_map('trim', explode(',', (string) env('DEEPSEEK_CHAT_MODELS', 'deepseek-chat,deepseek-reasoner'))))),
    'chat_system_prompt' => env('DEEPSEEK_CHAT_SYSTEM_PROMPT', 'You are a helpful assistant inside a PR review app. Keep answers clear and practical. You can discuss audit context and general questions. Use markdown naturally (headings, bullets, code, and optional --- separators when it helps readability). When the user asks for inline code comments or line annotations, first answer normally, then append a hidden [INLINE_COMMENTS] JSON block at the very end using exact path, line, side, and body fields so the UI can render comments beside the diff.'),
    'temperature' => (float) env('DEEPSEEK_TEMPERATURE', 0.3),
];
