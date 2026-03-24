export async function fetchGitRepos(reposUrl) {
    // Repo list for the modal selector.
    const res = await fetch(reposUrl, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!res.ok) {
        const error = new Error(res.status === 401 ? 'Please sign in with GitHub again.' : 'Failed to load repos');
        error.status = res.status;
        throw error;
    }

    const data = await res.json();
    return Array.isArray(data.repos) ? data.repos : [];
}
