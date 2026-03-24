export async function fetchGitPullComments(repoFullName, pullNumber) {
    const url = new URL('/api/github/pull-comments', window.location.origin);
    url.searchParams.set('repo', repoFullName);
    url.searchParams.set('pr_number', String(pullNumber));

    const response = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        const error = new Error(data?.message || 'Failed to load pull request comments');
        error.status = response.status;
        error.connectUrl = data?.connect_url || '';
        throw error;
    }

    const data = await response.json();
    return Array.isArray(data.comments) ? data.comments : [];
}
