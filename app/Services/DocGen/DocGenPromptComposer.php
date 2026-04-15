<?php

namespace App\Services\DocGen;

class DocGenPromptComposer
{
    public function buildMessages(string $message, array $history = [], string $activeAuditContext = '', bool $docGenModeActive = true): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt($docGenModeActive),
            ],
        ];

        if ($activeAuditContext !== '') {
            $messages[] = [
                'role' => 'system',
                'content' => "ACTIVE AUDIT CONTEXT:\n{$activeAuditContext}",
            ];
        }

        foreach ($history as $item) {
            $role = (string) ($item['role'] ?? '');
            $content = trim((string) ($item['content'] ?? ''));
            if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }

            $messages[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        return $messages;
    }

    private function systemPrompt(bool $docGenModeActive): string
    {
        return implode("\n", [
            'You are DocGen, a document generation assistant inside a PR review app.',
            $docGenModeActive ? '[DOCGEN_MODE:ACTIVE]' : '[DOCGEN_MODE:INACTIVE]',
            'Your job is to help the user draft a structured working document through iterative updates.',
            'When DocGen mode is active and the user asks for a document, generate a longer and more complete document than a usual chat answer.',
            'For document output, put only the document content inside [DOC_PREVIEW]...[/DOC_PREVIEW].',
            'Any conversational note like "here is your document" must stay outside DOC_PREVIEW tags.',
            'If you need user input, output exactly one [DOC_QUESTION]...[/DOC_QUESTION] block in that response and nothing else inside that question block.',
            'Ask questions one by one across separate responses, never multiple questions in the same response.',
            'Each DOC_QUESTION block must contain valid JSON with keys question and options.',
            'Options must be short button-friendly strings.',
            'If you are generating a document, include one [DOC_FORMATS]...[/DOC_FORMATS] block with JSON like {"default":"pdf","allowed":["pdf","docx","md"]}.',
            'Supported formats are: pdf, docx, md, txt, html, json, yaml, csv, xlsx, pptx, tex.',
            'When the document draft is complete enough to download, append [DOC_READY].',
            'Not every DocGen reply must contain a document. If the reply is only questions or guidance, do not emit DOC_PREVIEW.',
            'Use markdown headings, lists, and tables where useful.',
        ]);
    }
}
