/**
 * API Service for GitHub data
 */
export async function fetchRepos() {
    const response = await fetch('/api/github/repos');
    if (!response.ok) {
        const err = new Error('Failed to fetch repositories');
        err.status = response.status;
        throw err;
    }
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

export async function fetchRecentPullRequests() {
    const response = await fetch('/api/github/recent-pulls');
    if (!response.ok) {
        const err = new Error('Failed to fetch recent pull requests');
        err.status = response.status;
        throw err;
    }
    const data = await response.json();
    return data.pulls || data.data || [];
}

export async function fetchPullDiff(repoFullName, prNumber) {
    const response = await fetch(`/api/github/pull-diff?repo=${encodeURIComponent(repoFullName)}&pr_number=${prNumber}`);
    if (!response.ok) throw new Error('Failed to fetch pull request diff');
    return await response.text();
}

export async function fetchBranchDiff(repoFullName, base, head) {
    const response = await fetch(`/api/github/branch-diff?repo=${encodeURIComponent(repoFullName)}&base=${encodeURIComponent(base)}&head=${encodeURIComponent(head)}`);
    if (!response.ok) throw new Error('Failed to fetch branch diff');
    return await response.text();
}

export async function fetchCommitDiff(commitHash) {
    const response = await fetch(`/api/git/commit-diff?commit=${encodeURIComponent(commitHash)}`);
    if (!response.ok) throw new Error('Failed to fetch commit diff');
    return await response.text();
}
