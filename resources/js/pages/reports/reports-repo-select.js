import { fetchGitRepos } from '../auditor/git-repos-api';

export async function initReportsRepoSelect() {
    const select = document.getElementById('reports-repo-select');
    if (!select) return;
    
    const url = select.dataset.reposUrl;
    if (!url) return;

    try {
        const repos = await fetchGitRepos(url);
        select.innerHTML = '';
        
        if (!repos || repos.length === 0) {
            const opt = document.createElement('option');
            opt.textContent = "No repositories found";
            opt.disabled = true;
            select.appendChild(opt);
            return;
        }

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select a repository';
        placeholder.disabled = true;
        placeholder.selected = true;
        select.appendChild(placeholder);

        repos.forEach(repo => {
            const opt = document.createElement('option');
            opt.value = repo.full_name || repo.name || '';
            opt.textContent = repo.full_name || repo.name || 'Unnamed repo';
            select.appendChild(opt);
        });
        
        // Reset any error styles if we succeed upon retry
        select.style.color = '';
        select.style.borderColor = '';
        
    } catch (e) {
        select.innerHTML = '';
        const opt = document.createElement('option');
        // Show red error when github is not connected or fetching fails
        opt.textContent = 'Error: GitHub not connected';
        opt.disabled = true;
        select.appendChild(opt);
        
        select.style.color = '#e11d48'; // red
        select.style.borderColor = '#fecdd3';
    }
}
