import test from 'node:test';
import assert from 'node:assert/strict';

import { fetchGitPullRequests } from '../../../resources/js/pages/auditor/git-pulls-api.js';

// Verifies that the pull request API helper adds the repo query parameter and returns pull requests.
test('fetchGitPullRequests requests pulls for the selected repo', async () => {
    const calls = [];
    global.window = { location: { origin: 'https://example.test' } };
    global.fetch = async (url, options) => {
        calls.push({ url: String(url), options });
        return {
            ok: true,
            async json() {
                return {
                    pulls: [
                        { number: 17, title: 'Tidy import UI' },
                    ],
                };
            },
        };
    };

    const pulls = await fetchGitPullRequests('/api/github/pulls', 'acme/platform');

    assert.deepEqual(pulls, [{ number: 17, title: 'Tidy import UI' }]);
    assert.match(calls[0].url, /repo=acme%2Fplatform/);
    assert.equal(calls[0].options.credentials, 'same-origin');
});

// Verifies that the pull request API helper throws the backend message when loading fails.
test('fetchGitPullRequests throws a descriptive error payload when loading fails', async () => {
    global.window = { location: { origin: 'https://example.test' } };
    global.fetch = async () => ({
        ok: false,
        status: 500,
        async json() {
            return {
                message: 'GitHub pull request sync failed.',
                connect_url: '/auth/github',
                auth_required: false,
            };
        },
    });

    await assert.rejects(
        () => fetchGitPullRequests('/api/github/pulls', 'acme/platform'),
        (error) => {
            assert.equal(error.message, 'GitHub pull request sync failed.');
            assert.equal(error.status, 500);
            assert.equal(error.connectUrl, '/auth/github');
            assert.equal(error.authRequired, false);
            return true;
        }
    );
});
