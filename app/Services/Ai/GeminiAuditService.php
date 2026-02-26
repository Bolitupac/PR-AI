<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class GeminiAuditService
{
    // Sends audit request to Gemini and returns raw model text.
    public function runAudit(array $context): string
    {
        $prompt = $this->buildAuditPrompt($context);

        return $this->generate($prompt);
    }

    // Sends chat follow-up request to Gemini using same PR context.
    public function runChat(array $context, string $question): string
    {
        $prompt = $this->buildChatPrompt($context, $question);

        return $this->generate($prompt);
    }

    // Calls Gemini generateContent API.
    private function generate(string $prompt): string
    {
        $apiKey = (string) config('gemini.api_key');
        $model = (string) config('gemini.model', 'gemini-1.5-flash');
        $baseUrl = (string) (config('gemini.base_url') ?: 'https://generativelanguage.googleapis.com');
        $timeout = (int) config('gemini.request_timeout', 30);

        if ($apiKey === '') {
            return '';
        }

        $url = rtrim($baseUrl, '/')."/v1beta/models/{$model}:generateContent";

        $response = Http::timeout($timeout)
            ->withQueryParameters(['key' => $apiKey])
            ->post($url, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->failed()) {
            return '';
        }

        return (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
    }

    // Creates strict audit prompt with schema contract.
    private function buildAuditPrompt(array $context): string
    {
        return $this->baseContract('audit')."\n\n".
            "Audit this pull request context.\n\n".
            "PR_METADATA:\n".$this->toJson($context['details'] ?? [])."\n\n".
            "ISSUE_COMMENTS:\n".$this->toJson($context['issue_comments'] ?? [])."\n\n".
            "REVIEW_COMMENTS:\n".$this->toJson($context['review_comments'] ?? [])."\n\n".
            "DIFF_CHANGES_ONLY:\n".$this->truncate((string) ($context['diff_changes_only'] ?? ''), 120000)."\n\n".
            "Return JSON only.";
    }

    // Creates strict chat prompt with schema contract.
    private function buildChatPrompt(array $context, string $question): string
    {
        return $this->baseContract('chat')."\n\n".
            "Answer this user question about the PR.\n".
            "USER_QUESTION:\n".$question."\n\n".
            "PR_METADATA:\n".$this->toJson($context['details'] ?? [])."\n\n".
            "ISSUE_COMMENTS:\n".$this->toJson($context['issue_comments'] ?? [])."\n\n".
            "REVIEW_COMMENTS:\n".$this->toJson($context['review_comments'] ?? [])."\n\n".
            "DIFF_CHANGES_ONLY:\n".$this->truncate((string) ($context['diff_changes_only'] ?? ''), 120000)."\n\n".
            "Return JSON only.";
    }

    // Defines stable output schema for frontend rendering.
    private function baseContract(string $mode): string
    {
        return "You are a pull request audit assistant.\n".
            "Return only raw JSON, no backticks, no prose outside JSON.\n".
            "Schema:\n".
            "{\n".
            "  \"schema_version\": \"1.0\",\n".
            "  \"mode\": \"{$mode}\",\n".
            "  \"summary_md\": \"string\",\n".
            "  \"severity_counts\": {\"critical\":0,\"high\":0,\"medium\":0,\"low\":0,\"info\":0},\n".
            "  \"findings\": [\n".
            "    {\"title\":\"string\",\"severity\":\"critical|high|medium|low|info\",\"file\":\"string\",\"line\":0,\"risk_md\":\"string\",\"fix_md\":\"string\"}\n".
            "  ],\n".
            "  \"recommendations_md\": \"string\",\n".
            "  \"answer_md\": \"string\",\n".
            "  \"next_actions\": [\"string\"],\n".
            "  \"errors\": [\"string\"]\n".
            "}\n".
            "Use markdown only inside *_md string fields.";
    }

    private function toJson(array $value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function truncate(string $text, int $limit): string
    {
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) : $text;
    }
}
