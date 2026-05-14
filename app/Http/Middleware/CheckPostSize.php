<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detects when PHP silently drops POST data because the request body
 * exceeded post_max_size. Returns a clean 413 JSON error instead of
 * a confusing 422 validation failure.
 */
class CheckPostSize
{
    public function handle(Request $request, Closure $next): Response
    {
        $contentLength = (int) ($request->server('CONTENT_LENGTH') ?? 0);

        if ($contentLength > 0 && $request->isMethod('POST') && empty($_POST) && empty($_FILES)) {
            $maxBytes = $this->convertToBytes((string) ini_get('post_max_size'));

            if ($maxBytes > 0 && $contentLength > $maxBytes) {
                return response()->json([
                    'message' => 'The diff is too large to process. The request body exceeded the server limit of ' . ini_get('post_max_size') . '. Try auditing a smaller branch or a specific pull request instead.',
                    'error' => 'payload_too_large',
                ], 413);
            }
        }

        return $next($request);
    }

    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }
        $unit = strtolower($value[strlen($value) - 1]);
        $bytes = (int) $value;
        return match ($unit) {
            'g' => $bytes * 1024 * 1024 * 1024,
            'm' => $bytes * 1024 * 1024,
            'k' => $bytes * 1024,
            default => $bytes,
        };
    }
}
