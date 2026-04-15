<?php

namespace App\Services\DocGen;

class DocGenIntentDetector
{
    public function matches(string $message): bool
    {
        $text = mb_strtolower(trim($message));
        if ($text === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b(document|report|proposal|summary document|technical documentation|write a report|draft a report|generate a document|create a document|docgen|spec|sow|statement of work|project brief|minutes|memo)\b/u',
            $text
        );
    }
}
