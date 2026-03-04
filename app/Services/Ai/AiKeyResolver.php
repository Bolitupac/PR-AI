<?php

namespace App\Services\Ai;

use App\Models\User;

class AiKeyResolver
{
    public function resolveFor(?User $user): string
    {
        $default = trim((string) config('openai.api_key', ''));
        if (!$user) {
            return $default;
        }

        $mode = (string) ($user->ai_key_mode ?? 'developer');
        if ($mode === 'personal' && $user->hasCustomOpenAiKey()) {
            return trim((string) $user->custom_openai_api_key);
        }

        return $default;
    }
}

