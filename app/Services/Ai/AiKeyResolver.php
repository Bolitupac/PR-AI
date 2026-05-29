<?php

namespace App\Services\Ai;

use App\Models\User;

class AiKeyResolver
{
    public function resolveFor(?User $user, string $provider = 'openai'): string
    {
        $default = trim((string) config("{$provider}.api_key", ''));

        if (!$user) {
            return $default;
        }

        $mode = (string) ($user->ai_key_mode ?? 'developer');

        if ($mode === 'personal') {
            if ($provider === 'deepseek' && $user->hasCustomDeepSeekKey()) {
                return trim((string) $user->custom_deepseek_api_key);
            }

            if ($provider === 'openai' && $user->hasCustomOpenAiKey()) {
                return trim((string) $user->custom_openai_api_key);
            }
        }

        return $default;
    }
}
