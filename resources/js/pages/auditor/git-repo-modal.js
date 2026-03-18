import { fetchGitRepos } from './git-repos-api';
import { fetchGitPullRequests } from './git-pulls-api';
import { fetchGitPullDiff } from './git-diff-api';
import { setButtonLoading } from './button-loading';

// Controls repo modal open/close, PR selection, and loading diff from GitHub.
export function initGitRepoModal() {
    const repoSelectTrigger = document.getElementById('repo-select');
    const repoModal = document.getElementById('repo-modal');
    const repoSelectModal = document.getElementById('repo-import-select');
    const repoPrBox = document.getElementById('repo-pr-box');
    const repoPrState = document.getElementById('repo-pr-state');
    const repoPrList = document.getElementById('repo-pr-list');
    const repoLoadCue = document.getElementById('repo-load-cue');
    const loadRepoButton = document.getElementById('load-repo-btn');

    if (!repoModal || !repoSelectModal || !repoPrState || !repoPrList || !loadRepoButton) return;

    const reposUrl = repoSelectTrigger?.dataset.reposUrl || repoModal.dataset.reposUrl || '';
    const pullsUrl = repoSelectTrigger?.dataset.pullsUrl || repoModal.dataset.pullsUrl || '';
    const pullDiffUrl = repoSelectTrigger?.dataset.pullDiffUrl || repoModal.dataset.pullDiffUrl || '';
    const closeButtons = document.querySelectorAll('[data-close="repo-modal"]');
    let reposLoaded = false;
    let selectedPullNumber = null;
    let selectedPullTitle = '';
    let loadingTicker = null;
    let repoLoadingTicker = null;
    let cueTimer = null;

    const setSingleOption = (text) => {
        repoSelectModal.innerHTML = '';
        const opt = document.createElement('option');
        opt.textContent = text;
        opt.disabled = true;
        opt.selected = true;
        repoSelectModal.appendChild(opt);
    };

    const stopRepoLoadingTicker = () => {
        if (repoLoadingTicker) {
            clearInterval(repoLoadingTicker);
            repoLoadingTicker = null;
        }
    };

    const startRepoLoadingTicker = (baseText = 'Loading repositories') => {
        stopRepoLoadingTicker();
        const dots = ['.', '..', '...'];
        let index = 0;
        setSingleOption(`${baseText}${dots[index]}`);
        repoLoadingTicker = setInterval(() => {
            index = (index + 1) % dots.length;
            setSingleOption(`${baseText}${dots[index]}`);
        }, 420);
    };

    const setPrState = (text, tone = 'info') => {
        if (tone !== 'loading' && loadingTicker) {
            clearInterval(loadingTicker);
            loadingTicker = null;
        }
        repoPrState.textContent = text;
        repoPrState.classList.remove('is-info', 'is-loading', 'is-success', 'is-empty', 'is-error');
        repoPrState.classList.add(`is-${tone}`);
    };

    // Shows a simple dot-loop while async requests run.
    const startLoadingTicker = (baseText) => {
        if (loadingTicker) {
            clearInterval(loadingTicker);
            loadingTicker = null;
        }
        const dots = ['.', '..', '...'];
        let index = 0;
        setPrState(`${baseText}${dots[index]}`, 'loading');
        loadingTicker = setInterval(() => {
            index = (index + 1) % dots.length;
            setPrState(`${baseText}${dots[index]}`, 'loading');
        }, 420);
    };

    const clearPrList = () => {
        repoPrList.innerHTML = '';
    };

    const flashLoadedCue = () => {
        if (!repoLoadCue) return;
        if (cueTimer) {
            clearTimeout(cueTimer);
            cueTimer = null;
        }
        repoLoadCue.classList.remove('is-show');
        void repoLoadCue.offsetWidth;
        repoLoadCue.classList.add('is-show');
        cueTimer = setTimeout(() => {
            repoLoadCue.classList.remove('is-show');
        }, 1250);
    };

    const setLoadButtonEnabled = (enabled) => {
        loadRepoButton.disabled = !enabled;
    };

    const setLoadedBorder = (loaded) => {
        if (!repoPrBox) return;
        repoPrBox.classList.toggle('is-loaded', loaded);
    };

    const setRepoSelectLoadedBorder = (loaded) => {
        repoSelectModal.classList.toggle('is-loaded', loaded);
    };

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

    const selectPullItem = (item, pull) => {
        repoPrList.querySelectorAll('.repo-pr-item').forEach((node) => {
            node.classList.remove('is-selected');
        });
        item.classList.add('is-selected');
        selectedPullNumber = pull.number ?? null;
        selectedPullTitle = pull.title ?? '';
        repoModal.dataset.selectedPrNumber = selectedPullNumber ? String(selectedPullNumber) : '';
        setLoadButtonEnabled(Boolean(selectedPullNumber));
        setPrState(`Selected PR #${pull.number ?? ''}`, 'success');
    };

    const renderPullList = (pulls) => {
        clearPrList();
        selectedPullNumber = null;
        selectedPullTitle = '';
        repoModal.dataset.selectedPrNumber = '';
        setLoadButtonEnabled(false);
        setLoadedBorder(false);

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
            setLoadButtonEnabled(false);
            setRepoSelectLoadedBorder(false);
            return;
        }

        if (repoSelectTrigger) {
            setButtonLoading(repoSelectTrigger, true, 'Loading');
        }
        setLoadButtonEnabled(false);
        setRepoSelectLoadedBorder(false);
        startRepoLoadingTicker('Loading repositories');

        try {
            const repos = await fetchGitRepos(reposUrl);
            stopRepoLoadingTicker();
            repoSelectModal.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select a repository';
            placeholder.disabled = true;
            placeholder.selected = true;
            repoSelectModal.appendChild(placeholder);

            if (!repos.length) {
                setSingleOption('No repositories found');
                setLoadButtonEnabled(false);
                setRepoSelectLoadedBorder(false);
                return;
            }

            repos.forEach((repo) => {
                const opt = document.createElement('option');
                opt.value = repo.full_name || repo.name || '';
                opt.textContent = repo.full_name || repo.name || 'Unnamed repo';
                repoSelectModal.appendChild(opt);
            });
            reposLoaded = true;
            setRepoSelectLoadedBorder(true);
        } catch (error) {
            stopRepoLoadingTicker();
            setSingleOption('Failed to load repos');
            setLoadButtonEnabled(false);
            setRepoSelectLoadedBorder(false);
        } finally {
            stopRepoLoadingTicker();
            if (repoSelectTrigger) {
                setButtonLoading(repoSelectTrigger, false);
            }
        }
    };

    const loadPullRequests = async (repoFullName) => {
        clearPrList();
        selectedPullNumber = null;
        repoModal.dataset.selectedPrNumber = '';
        setLoadButtonEnabled(false);
        setLoadedBorder(false);
        startLoadingTicker('Loading pull requests');

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

    const loadSelectedPullDiff = async () => {
        const repoFullName = repoSelectModal.value;
        if (!repoFullName || !selectedPullNumber) return;
        if (!pullDiffUrl) {
            setPrState('Pull diff URL is missing.', 'error');
            return;
        }

        startLoadingTicker('Loading selected pull request diff');
        setLoadButtonEnabled(false);
        setButtonLoading(loadRepoButton, true, 'Loading');

        try {
            const diffText = await fetchGitPullDiff(pullDiffUrl, repoFullName, selectedPullNumber);
            document.dispatchEvent(new CustomEvent('auditor:diff-selected', {
                detail: {
                    source: 'github',
                    repo: repoFullName,
                    prNumber: selectedPullNumber,
                    prTitle: selectedPullTitle,
                    auditTitle: `${repoFullName} pull request audit ${selectedPullTitle || `#${selectedPullNumber}`}`.trim(),
                    auditKind: 'pull_request_audit',
                    diffText,
                },
            }));

            setPrState(`Loaded PR #${selectedPullNumber}. Auto audit started.`, 'success');
            setLoadedBorder(true);
            flashLoadedCue();

            setTimeout(() => {
                closeModal();
            }, 520);
        } catch (error) {
            setPrState('Failed to load pull request diff.', 'error');
            setLoadedBorder(false);
        } finally {
            setButtonLoading(loadRepoButton, false);
            setLoadButtonEnabled(Boolean(selectedPullNumber));
        }
    };

    const openModal = () => {
        repoModal.classList.add('is-open');
        repoModal.setAttribute('aria-hidden', 'false');
        setLoadedBorder(false);
        setRepoSelectLoadedBorder(false);
        loadRepos();
    };

    const closeModal = () => {
        repoModal.classList.remove('is-open');
        repoModal.setAttribute('aria-hidden', 'true');
        setLoadedBorder(false);
        stopRepoLoadingTicker();
    };

    repoSelectTrigger?.addEventListener('click', function (event) {
        event.preventDefault();
        openModal();
    });

    document.addEventListener('auditor:open-repo-modal', function () {
        openModal();
    });

    closeButtons.forEach((btn) => {
        btn.addEventListener('click', closeModal);
    });

    repoSelectModal.addEventListener('change', function () {
        if (!repoSelectModal.value) return;
        loadPullRequests(repoSelectModal.value);
    });

    setLoadButtonEnabled(false);

    loadRepoButton.addEventListener('click', function () {
        loadSelectedPullDiff();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });
}
