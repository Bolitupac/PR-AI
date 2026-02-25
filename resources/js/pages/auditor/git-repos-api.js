export async function fetchGitRepos(reposUrl) {
    const res = await fetch(reposUrl, {
        headers: { Accept: 'application/json' },
    });

    if (!res.ok) {
        throw new Error('Failed to load repos');
    }

    const data = await res.json();
    return Array.isArray(data.repos) ? data.repos : [];
}
