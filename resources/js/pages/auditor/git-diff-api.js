export async function fetchGitPullDiff(pullDiffUrl, repoFullName, pullNumber) {
    const url = new URL(pullDiffUrl, window.location.origin);
    url.searchParams.set('repo', repoFullName);
    url.searchParams.set('pr_number', String(pullNumber));

    const res = await fetch(url.toString(), {
        headers: { Accept: 'text/plain' },
    });

    if (!res.ok) {
        throw new Error('Failed to load diff');
    }

    return res.text();
}
