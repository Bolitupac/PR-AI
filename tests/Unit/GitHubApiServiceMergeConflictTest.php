<?php

namespace Tests\Unit;

use App\Services\GitHubApiService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GitHubApiServiceMergeConflictTest extends TestCase
{
    private string $encryptedToken = '';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        $this->encryptedToken = Crypt::encryptString('ghp_test_token');
    }

    public function test_is_pull_request_merge_conflicted_requires_dirty_state(): void
    {
        $service = new GitHubApiService();

        $this->assertTrue($service->isPullRequestMergeConflicted([
            'mergeable' => false,
            'mergeable_state' => 'dirty',
        ]));

        $this->assertFalse($service->isPullRequestMergeConflicted([
            'mergeable' => false,
            'mergeable_state' => 'behind',
        ]));

        $this->assertFalse($service->isPullRequestMergeConflicted([
            'mergeable' => true,
            'mergeable_state' => 'clean',
        ]));
    }

    public function test_get_merge_conflicts_returns_metadata_only_without_hunks(): void
    {
        Http::fake([
            'api.github.com/repos/acme/demo/pulls/42' => Http::response([
                'number' => 42,
                'title' => 'Conflicting PR',
                'mergeable' => false,
                'mergeable_state' => 'dirty',
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature'],
            ], 200),
        ]);

        $service = new GitHubApiService();
        $result = $service->getMergeConflicts($this->encryptedToken, 'acme/demo', '42');

        $this->assertTrue($result['ok']);
        $this->assertSame('github_metadata_only', $result['data']['conflict_source']);
        $this->assertFalse($result['data']['has_hunks']);
        $this->assertSame([], $result['data']['files']);
        $this->assertTrue($result['data']['has_conflicts']);
        $this->assertStringContainsString('does not expose', $result['data']['message']);
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/contents/');
        });
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/pulls/42/files');
        });
    }

    public function test_get_recent_merge_conflicts_verifies_each_pr_via_detail_endpoint(): void
    {
        Http::fake([
            'api.github.com/search/issues*' => Http::response([
                'items' => [
                    [
                        'number' => 10,
                        'title' => 'Conflict PR',
                        'updated_at' => '2026-01-01T00:00:00Z',
                        'repository_url' => 'https://api.github.com/repos/acme/demo',
                        'user' => ['login' => 'devuser'],
                    ],
                    [
                        'number' => 11,
                        'title' => 'Behind only',
                        'updated_at' => '2026-01-02T00:00:00Z',
                        'repository_url' => 'https://api.github.com/repos/acme/demo',
                        'user' => ['login' => 'devuser'],
                    ],
                ],
            ], 200),
            'api.github.com/repos/acme/demo/pulls/10' => Http::response([
                'number' => 10,
                'title' => 'Conflict PR',
                'state' => 'open',
                'mergeable' => false,
                'mergeable_state' => 'dirty',
                'updated_at' => '2026-01-01T00:00:00Z',
                'user' => ['login' => 'devuser'],
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature'],
            ], 200),
            'api.github.com/repos/acme/demo/pulls/11' => Http::response([
                'number' => 11,
                'title' => 'Behind only',
                'state' => 'open',
                'mergeable' => false,
                'mergeable_state' => 'behind',
                'updated_at' => '2026-01-02T00:00:00Z',
                'user' => ['login' => 'devuser'],
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature-2'],
            ], 200),
        ]);

        $service = new GitHubApiService();
        $result = $service->getRecentMergeConflicts($this->encryptedToken, 'devuser', 10);

        $this->assertTrue($result['ok']);
        $this->assertCount(1, $result['data']);
        $this->assertSame(10, $result['data'][0]['number']);
        $this->assertSame('github_metadata_only', $result['data'][0]['conflict_source']);
        Http::assertSentCount(3);
    }
}
