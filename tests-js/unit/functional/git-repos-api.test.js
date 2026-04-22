import test from 'node:test';
import assert from 'node:assert/strict';

import { fetchGitRepos } from '../../../resources/js/pages/auditor/git-repos-api.js';

// Verifies that the repo API helper returns the normalized repository list on success.
test('fetchGitRepos returns repositories from the API payload', async () => {
    const calls = [];
    global.fetch = async (url, options) => {
        calls.push({ url, options });
        return {
            ok: true,
            async json() {
                return {
                    repos: [
                        { full_name: 'acme/platform' },
                        { full_name: 'acme/mobile' },
                    ],
                };
            },
        };
    };

    const repos = await fetchGitRepos('/api/github/repos');

    assert.deepEqual(repos, [
        { full_name: 'acme/platform' },
        { full_name: 'acme/mobile' },
    ]);
    assert.equal(calls[0].url, '/api/github/repos');
    assert.equal(calls[0].options.credentials, 'same-origin');
});

// Verifies that the repo API helper exposes auth and reconnect details when GitHub access fails.
test('fetchGitRepos throws a rich auth error payload when the API request fails', async () => {
    global.fetch = async () => ({
        ok: false,
        status: 401,
        async json() {
            return {
                message: 'Please reconnect GitHub.',
                connect_url: '/auth/github',
                auth_required: true,
            };
        },
    });

    await assert.rejects(
        () => fetchGitRepos('/api/github/repos'),
        (error) => {
            assert.equal(error.message, 'Please reconnect GitHub.');
            assert.equal(error.status, 401);
            assert.equal(error.connectUrl, '/auth/github');
            assert.equal(error.authRequired, true);
            return true;
        }
    );
});
