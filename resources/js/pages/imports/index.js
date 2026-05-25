import { initThemeToggle } from '../auditor/theme-toggle';
import { initSidebar } from '../auditor/sidebar';
import { initSettingsModal } from '../auditor/settings-modal';
import { initSettingsAiKey } from '../auditor/settings-ai-key';
import { initSettingsAiPreferences } from '../auditor/settings-ai-preferences';
import * as API from './api';
import * as Renderers from './renderers';
import { buildBranchAuditPayload, buildCommitAuditPayload, buildPullRequestAuditPayload, startAuditSession } from './audit-session';
import { initRecentPullRequestsPanel } from './recent-pulls';
import { initRecentCommitsPanel } from './recent-commits';

const PAGE_SIZE = 20;
const METADATA_START_DELAY_MS = 1200;
const METADATA_ITEM_DELAY_MS = 250;

export async function initImportsPage() {
    const page = document.getElementById('imports-page');
    if (!page) return;

    const apiBase = page.dataset.vcsApiBase || '/api/vcs';
    const providerConfig = JSON.parse(page.dataset.vcsProviders || '{}');
    const providerSelect = document.getElementById('imports-provider-select');
    let currentProvider = providerSelect?.value || page.dataset.defaultProvider || 'github';

    initSidebar();
    initThemeToggle();
    initSettingsModal();
    initSettingsAiKey();
    initSettingsAiPreferences();

    const repoContainer = document.getElementById('repo-list-container');
    const loadMoreWrap = document.getElementById('load-more-wrap');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const countLabel = document.getElementById('repo-count-label');
    const importStatus = document.getElementById('imports-import-status');
    const commitList = document.querySelector('.imports-commit-list');
    if (!repoContainer) return;

    let allRepos = [];
    let shownCount = 0;
    let repoElements = [];
    let metadataStarted = false;
    let metadataProcessing = false;
    const metadataQueue = [];

    const isConnectedProvider = () => Boolean(providerConfig?.[currentProvider]?.connected);
    const providerLabel = () => providerConfig?.[currentProvider]?.label || currentProvider;
    const providerConnectTarget = () => providerConfig?.[currentProvider]?.connect_url || '#settings-vcs';

    const getProviderSvg = (provider) => {
        switch(provider) {
            case 'gitlab':
                return `<path d="M23.955 10.37L21.316 2.246a.82.82 0 0 0-1.564 0l-2.07 6.386H6.315L4.246 2.246a.82.82 0 0 0-1.564 0L.044 10.37a.822.822 0 0 0 .296.907L12 20.59l11.66-9.311a.822.822 0 0 0 .295-.91z" fill="#FC6D26"/><path d="M12 20.59L.044 10.37a.822.822 0 0 1-.296-.906L2.68 1.34a.82.82 0 0 1 1.564 0l2.07 6.386H12v12.863z" fill="#E24329"/><path d="M12 20.59V8.632H6.315L12 20.59z" fill="#FCA326"/><path d="M12 20.59l11.956-10.22a.822.822 0 0 0 .295-.91L21.32 1.34a.82.82 0 0 0-1.564 0l-2.07 6.386H12v12.863z" fill="#E24329"/><path d="M12 20.59V8.632h5.685L12 20.59z" fill="#FCA326"/>`;
            case 'bitbucket':
                return `<path d="M1.082 3.6A1.666 1.666 0 0 1 2.748 2h18.52a1.666 1.666 0 0 1 1.644 1.889l-2.613 15.013A1.666 1.666 0 0 1 18.656 20H5.319a1.666 1.666 0 0 1-1.644-1.39l-2.593-15.01zm13.195 10.23L15.65 8H8.38l1.373 5.83h4.524z" fill="#0052CC"/>`;
            case 'azure':
                return `<rect x="2" y="2" width="9" height="9" fill="#00A4EF"/><rect x="2" y="12" width="9" height="9" fill="#00A4EF"/><rect x="12" y="2" width="9" height="9" fill="#00A4EF"/><rect x="12" y="12" width="9" height="9" fill="#00A4EF"/>`;
            case 'github':
            default:
                return `<path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.161 22 16.416 22 12c0-5.523-4.477-10-10-10z" fill="currentColor" stroke="none"/>`;
        }
    };

    const setImportStatus = (text, isLoading = false) => {
        if (!importStatus) return;
        importStatus.textContent = text;
        importStatus.classList.toggle('is-loading', Boolean(isLoading));
    };

    const renderProviderPrompt = (container, title, message) => {
        if (!container) return;

        const actionLabel = `Connect ${providerLabel()}`;
        const providerSvg = getProviderSvg(currentProvider);
        container.innerHTML = `
            <li style="padding: 40px 20px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 16px;">
                <div style="background: var(--brand-soft); color: var(--brand); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2">
                        ${providerSvg}
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0 0 8px; color: var(--text-main); font-size: 18px;">${title}</h3>
                    <p style="margin: 0; color: var(--text-soft); font-size: 14px; max-width: 320px;">${message}</p>
                </div>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <a href="${providerConnectTarget()}" class="imports-login-btn">${actionLabel}</a>
                    <button type="button" class="change-vcs-btn" style="background: none; border: none; color: var(--text-soft); text-decoration: underline; cursor: pointer; font-size: 13px; padding: 4px;">Change VCS</button>
                </div>
            </li>
        `;

        const changeVcsBtn = container.querySelector('.change-vcs-btn');
        if (changeVcsBtn) {
            changeVcsBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (providerSelect) {
                    providerSelect.focus();
                    if (typeof providerSelect.showPicker === 'function') {
                        try { providerSelect.showPicker(); } catch(err) {}
                    }
                }
            });
        }
    };

    const renderDisconnectedText = (container, message = 'VCS not connected.') => {
        if (!container) return;
        container.innerHTML = `<li class="imports-history-item" style="color: var(--text-soft); font-size: 12px;">${message}</li>`;
    };

    const updateLoadMoreBar = () => {
        const remaining = allRepos.length - shownCount;
        if (remaining > 0) {
            loadMoreWrap.style.display = '';
            loadMoreBtn.textContent = `Load ${Math.min(remaining, PAGE_SIZE)} more repositories`;
            countLabel.textContent = `Showing ${shownCount} of ${allRepos.length}`;
        } else {
            loadMoreWrap.style.display = 'none';
        }
    };

    const enqueueMetadata = (items) => {
        items.forEach(({ repo, element }) => {
            if (!repo?.full_name || !element) return;
            if (element.dataset.metadataQueued === 'true') return;
            element.dataset.metadataQueued = 'true';
            metadataQueue.push({ repo, element, provider: currentProvider });
        });

        if (metadataStarted) {
            processMetadataQueue();
        }
    };

    const processMetadataQueue = async () => {
        if (metadataProcessing) return;
        metadataProcessing = true;

        while (metadataQueue.length > 0) {
            if (document.visibilityState === 'hidden') break;

            const { repo, element, provider } = metadataQueue.shift();
            const details = element.querySelector('.imports-repo-details');
            if (!details || details.open || details.dataset.loaded === 'true' || element.dataset.metadataLoaded === 'true') {
                continue;
            }

            try {
                const metadata = await API.fetchRepoMetadata(apiBase, provider, repo);
                const branchPh = element.querySelector('.branch-count-placeholder');
                const pullPh = element.querySelector('.pull-count-placeholder');
                const issuePh = element.querySelector('.issue-count-placeholder');
                if (branchPh) branchPh.textContent = metadata.branch_count ?? '--';
                if (pullPh) pullPh.textContent = metadata.pull_count ?? '--';
                if (issuePh) {
                    issuePh.textContent = metadata.pull_count === null ? '--' : Math.max(0, (repo.open_issues_count || 0) - metadata.pull_count);
                }
                element.dataset.metadataLoaded = 'true';
            } catch (err) {
                console.warn(`Metadata failed for ${repo.full_name}`, err);
            }

            await new Promise((resolve) => setTimeout(resolve, METADATA_ITEM_DELAY_MS));
        }

        metadataProcessing = false;
    };

    const startMetadataAfterInitialPaint = () => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                setTimeout(() => {
                    metadataStarted = true;
                    enqueueMetadata(repoElements.slice(0, shownCount));
                }, METADATA_START_DELAY_MS);
            });
        });
    };

    const attachToggleListener = (repoItem, repo) => {
        const details = repoItem.querySelector('.imports-repo-details');
        details.addEventListener('toggle', async () => {
            if (details.open && !details.dataset.loaded) {
                const branchList = details.querySelector('.imports-branches-list');
                const loader = details.querySelector('.repo-loading-indicator');
                try {
                    const [branches, pulls] = await Promise.all([
                        API.fetchBranches(apiBase, currentProvider, repo),
                        API.fetchPullRequests(apiBase, currentProvider, repo),
                    ]);
                    loader.remove();
                    branchList.innerHTML = '';

                    const branchPlaceholder = details.querySelector('.branch-count-placeholder');
                    const pullPlaceholder = details.querySelector('.pull-count-placeholder');
                    const issuePlaceholder = details.querySelector('.issue-count-placeholder');

                    if (branchPlaceholder) branchPlaceholder.textContent = branches.length;
                    if (pullPlaceholder) pullPlaceholder.textContent = pulls.length;
                    if (issuePlaceholder) {
                        issuePlaceholder.textContent = Math.max(0, (repo.open_issues_count || 0) - pulls.length);
                    }

                    if (branches.length === 0) {
                        branchList.innerHTML = '<li style="padding: 10px 20px; color: var(--text-soft); font-size: 13px;">No branches found.</li>';
                    } else {
                        branches.forEach((branch) => branchList.appendChild(Renderers.createBranchItem(branch, pulls, repo.default_branch)));
                    }
                    details.dataset.loaded = 'true';
                } catch (err) {
                    console.error('Error loading repo content:', err);
                    loader.innerHTML = '<div style="color: #ef4444;">Failed to load data.</div>';
                }
            }
        });
    };

    const renderNextPage = () => {
        const startIndex = shownCount;
        const end = Math.min(shownCount + PAGE_SIZE, allRepos.length);
        for (let i = shownCount; i < end; i++) {
            const { repo, element } = repoElements[i];
            if (loadMoreWrap && loadMoreWrap.parentNode === repoContainer) {
                repoContainer.insertBefore(element, loadMoreWrap);
            } else {
                repoContainer.appendChild(element);
            }
            attachToggleListener(element, repo);
        }
        shownCount = end;
        updateLoadMoreBar();
        enqueueMetadata(repoElements.slice(startIndex, end));
    };

    const buildRepoPayload = (repo, extras = {}) => ({
        provider: currentProvider,
        repo: repo?.full_name || repo,
        repoId: repo?.provider_repo_id || extras.repoId || null,
        project: repo?.provider_project || extras.project || null,
        organization: repo?.provider_organization || extras.organization || null,
        workspace: repo?.provider_workspace || extras.workspace || null,
        repoSlug: repo?.provider_repo_slug || extras.repoSlug || null,
    });

    const loadProviderRepos = async () => {
        allRepos = [];
        shownCount = 0;
        repoElements = [];
        metadataStarted = false;
        metadataProcessing = false;
        metadataQueue.length = 0;
        repoContainer.innerHTML = '';
        if (loadMoreWrap) repoContainer.appendChild(loadMoreWrap);
        setImportStatus('', false);

        if (!isConnectedProvider()) {
            renderDisconnectedText(document.getElementById('recent-pull-requests-list'), `${providerLabel()} not connected.`);
            renderProviderPrompt(repoContainer, `${providerLabel()} not connected`, `Connect ${providerLabel()} to import repositories and pull requests.`);
            return;
        }

        initRecentPullRequestsPanel(apiBase, currentProvider, setImportStatus);
        initRecentCommitsPanel(apiBase, currentProvider, setImportStatus);

        try {
            const repos = await API.fetchRepos(apiBase, currentProvider);
            repoContainer.innerHTML = '';
            if (loadMoreWrap) {
                repoContainer.appendChild(loadMoreWrap);
            }

            if (repos.length === 0) {
                repoContainer.innerHTML = '<li style="padding: 20px; text-align: center; color: var(--text-soft);">No repositories found.</li>';
                return;
            }

            repos.forEach((repo) => {
                const element = Renderers.createRepoItem(repo);
                allRepos.push(repo);
                repoElements.push({ repo, element });
            });

            renderNextPage();
            startMetadataAfterInitialPaint();
        } catch (error) {
            console.error('Error initializing imports:', error);
            if (error.status === 401) {
                renderProviderPrompt(repoContainer, `${providerLabel()} not connected`, `Connect ${providerLabel()} to import repositories and pull requests.`);
            } else {
                repoContainer.innerHTML = '<li style="padding: 20px; text-align: center; color: #ef4444;">Failed to load repositories. Please try again.</li>';
            }
        }
    };

    loadMoreBtn?.addEventListener('click', renderNextPage);



    repoContainer.addEventListener('click', async (event) => {
        const btn = event.target.closest('.imports-action-btn');
        if (!btn) return;

        event.preventDefault();
        event.stopPropagation();

        const action = btn.dataset.action;
        const details = btn.closest('.imports-repo-details');
        const repoName = details?.dataset.repo;
        const repo = allRepos.find((item) => item.full_name === repoName);
        const defaultBranch = details?.dataset.defaultBranch;
        if (!repo) return;

        btn.classList.add('is-loading');
        setImportStatus('Preparing audit in Auditor...', true);

        try {
            let payload = null;
            if (action === 'import-branch') {
                const head = btn.dataset.branch;
                if (head === defaultBranch) {
                    alert(`Cannot import the default branch (${defaultBranch}). Choose a different branch to compare against ${defaultBranch}.`);
                    btn.classList.remove('is-loading');
                    setImportStatus('', false);
                    return;
                }
                payload = buildBranchAuditPayload({
                    ...buildRepoPayload(repo),
                    branchName: head,
                    baseBranch: defaultBranch,
                });
            } else if (action === 'import-pr') {
                payload = buildPullRequestAuditPayload({
                    ...buildRepoPayload(repo),
                    prNumber: btn.dataset.pr,
                    title: btn.dataset.title,
                });
            }

            if (!payload) {
                throw new Error('Unsupported import action.');
            }

            startAuditSession(payload);
        } catch (err) {
            console.error('Import failed:', err);
            alert(`Import failed: ${err.message || 'Check console for details'}`);
            btn.classList.remove('is-loading');
            setImportStatus('', false);
        }
    });

    providerSelect?.addEventListener('change', () => {
        currentProvider = providerSelect.value || page.dataset.defaultProvider || 'github';
        loadProviderRepos();
    });

    loadProviderRepos();
}
