<?php

declare(strict_types=1);

return [
    'api_key' => env('OPENAI_API_KEY'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'temperature' => (float) env('OPENAI_TEMPERATURE', 0.3),
];

