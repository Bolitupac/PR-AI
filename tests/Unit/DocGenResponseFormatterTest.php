<?php

namespace Tests\Unit;

use App\Services\DocGen\DocGenResponseFormatter;
use PHPUnit\Framework\TestCase;

class DocGenResponseFormatterTest extends TestCase
{
    public function test_it_preserves_visible_text_outside_doc_preview(): void
    {
        $formatter = new DocGenResponseFormatter();

        $formatted = $formatter->normalize("Here is your draft.\n[DOC_PREVIEW]\n# Title\n\nBody\n[/DOC_PREVIEW]\n[DOC_READY]");

        $this->assertStringContainsString('Here is your draft.', $formatted);
        $this->assertStringContainsString('[DOC_PREVIEW]', $formatted);
        $this->assertStringContainsString("# Title\n\nBody", $formatted);
        $this->assertStringContainsString('[DOC_READY]', $formatted);
    }

    public function test_it_preserves_doc_question_blocks(): void
    {
        $formatter = new DocGenResponseFormatter();

        $formatted = $formatter->normalize("[DOC_QUESTION]{\"question\":\"Pick one\",\"options\":[\"A\",\"B\"]}[/DOC_QUESTION]\n# Draft");

        $this->assertStringContainsString('[DOC_QUESTION]', $formatted);
        $this->assertStringNotContainsString('[DOC_PREVIEW]', $formatted);
        $this->assertStringContainsString('# Draft', $formatted);
    }

    public function test_it_keeps_only_the_first_doc_question_block(): void
    {
        $formatter = new DocGenResponseFormatter();

        $formatted = $formatter->normalize(
            "[DOC_QUESTION]{\"question\":\"Pick one\",\"options\":[\"A\",\"B\"]}[/DOC_QUESTION]\n"
            ."[DOC_QUESTION]{\"question\":\"Second question\",\"options\":[\"X\",\"Y\"]}[/DOC_QUESTION]"
        );

        $this->assertSame(1, preg_match_all('/\[DOC_QUESTION\]/', $formatted));
        $this->assertStringContainsString('Pick one', $formatted);
        $this->assertStringNotContainsString('Second question', $formatted);
    }
}
