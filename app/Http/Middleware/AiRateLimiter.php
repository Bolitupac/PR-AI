<?php

namespace App\Http\Middleware;

use App\Models\TokenUsage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AiRateLimiter
{
    /**
     * Enforce per-user rate limits on AI endpoints.
     *
     * Tracks request count and estimated token usage per day / per week.
     * Returns 429 with retry-after headers when a cap is exceeded.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('rate_limits.enabled', true)) {
            return $next($request);
        }

        $user = Auth::user();
        if (! $user) {
            return $next($request);
        }

        $dryRun = (bool) config('rate_limits.dry_run', false);

        $today = now()->toDateString();
        $weekKey = now()->startOfWeek()->toDateString();

        $usage = TokenUsage::firstOrCreate(
            ['user_id' => $user->id, 'usage_date' => $today],
            ['week_key' => $weekKey, 'request_count' => 0, 'tokens_used' => 0]
        );

        // ── Check daily request cap ──
        $maxRequestsPerDay = (int) config('rate_limits.max_requests_per_day', 0);
        if ($maxRequestsPerDay > 0 && $usage->request_count >= $maxRequestsPerDay) {
            if (! $dryRun) {
                return $this->tooManyRequests('Daily request limit reached.');
            }
        }

        // ── Check daily token cap ──
        $maxTokensPerDay = (int) config('rate_limits.max_tokens_per_day', 0);
        if ($maxTokensPerDay > 0 && $usage->tokens_used >= $maxTokensPerDay) {
            if (! $dryRun) {
                return $this->tooManyRequests('Daily token budget exhausted.');
            }
        }

        // ── Check weekly token cap ──
        $maxTokensPerWeek = (int) config('rate_limits.max_tokens_per_week', 0);
        if ($maxTokensPerWeek > 0) {
            $weekUsage = TokenUsage::where('user_id', $user->id)
                ->where('week_key', $weekKey)
                ->sum('tokens_used');

            if ($weekUsage >= $maxTokensPerWeek) {
                if (! $dryRun) {
                    return $this->tooManyRequests('Weekly token budget exhausted.');
                }
            }
        }

        // ── Increment counters ──
        if (! $dryRun) {
            $usage->increment('request_count');

            // Estimate tokens from the request body. We'll also track
            // response tokens after the request completes.
            $estimatedTokens = $this->estimateTokens($request);
            if ($estimatedTokens > 0) {
                $usage->increment('tokens_used', $estimatedTokens);
            }
        }

        // Attach usage record so post-request hooks (or response) can update
        $request->attributes->set('ai_rate_limit_usage', $usage);

        $response = $next($request);

        // ── Add rate-limit headers ──
        $remaining = max(0, $maxRequestsPerDay - $usage->request_count);
        $response->headers->set('X-RateLimit-Limit', (string) $maxRequestsPerDay);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        if ($maxTokensPerDay > 0) {
            $tokenRemaining = max(0, $maxTokensPerDay - (int) $usage->tokens_used);
            $response->headers->set('X-TokenLimit-Daily', (string) $maxTokensPerDay);
            $response->headers->set('X-TokenLimit-Remaining', (string) $tokenRemaining);
        }

        return $response;
    }

    /**
     * Rough token estimation from the request payload.
     * We count the 'messages' content and 'diff_text' for audit endpoints.
     */
    private function estimateTokens(Request $request): int
    {
        $charCount = 0;

        // Chat endpoints send a "message" field
        if ($request->has('message')) {
            $charCount += strlen((string) $request->input('message', ''));
        }

        // History payloads
        if ($request->has('history') && is_array($request->input('history'))) {
            foreach ($request->input('history') as $item) {
                $charCount += strlen((string) ($item['content'] ?? ''));
            }
        }

        // Audit diff endpoints
        if ($request->has('diff_text')) {
            $charCount += strlen((string) $request->input('diff_text', ''));
        }

        // Follow-up suggestions
        if ($request->has('assistant_text')) {
            $charCount += strlen((string) $request->input('assistant_text', ''));
        }
        if ($request->has('user_text')) {
            $charCount += strlen((string) $request->input('user_text', ''));
        }

        $multiplier = (float) config('rate_limits.chars_to_tokens_multiplier', 0.30);

        return (int) ceil($charCount * $multiplier);
    }

    private function tooManyRequests(string $message): Response
    {
        $retryAfter = now()->endOfDay()->diffInSeconds(now());

        return response()->json([
            'message' => 'Rate limit exceeded. ' . $message,
            'retry_after_seconds' => $retryAfter,
        ], 429, [
            'Retry-After' => (string) $retryAfter,
        ]);
    }
}
