import { appendRepoParams } from '../../shared/vcs-repo-query.js';

export async function fetchGitPullRequests(pullsUrl, repoFullName) {
    // Pulls for the selected repo.
    const url = new URL(pullsUrl, window.location.origin);
    appendRepoParams(url, repoFullName);

    const res = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
        const error = new Error(data?.message || (res.status === 401 ? 'Please sign in with GitHub again.' : 'Failed to load pull requests'));
        error.status = res.status;
        error.connectUrl = data?.connect_url || '';
        error.authRequired = Boolean(data?.auth_required);
        throw error;
    }

    return Array.isArray(data.pulls) ? data.pulls : [];
}
