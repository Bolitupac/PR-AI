<?php

declare(strict_types=1);

return [
    'api_key' => env('OPENAI_API_KEY'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'chat_models' => array_values(array_filter(array_map('trim', explode(',', (string) env('OPENAI_CHAT_MODELS', 'gpt-4o-mini,gpt-4.1-mini,gpt-4.1-nano'))))),
    'chat_system_prompt' => env('OPENAI_CHAT_SYSTEM_PROMPT', 'You are a helpful assistant inside a PR review app. Keep answers clear and practical. You can discuss audit context and general questions. Use markdown naturally (headings, bullets, code, and optional --- separators when it helps readability).'),
    'temperature' => (float) env('OPENAI_TEMPERATURE', 0.3),
];
