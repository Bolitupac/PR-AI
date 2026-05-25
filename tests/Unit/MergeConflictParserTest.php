<?php

namespace Tests\Unit;

use App\Services\Vcs\MergeConflictParser;
use PHPUnit\Framework\TestCase;

class MergeConflictParserTest extends TestCase
{
    public function test_it_parses_real_conflict_markers(): void
    {
        $parser = new MergeConflictParser();
        $content = <<<'TXT'
before
<<<<<<< HEAD
ours line
=======
theirs line
>>>>>>> feature/x
after
TXT;

        $hunks = $parser->parseHunks($content);

        $this->assertCount(1, $hunks);
        $this->assertSame('ours line', $hunks[0]['ours_snippet']);
        $this->assertSame('theirs line', $hunks[0]['theirs_snippet']);
        $this->assertStringContainsString('<<<<<<< HEAD', $hunks[0]['raw_marker_block']);
    }

    public function test_it_returns_empty_when_no_markers(): void
    {
        $parser = new MergeConflictParser();

        $this->assertSame([], $parser->parseHunks('echo "clean file";'));
        $this->assertSame([], $parser->parseFiles([
            ['path' => 'src/a.php', 'content' => 'no markers here'],
        ]));
    }

    public function test_it_does_not_fabricate_hunks_from_clean_pr_head_files(): void
    {
        $parser = new MergeConflictParser();
        $files = $parser->parseFiles([
            ['path' => 'README.md', 'content' => "# Title\n\nNormal content without conflict markers.\n"],
            ['path' => 'app/Foo.php', 'content' => "<?php\n\nclass Foo {}\n"],
        ]);

        $this->assertSame([], $files);
    }
}
