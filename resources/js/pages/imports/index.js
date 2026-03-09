import { initThemeToggle } from '../auditor/theme-toggle';
import { initSidebar } from '../auditor/sidebar';
import * as API from './api';
import * as Renderers from './renderers';

export async function initImportsPage() {
    const page = document.getElementById('imports-page');
    if (!page) return;

    initSidebar();
    initThemeToggle();

    const repoContainer = document.getElementById('repo-list-container');
    if (!repoContainer) return;

    try {
        const repos = await API.fetchRepos();
        repoContainer.innerHTML = ''; // Clear skeletons

        if (repos.length === 0) {
            repoContainer.innerHTML = '<li style="padding: 20px; text-align: center; color: var(--text-soft);">No repositories found.</li>';
            return;
        }

        const repoElements = [];

        repos.forEach(repo => {
            const repoItem = Renderers.createRepoItem(repo);
            repoContainer.appendChild(repoItem);
            repoElements.push({ repo, element: repoItem });

            // Lazy load branches/PRs when expanded
            const details = repoItem.querySelector('.imports-repo-details');
            details.addEventListener('toggle', async (e) => {
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

                        // Update metadata in the summary if it was -- or placeholder
                        const branchPlaceholder = details.querySelector('.branch-count-placeholder');
                        const pullPlaceholder = details.querySelector('.pull-count-placeholder');
                        const issuePlaceholder = details.querySelector('.issue-count-placeholder');

                        if (branchPlaceholder) branchPlaceholder.textContent = branches.length;
                        if (pullPlaceholder) pullPlaceholder.textContent = pulls.length;
                        if (issuePlaceholder) {
                            // open_issues_count from GitHub includes PRs, so we subtract PR count for "pure" issues
                            const pureIssues = Math.max(0, repo.open_issues_count - pulls.length);
                            issuePlaceholder.textContent = pureIssues;
                        }

                        if (branches.length === 0) {
                            branchList.innerHTML = '<li style="padding: 10px 20px; color: var(--text-soft); font-size: 13px;">No branches found.</li>';
                        } else {
                            branches.forEach(branch => {
                                branchList.appendChild(Renderers.createBranchItem(branch, pulls));
                            });
                        }

                        details.dataset.loaded = 'true';
                    } catch (err) {
                        console.error('Error loading repo content:', err);
                        loader.innerHTML = '<div style="color: #ef4444;">Failed to load data.</div>';
                    }
                }
            });
        });

        // Background loader for metadata (Branch/PR counts)
        // We process them sequentially with a small delay to be polite to the API
        (async () => {
            for (const { repo, element } of repoElements) {
                const details = element.querySelector('.imports-repo-details');
                if (details.dataset.loaded === 'true') continue; // Skip if already loaded via expansion

                try {
                    // Small delay between requests
                    await new Promise(r => setTimeout(r, 300));

                    const metadata = await API.fetchRepoMetadata(repo.full_name);

                    const branchPlaceholder = element.querySelector('.branch-count-placeholder');
                    const pullPlaceholder = element.querySelector('.pull-count-placeholder');
                    const issuePlaceholder = element.querySelector('.issue-count-placeholder');

                    if (branchPlaceholder) branchPlaceholder.textContent = metadata.branch_count ?? '--';
                    if (pullPlaceholder) pullPlaceholder.textContent = metadata.pull_count ?? '--';
                    if (issuePlaceholder) {
                        if (metadata.pull_count === null) {
                            issuePlaceholder.textContent = '--';
                        } else {
                            const pureIssues = Math.max(0, repo.open_issues_count - metadata.pull_count);
                            issuePlaceholder.textContent = pureIssues;
                        }
                    }
                } catch (err) {
                    console.warn(`Failed to background load metadata for ${repo.full_name}`, err);
                }
            }
        })();

    } catch (error) {
        console.error('Error initializing imports:', error);
        repoContainer.innerHTML = '<li style="padding: 20px; text-align: center; color: #ef4444;">Failed to load repositories. Please try again.</li>';
    }
}


