import { fetchGitRepos } from './git-repos-api';
import { fetchGitPullRequests } from './git-pulls-api';

export function initGitRepoModal() {
    const repoSelectTrigger = document.getElementById('repo-select');
    const repoModal = document.getElementById('repo-modal');
    const repoSelectModal = document.getElementById('repo-import-select');
    const repoPrState = document.getElementById('repo-pr-state');
    const repoPrList = document.getElementById('repo-pr-list');

    if (!repoSelectTrigger || !repoModal || !repoSelectModal || !repoPrState || !repoPrList) return;

    const reposUrl = repoSelectTrigger.dataset.reposUrl;
    const pullsUrl = repoSelectTrigger.dataset.pullsUrl;
    const closeButtons = document.querySelectorAll('[data-close="repo-modal"]');
    let reposLoaded = false;
    let selectedPullNumber = null;

    const setSingleOption = (text) => {
        repoSelectModal.innerHTML = '';
        const opt = document.createElement('option');
        opt.textContent = text;
        opt.disabled = true;
        opt.selected = true;
        repoSelectModal.appendChild(opt);
    };

    const setPrState = (text, tone = 'info') => {
        repoPrState.textContent = text;
        repoPrState.classList.remove('is-info', 'is-loading', 'is-success', 'is-empty', 'is-error');
        repoPrState.classList.add(`is-${tone}`);
    };

    const clearPrList = () => {
        repoPrList.innerHTML = '';
    };

    // Match GitHub-style relative timestamps.
    const formatRelativeTime = (isoDate) => {
        if (!isoDate) return 'time unknown';
        const then = new Date(isoDate).getTime();
        const now = Date.now();
        const diffMs = Math.max(0, now - then);

        const minute = 60 * 1000;
        const hour = 60 * minute;
        const day = 24 * hour;

        if (diffMs < minute) return 'just now';
        if (diffMs < hour) {
            const mins = Math.floor(diffMs / minute);
            return mins === 1 ? '1 minute ago' : `${mins} minutes ago`;
        }
        if (diffMs < day) {
            const hours = Math.floor(diffMs / hour);
            return hours === 1 ? '1 hour ago' : `${hours} hours ago`;
        }
        const days = Math.floor(diffMs / day);
        return days === 1 ? '1 day ago' : `${days} days ago`;
    };

    // Keep one selected PR at a time.
    const selectPullItem = (item, pull) => {
        repoPrList.querySelectorAll('.repo-pr-item').forEach((node) => {
            node.classList.remove('is-selected');
        });
        item.classList.add('is-selected');
        selectedPullNumber = pull.number ?? null;
        repoModal.dataset.selectedPrNumber = selectedPullNumber ? String(selectedPullNumber) : '';
        setPrState(`Selected PR #${pull.number ?? ''}`, 'success');
    };

    const renderPullList = (pulls) => {
        clearPrList();
        selectedPullNumber = null;
        repoModal.dataset.selectedPrNumber = '';

        if (!pulls.length) {
            setPrState('No pull requests found for this repository.', 'empty');
            return;
        }

        setPrState(`Open pull requests (${pulls.length})`, 'success');

        pulls.forEach((pull) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'repo-pr-item';
            item.innerHTML = `
                <div class="repo-pr-title">#${pull.number ?? ''} ${pull.title ?? ''}</div>
                <div class="repo-pr-meta">@${pull.author || 'unknown'} - ${pull.state || ''} - updated ${formatRelativeTime(pull.updated_at)}</div>
            `;
            item.addEventListener('click', function () {
                selectPullItem(item, pull);
            });
            repoPrList.appendChild(item);
        });
    };

    const loadRepos = async () => {
        if (reposLoaded) return;
        if (!reposUrl) {
            setSingleOption('Repo URL is missing');
            return;
        }

        repoSelectTrigger.disabled = true;
        repoSelectTrigger.classList.add('is-loading');

        try {
            const repos = await fetchGitRepos(reposUrl);
            repoSelectModal.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select a repository';
            placeholder.disabled = true;
            placeholder.selected = true;
            repoSelectModal.appendChild(placeholder);

            if (!repos.length) {
                setSingleOption('No repositories found');
                return;
            }

            repos.forEach((repo) => {
                const opt = document.createElement('option');
                opt.value = repo.full_name || repo.name || '';
                opt.textContent = repo.full_name || repo.name || 'Unnamed repo';
                repoSelectModal.appendChild(opt);
            });
            reposLoaded = true;
        } catch (error) {
            setSingleOption('Failed to load repos');
        } finally {
            repoSelectTrigger.disabled = false;
            repoSelectTrigger.classList.remove('is-loading');
        }
    };

    const loadPullRequests = async (repoFullName) => {
        clearPrList();
        selectedPullNumber = null;
        repoModal.dataset.selectedPrNumber = '';
        setPrState('Loading pull requests...', 'loading');

        if (!pullsUrl) {
            setPrState('Pull request URL is missing.', 'error');
            return;
        }

        try {
            const pulls = await fetchGitPullRequests(pullsUrl, repoFullName);
            renderPullList(pulls);
        } catch (error) {
            setPrState('Failed to load pull requests.', 'error');
        }
    };

    const openModal = () => {
        repoModal.classList.add('is-open');
        repoModal.setAttribute('aria-hidden', 'false');
        loadRepos();
    };

    const closeModal = () => {
        repoModal.classList.remove('is-open');
        repoModal.setAttribute('aria-hidden', 'true');
    };

    repoSelectTrigger.addEventListener('click', function (event) {
        event.preventDefault();
        openModal();
    });

    closeButtons.forEach((btn) => {
        btn.addEventListener('click', closeModal);
    });

    repoSelectModal.addEventListener('change', function () {
        if (!repoSelectModal.value) return;
        loadPullRequests(repoSelectModal.value);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });
}

