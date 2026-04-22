import test from 'node:test';
import assert from 'node:assert/strict';
import { performance } from 'node:perf_hooks';

import { fetchGitRepos } from '../../../resources/js/pages/auditor/git-repos-api.js';
import { fetchGitPullRequests } from '../../../resources/js/pages/auditor/git-pulls-api.js';

function wait(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

// Measures repo list loading latency through the actual helper used by the import experience.
test('repository loading stays within the expected timing window under mocked latency', async () => {
    global.fetch = async () => {
        await wait(25);
        return {
            ok: true,
            async json() {
                return { repos: [{ full_name: 'acme/platform' }] };
            },
        };
    };

    const startedAt = performance.now();
    const repos = await fetchGitRepos('/api/github/repos');
    const elapsedMs = performance.now() - startedAt;

    assert.equal(repos.length, 1);
    assert.ok(elapsedMs >= 20, `Expected repo loading to reflect mocked latency, got ${elapsedMs.toFixed(2)}ms`);
    assert.ok(elapsedMs < 200, `Expected repo loading to stay under 200ms, got ${elapsedMs.toFixed(2)}ms`);
});

// Measures pull request loading latency through the actual helper used after a repository is selected.
test('pull request loading stays within the expected timing window under mocked latency', async () => {
    global.window = { location: { origin: 'https://example.test' } };
    global.fetch = async () => {
        await wait(25);
        return {
            ok: true,
            async json() {
                return { pulls: [{ number: 9, title: 'Improve imports' }] };
            },
        };
    };

    const startedAt = performance.now();
    const pulls = await fetchGitPullRequests('/api/github/pulls', 'acme/platform');
    const elapsedMs = performance.now() - startedAt;

    assert.equal(pulls.length, 1);
    assert.ok(elapsedMs >= 20, `Expected pull request loading to reflect mocked latency, got ${elapsedMs.toFixed(2)}ms`);
    assert.ok(elapsedMs < 200, `Expected pull request loading to stay under 200ms, got ${elapsedMs.toFixed(2)}ms`);
});
