<?php

namespace Tests\Unit;

use App\Services\DocGen\DocGenIntentDetector;
use PHPUnit\Framework\TestCase;

class DocGenIntentDetectorTest extends TestCase
{
    public function test_it_matches_document_requests(): void
    {
        $detector = new DocGenIntentDetector();

        $this->assertTrue($detector->matches('Write a report for this PR'));
        $this->assertTrue($detector->matches('Generate a technical documentation draft'));
        $this->assertTrue($detector->matches('Create a project brief'));
    }

    public function test_it_ignores_regular_chat_requests(): void
    {
        $detector = new DocGenIntentDetector();

        $this->assertFalse($detector->matches('Explain this function'));
        $this->assertFalse($detector->matches('Fix the failing test'));
        $this->assertFalse($detector->matches('Review the diff and leave comments'));
    }
}
