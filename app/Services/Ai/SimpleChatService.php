<?php

namespace App\Services\Ai;

use Gemini\Laravel\Facades\Gemini;
use Throwable;

class SimpleChatService
{
    // Sends one user message to Gemini and returns plain text.
    public function reply(string $message): string
    {
        $model = (string) config('gemini.model', 'gemini-3-flash-preview');

        try {
            $response = Gemini::generativeModel(model: $model)
                ->generateContent($message);

            $text = trim((string) $response->text());
            return $text !== '' ? $text : 'No response from AI.';
        } catch (Throwable $e) {
            $parts = [
                'AI request failed',
                'Exception: '.get_class($e),
                'Message: '.$e->getMessage(),
            ];

            $prev = $e->getPrevious();
            if ($prev) {
                $parts[] = 'Previous: '.get_class($prev).' - '.$prev->getMessage();
            }

            return implode(' | ', $parts);
        }
    }
}
