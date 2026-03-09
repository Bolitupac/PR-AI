/**
 * API Service for GitHub data
 */
export async function fetchRepos() {
    const response = await fetch('/api/github/repos');
    if (!response.ok) throw new Error('Failed to fetch repositories');
    const data = await response.json();
    return data.repos || [];
}

export async function fetchBranches(repoFullName) {
    const response = await fetch(`/api/github/branches?repo=${encodeURIComponent(repoFullName)}`);
    if (!response.ok) throw new Error('Failed to fetch branches');
    const data = await response.json();
    // Controller wraps in { branches: [...] }
    return data.branches || data.data || [];
}

export async function fetchRepoMetadata(repoFullName) {
    const response = await fetch(`/api/github/metadata?repo=${encodeURIComponent(repoFullName)}`);
    if (!response.ok) return { branch_count: null, pull_count: null };
    const result = await response.json();
    if (!result.ok) return { branch_count: null, pull_count: null };
    return result.data || { branch_count: null, pull_count: null };
}

export async function fetchPullRequests(repoFullName) {
    const response = await fetch(`/api/github/pulls?repo=${encodeURIComponent(repoFullName)}`);
    if (!response.ok) throw new Error('Failed to fetch pull requests');
    const data = await response.json();
    // Controller wraps in { pulls: [...] }
    return data.pulls || data.data || [];
}
