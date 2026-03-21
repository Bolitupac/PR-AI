export async function fetchGitPullComments(repoFullName, pullNumber) {
    const url = new URL('/api/github/pull-comments', window.location.origin);
    url.searchParams.set('repo', repoFullName);
    url.searchParams.set('pr_number', String(pullNumber));

    const response = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error('Failed to load pull request comments');
    }

    const data = await response.json();
    return Array.isArray(data.comments) ? data.comments : [];
}
