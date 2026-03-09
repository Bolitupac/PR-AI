import { initThemeToggle } from '../auditor/theme-toggle';
import { initSidebar } from '../auditor/sidebar';
import * as API from './api';
import * as Renderers from './renderers';

const PAGE_SIZE = 10;

export async function initImportsPage() {
    const page = document.getElementById('imports-page');
    if (!page) return;

    initSidebar();
    initThemeToggle();

    const repoContainer = document.getElementById('repo-list-container');
    const loadMoreWrap = document.getElementById('load-more-wrap');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const countLabel = document.getElementById('repo-count-label');
    if (!repoContainer) return;

    // All fetched repos stored here; we reveal PAGE_SIZE at a time
    let allRepos = [];
    let shownCount = 0;
    const repoElements = []; // { repo, element }

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
                        branches.forEach(branch => branchList.appendChild(Renderers.createBranchItem(branch, pulls)));
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
    };

    loadMoreBtn?.addEventListener('click', renderNextPage);

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

        // Background loader for metadata (Branch/PR counts)
        // Sequential with small delay to keep below GitHub rate limit
        (async () => {
            for (const { repo, element } of repoElements) {
                const details = element.querySelector('.imports-repo-details');
                if (details?.dataset.loaded === 'true') continue;
                try {
                    await new Promise(r => setTimeout(r, 300));
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
                } catch (err) {
                    console.warn(`Background metadata failed for ${repo.full_name}`, err);
                }
            }
        })();

    } catch (error) {
        console.error('Error initializing imports:', error);
        repoContainer.innerHTML = '<li style="padding: 20px; text-align: center; color: #ef4444;">Failed to load repositories. Please try again.</li>';
    }
}




