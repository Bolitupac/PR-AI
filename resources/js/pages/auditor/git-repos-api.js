export async function fetchGitRepos(reposUrl) {
    // Repo list for the modal selector.
    const res = await fetch(reposUrl, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
        const error = new Error(data?.message || (res.status === 401 ? 'Please sign in with GitHub again.' : 'Failed to load repos'));
        error.status = res.status;
        error.connectUrl = data?.connect_url || '';
        error.authRequired = Boolean(data?.auth_required);
        throw error;
    }

    return Array.isArray(data.repos) ? data.repos : [];
}
