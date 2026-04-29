export function buildVcsUrl(apiBase, provider, resource) {
    const base = String(apiBase || '').replace(/\/$/, '');
    return `${base}/${encodeURIComponent(provider)}/${resource}`;
}

export function appendRepoParams(url, repoInput) {
    const repo = normalizeRepoInput(repoInput);
    if (repo.repo) url.searchParams.set('repo', repo.repo);
    if (repo.repo_id) url.searchParams.set('repo_id', repo.repo_id);
    if (repo.project) url.searchParams.set('project', repo.project);
    if (repo.workspace) url.searchParams.set('workspace', repo.workspace);
    if (repo.organization) url.searchParams.set('organization', repo.organization);
    if (repo.repo_slug) url.searchParams.set('repo_slug', repo.repo_slug);
    return url;
}

export function normalizeRepoInput(repoInput) {
    if (typeof repoInput === 'string') {
        return { repo: repoInput };
    }

    return {
        repo: String(repoInput?.repo || repoInput?.full_name || ''),
        repo_id: normalizeOptional(repoInput?.repo_id || repoInput?.provider_repo_id),
        project: normalizeOptional(repoInput?.project || repoInput?.provider_project),
        workspace: normalizeOptional(repoInput?.workspace || repoInput?.provider_workspace),
        organization: normalizeOptional(repoInput?.organization || repoInput?.provider_organization),
        repo_slug: normalizeOptional(repoInput?.repo_slug || repoInput?.provider_repo_slug),
    };
}

function normalizeOptional(value) {
    const text = String(value || '').trim();
    return text ? text : '';
}
