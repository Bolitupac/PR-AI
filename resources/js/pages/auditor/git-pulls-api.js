export async function fetchGitPullRequests(pullsUrl, repoFullName) {
    // Pulls for the selected repo.
    const url = new URL(pullsUrl, window.location.origin);
    url.searchParams.set('repo', repoFullName);

    const res = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
    });

    if (!res.ok) {
        throw new Error('Failed to load pull requests');
    }

    const data = await res.json();
    return Array.isArray(data.pulls) ? data.pulls : [];
}
