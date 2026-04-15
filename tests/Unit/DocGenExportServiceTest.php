<?php

namespace Tests\Unit;

use App\Services\DocGen\DocGenExportService;
use PHPUnit\Framework\TestCase;

class DocGenExportServiceTest extends TestCase
{
    private DocGenExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DocGenExportService();
    }

    public function test_it_exports_markdown(): void
    {
        $result = $this->service->export('md', "# Title\n\n- Item one", 'Sprint Report');

        $this->assertSame('sprint-report.md', $result['filename']);
        $this->assertStringContainsString('# Title', $result['content']);
        $this->assertSame('text/markdown; charset=UTF-8', $result['mime']);
    }

    public function test_it_exports_json_with_expected_payload(): void
    {
        $result = $this->service->export('json', "# Title\n\nBody", 'Sprint Report');

        $this->assertSame('sprint-report.json', $result['filename']);
        $this->assertSame('application/json', $result['mime']);
        $this->assertStringContainsString('"title": "sprint-report"', $result['content']);
        $this->assertStringContainsString('"markdown": "# Title\\n\\nBody"', $result['content']);
    }

    public function test_it_exports_pdf_signature(): void
    {
        $result = $this->service->export('pdf', "# Title\n\nBody", 'Sprint Report');

        $this->assertSame('sprint-report.pdf', $result['filename']);
        $this->assertSame('application/pdf', $result['mime']);
        $this->assertStringStartsWith('%PDF-', $result['content']);
    }

    public function test_it_exports_csv_rows(): void
    {
        $result = $this->service->export('csv', "# Title\n\nBody", 'Sprint Report');

        $this->assertSame('sprint-report.csv', $result['filename']);
        $this->assertStringContainsString('line_number,content', $result['content']);
        $this->assertStringContainsString('1,Title', $result['content']);
    }
}
