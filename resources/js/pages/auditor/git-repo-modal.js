import { fetchGitRepos } from './git-repos-api.js';
import { fetchGitPullRequests } from './git-pulls-api.js';
import { fetchGitPullDiff } from './git-diff-api.js';
import { fetchGitPullComments } from './diff-comments/api.js';
import { setButtonLoading } from './button-loading.js';
import { buildVcsUrl } from '../../shared/vcs-repo-query.js';

export function initGitRepoModal() {
    const repoSelectTrigger = document.getElementById('repo-select');
    const repoModal = document.getElementById('repo-modal');
    const providerSelect = document.getElementById('repo-provider-select');
    const repoSelectModal = document.getElementById('repo-import-select');
    const repoImportHelp = document.getElementById('repo-import-help');
    const repoActionsRow = document.getElementById('repo-import-actions-row');
    const repoConnectButton = document.getElementById('repo-connect-github-btn');
    const repoRetryButton = document.getElementById('repo-retry-btn');
    const repoPrBox = document.getElementById('repo-pr-box');
    const repoPrState = document.getElementById('repo-pr-state');
    const repoPrList = document.getElementById('repo-pr-list');
    const repoLoadCue = document.getElementById('repo-load-cue');
    const loadRepoButton = document.getElementById('load-repo-btn');

    if (!repoModal || !repoSelectModal || !repoPrState || !repoPrList || !loadRepoButton) return;

    const apiBase = repoModal.dataset.vcsApiBase || '/api/vcs';
    const providerConfig = JSON.parse(repoModal.dataset.vcsProviders || '{}');
    const legacyReposUrl = repoSelectTrigger?.dataset.reposUrl || repoModal.dataset.reposUrl || '';
    const legacyPullsUrl = repoSelectTrigger?.dataset.pullsUrl || repoModal.dataset.pullsUrl || '';
    const legacyPullDiffUrl = repoSelectTrigger?.dataset.pullDiffUrl || repoModal.dataset.pullDiffUrl || '';
    const useLegacyGithubRoutes = !repoModal.dataset.vcsApiBase;
    const closeButtons = document.querySelectorAll('[data-close="repo-modal"]');
    let currentProvider = providerSelect?.value || repoModal.dataset.defaultProvider || 'github';
    let reposLoaded = false;
    let selectedPullNumber = null;
    let selectedPullTitle = '';
    let selectedRepo = null;
    let loadingTicker = null;
    let repoLoadingTicker = null;
    let cueTimer = null;
    let repoIndex = new Map();

    const providerLabel = () => providerConfig?.[currentProvider]?.label || currentProvider;
    const connectUrl = () => currentProvider === 'github' ? '/auth/github' : '#settings-vcs';
    const reposUrl = () => useLegacyGithubRoutes ? legacyReposUrl : buildVcsUrl(apiBase, currentProvider, 'repos');
    const pullsUrl = () => useLegacyGithubRoutes ? legacyPullsUrl : buildVcsUrl(apiBase, currentProvider, 'pulls');
    const pullDiffUrl = () => useLegacyGithubRoutes ? legacyPullDiffUrl : buildVcsUrl(apiBase, currentProvider, 'pull-diff');

    const setSingleOption = (text) => {
        repoSelectModal.innerHTML = '';
        const opt = document.createElement('option');
        opt.textContent = text;
        opt.disabled = true;
        opt.selected = true;
        repoSelectModal.appendChild(opt);
    };

    const setHelp = (text = '', tone = '') => {
        if (!repoImportHelp) return;
        repoImportHelp.textContent = text;
        repoImportHelp.classList.remove('is-error', 'is-success');
        if (tone) repoImportHelp.classList.add(`is-${tone}`);
    };

    const setActions = ({ show = false, showConnect = false, retryText = 'Retry', connectHref = connectUrl() } = {}) => {
        if (repoActionsRow) repoActionsRow.hidden = !show;
        if (repoConnectButton) {
            repoConnectButton.hidden = !showConnect;
            repoConnectButton.href = connectHref || connectUrl();
        }
        if (repoRetryButton) repoRetryButton.textContent = retryText;
    };

    const clearPrList = () => {
        repoPrList.innerHTML = '';
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

    const startLoadingTicker = (baseText) => {
        if (loadingTicker) clearInterval(loadingTicker);
        const dots = ['.', '..', '...'];
        let index = 0;
        setPrState(`${baseText}${dots[index]}`, 'loading');
        loadingTicker = setInterval(() => {
            index = (index + 1) % dots.length;
            setPrState(`${baseText}${dots[index]}`, 'loading');
        }, 420);
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

    const flashLoadedCue = () => {
        if (!repoLoadCue) return;
        if (cueTimer) clearTimeout(cueTimer);
        repoLoadCue.classList.remove('is-show');
        void repoLoadCue.offsetWidth;
        repoLoadCue.classList.add('is-show');
        cueTimer = setTimeout(() => repoLoadCue.classList.remove('is-show'), 1250);
    };

    const setLoadButtonEnabled = (enabled) => {
        loadRepoButton.disabled = !enabled;
    };

    const setLoadedBorder = (loaded) => {
        repoPrBox?.classList.toggle('is-loaded', loaded);
    };

    const setRepoSelectLoadedBorder = (loaded) => {
        repoSelectModal.classList.toggle('is-loaded', loaded);
    };

    const resetRepoUi = () => {
        setHelp('');
        setActions({ show: false });
        clearPrList();
        setPrState('Select a repository to view pull requests.', 'info');
        setLoadedBorder(false);
        setRepoSelectLoadedBorder(false);
        setLoadButtonEnabled(false);
        selectedPullNumber = null;
        selectedPullTitle = '';
        selectedRepo = null;
        repoModal.dataset.selectedPrNumber = '';
        repoIndex = new Map();
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
        repoPrList.querySelectorAll('.repo-pr-item').forEach((node) => node.classList.remove('is-selected'));
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
            item.addEventListener('click', () => selectPullItem(item, pull));
            repoPrList.appendChild(item);
        });
    };

    const loadRepos = async () => {
        reposLoaded = false;
        resetRepoUi();
        startRepoLoadingTicker('Loading repositories');
        setHelp(useLegacyGithubRoutes ? 'Checking your GitHub connection...' : `Checking your ${providerLabel()} connection...`);
        if (repoSelectTrigger) setButtonLoading(repoSelectTrigger, true, 'Loading');

        try {
            const repos = await fetchGitRepos(reposUrl());
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
                setHelp(`This ${providerLabel()} connection does not have any accessible repositories yet.`);
                setActions({ show: true, showConnect: false, retryText: 'Refresh' });
                return;
            }

            repos.forEach((repo) => {
                repoIndex.set(repo.full_name, repo);
                const opt = document.createElement('option');
                opt.value = repo.full_name || repo.name || '';
                opt.textContent = repo.full_name || repo.name || 'Unnamed repo';
                repoSelectModal.appendChild(opt);
            });
            reposLoaded = true;
            setRepoSelectLoadedBorder(true);
            setHelp(useLegacyGithubRoutes ? 'Choose a repository to load its pull requests.' : `Choose a ${providerLabel()} repository to load its pull requests.`, 'success');
            setActions({ show: true, showConnect: false, retryText: 'Refresh' });
        } catch (error) {
            stopRepoLoadingTicker();
            setSingleOption('Repositories unavailable');
            setHelp(error?.message || 'Failed to load repositories.', 'error');
            setActions({
                show: true,
                showConnect: true,
                retryText: 'Retry',
                connectHref: error?.connectUrl || connectUrl(),
            });
            setPrState(`Repository loading is blocked until ${providerLabel()} access is restored.`, 'error');
        } finally {
            stopRepoLoadingTicker();
            if (repoSelectTrigger) setButtonLoading(repoSelectTrigger, false);
        }
    };

    const loadPullRequests = async (repoFullName) => {
        clearPrList();
        selectedPullNumber = null;
        selectedRepo = repoIndex.get(repoFullName) || null;
        repoModal.dataset.selectedPrNumber = '';
        setLoadButtonEnabled(false);
        setLoadedBorder(false);
        startLoadingTicker('Loading pull requests');

        try {
            const pulls = await fetchGitPullRequests(pullsUrl(), selectedRepo || repoFullName);
            renderPullList(pulls);
            setHelp(`Loaded pull requests for ${repoFullName}.`, 'success');
        } catch (error) {
            setPrState(error?.message || 'Failed to load pull requests.', 'error');
            setHelp(error?.message || 'Failed to load pull requests.', 'error');
            setActions({ show: true, showConnect: true, retryText: 'Retry', connectHref: error?.connectUrl || connectUrl() });
        }
    };

    const loadSelectedPullDiff = async () => {
        if (!selectedRepo || !selectedPullNumber) return;
        startLoadingTicker('Loading selected pull request diff');
        setLoadButtonEnabled(false);
        setButtonLoading(loadRepoButton, true, 'Loading');

        try {
            const [diffText, comments] = await Promise.all([
                fetchGitPullDiff(pullDiffUrl(), selectedRepo, selectedPullNumber),
                fetchGitPullComments(selectedRepo, selectedPullNumber, currentProvider, useLegacyGithubRoutes ? '/api' : apiBase),
            ]);
            const detail = {
                source: currentProvider,
                repo: selectedRepo.full_name,
                prNumber: selectedPullNumber,
                prTitle: selectedPullTitle,
                auditStatus: 'open',
                auditTitle: `${selectedRepo.full_name} pull request audit ${selectedPullTitle || `#${selectedPullNumber}`}`.trim(),
                auditKind: 'pull_request_audit',
                diffText,
                comments,
            };
            if (selectedRepo.provider_repo_id) detail.repoId = selectedRepo.provider_repo_id;
            if (selectedRepo.provider_project) detail.project = selectedRepo.provider_project;
            if (selectedRepo.provider_organization) detail.organization = selectedRepo.provider_organization;
            if (selectedRepo.provider_workspace) detail.workspace = selectedRepo.provider_workspace;
            if (selectedRepo.provider_repo_slug) detail.repoSlug = selectedRepo.provider_repo_slug;
            document.dispatchEvent(new CustomEvent('auditor:diff-selected', {
                detail,
            }));

            setPrState(`Loaded PR #${selectedPullNumber}. Auto audit started.`, 'success');
            setLoadedBorder(true);
            flashLoadedCue();
            setTimeout(closeModal, 520);
        } catch (error) {
            setPrState(error?.message || 'Failed to load pull request diff.', 'error');
            setHelp(error?.message || 'Failed to load pull request diff.', 'error');
            setActions({ show: true, showConnect: true, retryText: 'Retry', connectHref: error?.connectUrl || connectUrl() });
            setLoadedBorder(false);
        } finally {
            setButtonLoading(loadRepoButton, false);
            setLoadButtonEnabled(Boolean(selectedPullNumber));
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
        setLoadedBorder(false);
        stopRepoLoadingTicker();
    };

    providerSelect?.addEventListener('change', () => {
        currentProvider = providerSelect.value || repoModal.dataset.defaultProvider || 'github';
        loadRepos();
    });

    repoSelectTrigger?.addEventListener('click', (event) => {
        event.preventDefault();
        openModal();
    });
    document.addEventListener('auditor:open-repo-modal', openModal);
    closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
    repoSelectModal.addEventListener('change', () => {
        if (!repoSelectModal.value) return;
        setHelp(`Loading pull requests for ${repoSelectModal.value}...`);
        loadPullRequests(repoSelectModal.value);
    });
    repoRetryButton?.addEventListener('click', loadRepos);
    loadRepoButton.addEventListener('click', loadSelectedPullDiff);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeModal();
    });

    setLoadButtonEnabled(false);
}
