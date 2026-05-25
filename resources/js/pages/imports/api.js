import { appendRepoParams, buildVcsUrl } from '../../shared/vcs-repo-query.js';

async function fetchJson(url, fallbackMessage) {
    const response = await fetch(url, { headers: { Accept: 'application/json' } });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        const error = new Error(data?.message || fallbackMessage);
        error.status = response.status;
        error.connectUrl = data?.connect_url || '';
        error.authRequired = Boolean(data?.auth_required);
        throw error;
    }
    return data;
}

export async function fetchRepos(apiBase, provider) {
    const data = await fetchJson(buildVcsUrl(apiBase, provider, 'repos'), 'Failed to fetch repositories');
    return data.repos || [];
}

export async function fetchBranches(apiBase, provider, repo) {
    const url = new URL(buildVcsUrl(apiBase, provider, 'branches'), window.location.origin);
    appendRepoParams(url, repo);
    const data = await fetchJson(url.toString(), 'Failed to fetch branches');
    return data.branches || data.data || [];
}

export async function fetchRepoMetadata(apiBase, provider, repo) {
    const url = new URL(buildVcsUrl(apiBase, provider, 'metadata'), window.location.origin);
    appendRepoParams(url, repo);
    const result = await fetchJson(url.toString(), 'Failed to fetch metadata').catch(() => ({ ok: false }));
    if (!result.ok) return { branch_count: null, pull_count: null };
    return result.data || { branch_count: null, pull_count: null };
}

export async function fetchPullRequests(apiBase, provider, repo) {
    const url = new URL(buildVcsUrl(apiBase, provider, 'pulls'), window.location.origin);
    appendRepoParams(url, repo);
    const data = await fetchJson(url.toString(), 'Failed to fetch pull requests');
    return data.pulls || data.data || [];
}

export async function fetchRecentPullRequests(apiBase, provider) {
    const data = await fetchJson(buildVcsUrl(apiBase, provider, 'recent-pulls'), 'Failed to fetch recent pull requests');
    return data.pulls || data.data || [];
}

export async function fetchRecentCommits(apiBase, provider) {
    const data = await fetchJson(buildVcsUrl(apiBase, provider, 'recent-commits'), 'Failed to fetch recent commits');
    return data.commits || data.data || [];
}

export async function fetchRecentMergeConflicts(apiBase, provider) {
    const data = await fetchJson(buildVcsUrl(apiBase, provider, 'recent-merge-conflicts'), 'Failed to fetch merge conflicts');
    return data.conflicts || data.data || [];
}

export async function fetchMergeConflicts(apiBase, provider, repo, prNumber) {
    const url = new URL(buildVcsUrl(apiBase, provider, 'merge-conflicts'), window.location.origin);
    appendRepoParams(url, repo);
    url.searchParams.set('pr_number', String(prNumber));
    const data = await fetchJson(url.toString(), 'Failed to fetch merge conflict details');
    return data.data || data;
}

export async function fetchPullDiff(apiBase, provider, repo, prNumber) {
    const url = new URL(buildVcsUrl(apiBase, provider, 'pull-diff'), window.location.origin);
    appendRepoParams(url, repo);
    url.searchParams.set('pr_number', String(prNumber));
    const response = await fetch(url.toString());
    if (!response.ok) throw new Error('Failed to fetch pull request diff');
    return await response.text();
}

export async function fetchBranchDiff(apiBase, provider, repo, base, head) {
    const url = new URL(buildVcsUrl(apiBase, provider, 'branch-diff'), window.location.origin);
    appendRepoParams(url, repo);
    url.searchParams.set('base', String(base));
    url.searchParams.set('head', String(head));
    const response = await fetch(url.toString());
    if (!response.ok) throw new Error('Failed to fetch branch diff');
    return await response.text();
}

export async function fetchCommitDiff(apiBase, provider, repo, commitHash) {
    const url = new URL(buildVcsUrl(apiBase, provider, 'commit-diff'), window.location.origin);
    appendRepoParams(url, repo);
    url.searchParams.set('commit', String(commitHash));
    const response = await fetch(url.toString());
    if (!response.ok) throw new Error('Failed to fetch commit diff');
    return await response.text();
}
