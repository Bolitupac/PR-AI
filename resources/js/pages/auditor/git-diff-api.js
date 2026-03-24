export async function fetchGitPullDiff(pullDiffUrl, repoFullName, pullNumber) {
    const url = new URL(pullDiffUrl, window.location.origin);
    url.searchParams.set('repo', repoFullName);
    url.searchParams.set('pr_number', String(pullNumber));

    const res = await fetch(url.toString(), {
        headers: { Accept: 'text/plain' },
        credentials: 'same-origin',
    });

    if (!res.ok) {
        const error = new Error(res.status === 401 ? 'Please sign in with GitHub again.' : 'Failed to load diff');
        error.status = res.status;
        throw error;
    }

    return res.text();
}
