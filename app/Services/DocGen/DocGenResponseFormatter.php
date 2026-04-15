<?php

namespace App\Services\DocGen;

class DocGenResponseFormatter
{
    public function normalize(string $reply): string
    {
        $raw = trim($reply);
        if ($raw === '') {
            return "[DOC_PREVIEW]\n# Untitled Document\n\nNo content was generated.\n[/DOC_PREVIEW]\n[DOC_READY]";
        }

        preg_match('/\[DOC_PREVIEW\]([\s\S]*?)\[\/DOC_PREVIEW\]/i', $raw, $previewMatch);
        preg_match_all('/\[DOC_QUESTION\]([\s\S]*?)\[\/DOC_QUESTION\]/i', $raw, $questionMatches);
        preg_match('/\[DOC_FORMATS\]([\s\S]*?)\[\/DOC_FORMATS\]/i', $raw, $formatsMatch);

        $preview = isset($previewMatch[1]) ? trim((string) $previewMatch[1]) : '';
        $allQuestions = $questionMatches[0] ?? [];
        $questions = !empty($allQuestions) ? [reset($allQuestions)] : [];
        $formats = isset($formatsMatch[0]) ? trim((string) $formatsMatch[0]) : '';
        $ready = stripos($raw, '[DOC_READY]') !== false;
        $visible = trim($raw);

        if ($preview !== '') {
            $visible = trim(str_replace($previewMatch[0], '', $visible));
        }
        foreach ($allQuestions as $question) {
            $visible = trim(str_replace($question, '', $visible));
        }
        if ($formats !== '') {
            $visible = trim(str_replace($formats, '', $visible));
        }
        $visible = trim(str_ireplace('[DOC_READY]', '', $visible));

        $parts = [];
        if ($visible !== '') {
            $parts[] = $visible;
        }
        if ($preview !== '') {
            $parts[] = "[DOC_PREVIEW]\n{$preview}\n[/DOC_PREVIEW]";
        }

        if (!empty($questions)) {
            $parts[] = implode("\n", $questions);
        }
        if ($formats !== '') {
            $parts[] = $formats;
        }

        if ($ready || ($preview !== '' && empty($questions))) {
            $parts[] = '[DOC_READY]';
        }

        return trim(implode("\n", array_filter($parts)));
    }
}
