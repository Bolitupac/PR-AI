<?php

namespace App\Services\Ai;

class AiResponseValidator
{
    // Parses JSON from model output and normalizes to stable schema.
    public function normalize(string $rawText, string $mode): array
    {
        $decoded = $this->decodeJson($rawText);

        if (!is_array($decoded)) {
            return $this->fallback($mode, 'Model returned invalid JSON.');
        }

        return [
            'schema_version' => '1.0',
            'mode' => $mode,
            'summary_md' => (string) ($decoded['summary_md'] ?? ''),
            'severity_counts' => $this->normalizeSeverityCounts($decoded['severity_counts'] ?? []),
            'findings' => $this->normalizeFindings($decoded['findings'] ?? []),
            'recommendations_md' => (string) ($decoded['recommendations_md'] ?? ''),
            'answer_md' => (string) ($decoded['answer_md'] ?? ''),
            'next_actions' => $this->normalizeTextArray($decoded['next_actions'] ?? []),
            'errors' => $this->normalizeTextArray($decoded['errors'] ?? []),
        ];
    }

    // Builds a predictable error payload for the UI.
    public function fallback(string $mode, string $error): array
    {
        return [
            'schema_version' => '1.0',
            'mode' => $mode,
            'summary_md' => '',
            'severity_counts' => [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0,
                'info' => 0,
            ],
            'findings' => [],
            'recommendations_md' => '',
            'answer_md' => '',
            'next_actions' => [],
            'errors' => [$error],
        ];
    }

    // Handles plain JSON and fenced ```json blocks.
    private function decodeJson(string $rawText): ?array
    {
        $rawText = trim($rawText);

        if ($rawText === '') {
            return null;
        }

        $decoded = json_decode($rawText, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/```json\s*(.*?)\s*```/is', $rawText, $matches)) {
            $decoded = json_decode(trim($matches[1]), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    // Forces all severity fields to integers.
    private function normalizeSeverityCounts(mixed $value): array
    {
        $counts = is_array($value) ? $value : [];

        return [
            'critical' => (int) ($counts['critical'] ?? 0),
            'high' => (int) ($counts['high'] ?? 0),
            'medium' => (int) ($counts['medium'] ?? 0),
            'low' => (int) ($counts['low'] ?? 0),
            'info' => (int) ($counts['info'] ?? 0),
        ];
    }

    // Normalizes finding objects for consistent rendering.
    private function normalizeFindings(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($item) => is_array($item))
            ->map(fn ($item) => [
                'title' => (string) ($item['title'] ?? 'Untitled finding'),
                'severity' => (string) ($item['severity'] ?? 'info'),
                'file' => (string) ($item['file'] ?? ''),
                'line' => is_numeric($item['line'] ?? null) ? (int) $item['line'] : null,
                'risk_md' => (string) ($item['risk_md'] ?? ''),
                'fix_md' => (string) ($item['fix_md'] ?? ''),
            ])
            ->values()
            ->all();
    }

    // Keeps only non-empty strings.
    private function normalizeTextArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($item) => is_string($item) ? trim($item) : '')
            ->filter(fn ($item) => $item !== '')
            ->values()
            ->all();
    }
}

