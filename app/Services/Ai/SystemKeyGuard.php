<?php

namespace App\Services\Ai;

use App\Models\User;

class SystemKeyGuard
{
    /**
     * Check whether a user can make an AI request using the system (shared) key.
     *
     * Returns [ok => bool, message => string, credits_remaining => int].
     */
    public function check(User $user, string $provider = 'openai'): array
    {
        // If user has a personal key for this provider, no system-key limit applies.
        if ($this->userHasPersonalKey($user, $provider)) {
            return [
                'ok' => true,
                'message' => '',
                'credits_remaining' => $user->system_key_credits,
            ];
        }

        $credits = max(0, (int) $user->system_key_credits);

        if ($credits <= 0) {
            return [
                'ok' => false,
                'message' => 'You\'ve used all your System Key requests. Add your own API key in Settings → API Keys for unlimited use.',
                'credits_remaining' => 0,
            ];
        }

        return [
            'ok' => true,
            'message' => '',
            'credits_remaining' => $credits,
        ];
    }

    /**
     * Consume one credit from the user's system key balance.
     */
    public function consume(User $user): void
    {
        if ($user->system_key_credits > 0) {
            $user->decrement('system_key_credits');
        }
    }

    private function userHasPersonalKey(User $user, string $provider): bool
    {
        return match ($provider) {
            'deepseek' => $user->hasCustomDeepSeekKey(),
            default => $user->hasCustomOpenAiKey(),
        };
    }
}
