import { appendRepoParams } from '../../shared/vcs-repo-query.js';

export async function fetchGitPullDiff(pullDiffUrl, repoFullName, pullNumber) {
    const url = new URL(pullDiffUrl, window.location.origin);
    appendRepoParams(url, repoFullName);
    url.searchParams.set('pr_number', String(pullNumber));

    const res = await fetch(url.toString(), {
        headers: { Accept: 'text/plain' },
        credentials: 'same-origin',
    });

    if (!res.ok) {
        let message = res.status === 401 ? 'Please sign in with GitHub again.' : 'Failed to load diff';
        let connectUrl = '';
        let authRequired = false;
        const contentType = res.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            const data = await res.json().catch(() => ({}));
            message = data?.message || message;
            connectUrl = data?.connect_url || '';
            authRequired = Boolean(data?.auth_required);
        }
        const error = new Error(message);
        error.status = res.status;
        error.connectUrl = connectUrl;
        error.authRequired = authRequired;
        throw error;
    }

    return res.text();
}
