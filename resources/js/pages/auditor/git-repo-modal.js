import { fetchGitRepos } from './git-repos-api';

export function initGitRepoModal() {
    const repoSelectTrigger = document.getElementById('repo-select');
    const repoModal = document.getElementById('repo-modal');
    const repoSelectModal = document.getElementById('repo-import-select');

    if (!repoSelectTrigger || !repoModal || !repoSelectModal) return;

    const reposUrl = repoSelectTrigger.dataset.reposUrl;
    const closeButtons = document.querySelectorAll('[data-close="repo-modal"]');
    let reposLoaded = false;

    const setSingleOption = (text) => {
        repoSelectModal.innerHTML = '';
        const opt = document.createElement('option');
        opt.textContent = text;
        opt.disabled = true;
        opt.selected = true;
        repoSelectModal.appendChild(opt);
    };

    const loadRepos = async () => {
        if (reposLoaded) return;
        if (!reposUrl) {
            setSingleOption('Repo URL is missing');
            return;
        }

        try {
            const repos = await fetchGitRepos(reposUrl);
            repoSelectModal.innerHTML = '';

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

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });
}
