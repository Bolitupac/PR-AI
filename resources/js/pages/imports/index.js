import { initThemeToggle } from '../auditor/theme-toggle';
import { initSidebar } from '../auditor/sidebar';
import * as API from './api';
import * as Renderers from './renderers';
import { buildBranchAuditPayload, buildCommitAuditPayload, buildPullRequestAuditPayload, startAuditSession } from './audit-session';
import { initRecentPullRequestsPanel } from './recent-pulls';

const PAGE_SIZE = 20;
const METADATA_START_DELAY_MS = 1200;
const METADATA_ITEM_DELAY_MS = 250;

export async function initImportsPage() {
    const page = document.getElementById('imports-page');
    if (!page) return;

    initSidebar();
    initThemeToggle();

    const repoContainer = document.getElementById('repo-list-container');
    const loadMoreWrap = document.getElementById('load-more-wrap');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const countLabel = document.getElementById('repo-count-label');
    const importStatus = document.getElementById('imports-import-status');
    if (!repoContainer) return;

    // All fetched repos stored here; we reveal PAGE_SIZE at a time
    let allRepos = [];
    let shownCount = 0;
    const repoElements = []; // { repo, element }

    // ── metadata queue (deferred until after initial paint) ──────────────────
    let metadataStarted = false;
    let metadataProcessing = false;
    const metadataQueue = [];

    const enqueueMetadata = (items) => {
        items.forEach(({ repo, element }) => {
            if (!repo?.full_name || !element) return;
            if (element.dataset.metadataQueued === 'true') return;
            element.dataset.metadataQueued = 'true';
            metadataQueue.push({ repo, element });
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

            const { repo, element } = metadataQueue.shift();
            const details = element.querySelector('.imports-repo-details');
            if (!details || details.open || details.dataset.loaded === 'true') {
                continue;
            }

            if (element.dataset.metadataLoaded === 'true') {
                continue;
            }

            try {
                const metadata = await API.fetchRepoMetadata(repo.full_name);
                const branchPh = element.querySelector('.branch-count-placeholder');
                const pullPh = element.querySelector('.pull-count-placeholder');
                const issuePh = element.querySelector('.issue-count-placeholder');
                if (branchPh) branchPh.textContent = metadata.branch_count ?? '--';
                if (pullPh) pullPh.textContent = metadata.pull_count ?? '--';
                if (issuePh) {
                    issuePh.textContent = metadata.pull_count === null
                        ? '--'
                        : Math.max(0, repo.open_issues_count - metadata.pull_count);
                }
                element.dataset.metadataLoaded = 'true';
            } catch (err) {
                console.warn(`Metadata failed for ${repo.full_name}`, err);
            }

            await new Promise(r => setTimeout(r, METADATA_ITEM_DELAY_MS));
        }

        metadataProcessing = false;
    };

    const startMetadataAfterInitialPaint = () => {
        // Defer metadata calls so the page becomes interactive quickly.
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                setTimeout(() => {
                    metadataStarted = true;
                    enqueueMetadata(repoElements.slice(0, shownCount));
                }, METADATA_START_DELAY_MS);
            });
        });
    };

    // ── helpers ──────────────────────────────────────────────────────────────

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

    // Attaches toggle listener for lazy-loading branches/PRs into a repo item
    const attachToggleListener = (repoItem, repo) => {
        const details = repoItem.querySelector('.imports-repo-details');
        details.addEventListener('toggle', async () => {
            if (details.open && !details.dataset.loaded) {
                const branchList = details.querySelector('.imports-branches-list');
                const loader = details.querySelector('.repo-loading-indicator');
                try {
                    const [branches, pulls] = await Promise.all([
                        API.fetchBranches(repo.full_name),
                        API.fetchPullRequests(repo.full_name)
                    ]);
                    loader.remove();
                    branchList.innerHTML = '';

                    const branchPlaceholder = details.querySelector('.branch-count-placeholder');
                    const pullPlaceholder = details.querySelector('.pull-count-placeholder');
                    const issuePlaceholder = details.querySelector('.issue-count-placeholder');

                    if (branchPlaceholder) branchPlaceholder.textContent = branches.length;
                    if (pullPlaceholder) pullPlaceholder.textContent = pulls.length;
                    if (issuePlaceholder) {
                        issuePlaceholder.textContent = Math.max(0, repo.open_issues_count - pulls.length);
                    }

                    if (branches.length === 0) {
                        branchList.innerHTML = '<li style="padding: 10px 20px; color: var(--text-soft); font-size: 13px;">No branches found.</li>';
                    } else {
                        branches.forEach(branch => branchList.appendChild(Renderers.createBranchItem(branch, pulls, repo.default_branch)));
                    }
                    details.dataset.loaded = 'true';
                } catch (err) {
                    console.error('Error loading repo content:', err);
                    loader.innerHTML = '<div style="color: #ef4444;">Failed to load data.</div>';
                }
            }
        });
    };

    // Renders the next PAGE_SIZE repos from allRepos
    const renderNextPage = () => {
        const startIndex = shownCount;
        const end = Math.min(shownCount + PAGE_SIZE, allRepos.length);
        for (let i = shownCount; i < end; i++) {
            const { repo, element } = repoElements[i];

            // If loadMoreWrap is in the container, insert before it
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

    loadMoreBtn?.addEventListener('click', renderNextPage);

    // ── import actions ──────────────────────────────────────────────────────────
    const setImportStatus = (text, isLoading = false) => {
        if (!importStatus) return;
        importStatus.textContent = text;
        importStatus.classList.toggle('is-loading', Boolean(isLoading));
    };

    initRecentPullRequestsPanel(setImportStatus);

    const commitList = document.querySelector('.imports-commit-list');

    commitList?.addEventListener('click', async (event) => {
        const button = event.target.closest('.imports-commit-import-btn');
        if (!button) return;

        event.preventDefault();
        event.stopPropagation();

        const commitHash = button.dataset.commit;
        const title = button.dataset.title || 'Commit audit';
        const repo = button.dataset.repo || 'repo';

        if (!commitHash) return;

        button.classList.add('is-loading');
        setImportStatus('Preparing audit in Auditor...', true);

        try {
            startAuditSession(buildCommitAuditPayload({
                repo,
                commitHash,
                title,
            }));
        } catch (err) {
            console.error('Commit import failed:', err);
            alert(`Commit import failed: ${err.message || 'Check console for details'}`);
            button.classList.remove('is-loading');
            setImportStatus('', false);
        }
    });

    const handleImportClick = async (event) => {
        const btn = event.target.closest('.imports-action-btn');
        if (!btn) return;

        event.preventDefault();
        event.stopPropagation();

        const action = btn.dataset.action;
        const details = btn.closest('.imports-repo-details');
        const repo = details?.dataset.repo;
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
                    repo,
                    branchName: head,
                    baseBranch: defaultBranch,
                });
            } else if (action === 'import-pr') {
                const prNum = btn.dataset.pr;
                const prTitle = btn.dataset.title;
                payload = buildPullRequestAuditPayload({
                    repo,
                    prNumber: prNum,
                    title: prTitle,
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
    };

    repoContainer.addEventListener('click', handleImportClick);

    // ── initial fetch ─────────────────────────────────────────────────────────
    try {
        const repos = await API.fetchRepos();
        repoContainer.innerHTML = ''; // Clear skeletons

        // Re-append the load-more-wrap we just cleared (if it existed in Blade)
        if (loadMoreWrap) {
            repoContainer.appendChild(loadMoreWrap);
        }

        if (repos.length === 0) {
            repoContainer.innerHTML = '<li style="padding: 20px; text-align: center; color: var(--text-soft);">No repositories found.</li>';
            return;
        }

        // Build all elements upfront but don't append yet
        repos.forEach(repo => {
            const element = Renderers.createRepoItem(repo);
            allRepos.push(repo);
            repoElements.push({ repo, element });
        });

        // Show first page immediately
        renderNextPage();
        startMetadataAfterInitialPaint();

    } catch (error) {
        console.error('Error initializing imports:', error);
        if (error.status === 401) {
            repoContainer.innerHTML = `
                <li style="padding: 40px 20px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 16px;">
                    <div style="background: var(--brand-soft); color: var(--brand); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                         <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.161 22 16.416 22 12c0-5.523-4.477-10-10-10z" fill="currentColor" stroke="none"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="margin: 0 0 8px; color: var(--text-main); font-size: 18px;">Connect GitHub</h3>
                        <p style="margin: 0; color: var(--text-soft); font-size: 14px; max-width: 320px;">Authorize your GitHub account to import repositories and pull requests.</p>
                    </div>
                    <a href="/auth/github" class="action-btn" style="text-decoration: none; padding: 10px 24px;">Connect VSC</a>
                </li>
            `;
        } else {
            repoContainer.innerHTML = '<li style="padding: 20px; text-align: center; color: #ef4444;">Failed to load repositories. Please try again.</li>';
        }
    }
}
