<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | AI Rate Limits — per-user hard caps
    |--------------------------------------------------------------------------
    |
    | These caps apply per authenticated user. When any limit is exceeded the
    | endpoint returns HTTP 429. Limits reset at midnight (daily) and Monday
    | midnight (weekly). Set any value to `null` to disable that cap.
    |
    */

    // Maximum AI requests per user per day
    'max_requests_per_day' => (int) env('AI_RATE_LIMIT_REQUESTS_PER_DAY', 200),

    // Maximum estimated tokens per user per day
    'max_tokens_per_day' => (int) env('AI_RATE_LIMIT_TOKENS_PER_DAY', 600_000),

    // Maximum estimated tokens per user per week
    'max_tokens_per_week' => (int) env('AI_RATE_LIMIT_TOKENS_PER_WEEK', 2_500_000),

    // Token estimation multiplier applied to prompt + response character counts.
    // ~1 token ≈ 4 characters for English text. We use 0.3 to be conservative
    // (slightly overestimates so users hit caps before providers do).
    'chars_to_tokens_multiplier' => 0.30,

    // Whether rate limiting is enabled at all
    'enabled' => (bool) env('AI_RATE_LIMIT_ENABLED', true),

    // If true, the limit headers are sent but requests are never blocked.
    // Useful for monitoring before enforcing.
    'dry_run' => (bool) env('AI_RATE_LIMIT_DRY_RUN', false),
];
