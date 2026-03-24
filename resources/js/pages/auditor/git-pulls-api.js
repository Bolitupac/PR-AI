export async function fetchGitPullRequests(pullsUrl, repoFullName) {
    // Pulls for the selected repo.
    const url = new URL(pullsUrl, window.location.origin);
    url.searchParams.set('repo', repoFullName);

    const res = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!res.ok) {
        const error = new Error(res.status === 401 ? 'Please sign in with GitHub again.' : 'Failed to load pull requests');
        error.status = res.status;
        throw error;
    }

    const data = await res.json();
    return Array.isArray(data.pulls) ? data.pulls : [];
}
