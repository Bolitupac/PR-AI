import { initGitRepoModal } from './git-repo-modal';
import { initDiffUploadModal } from './diff-upload-modal';
import { initDiffViewer } from './diff-viewer';
import { initChatInput } from './chat-input';
import { initChatPrelayout } from './chat-prelayout';
import { initImportUi } from './import';
import { initVoiceInput } from './voice';
import { initAutoAudit } from './auto-audit';
import { initProfileModal } from './profile-modal';
import { initProfileAiKey } from './profile-ai-key';
import { initSettingsModal } from './settings-modal';
import { initSettingsAiKey } from './settings-ai-key';
import { initSettingsAiPreferences } from './settings-ai-preferences';
import { initLoadingInteractions } from './loading-interactions';
import { initThemeToggle } from './theme-toggle';
import { initSidebar } from './sidebar';
import { initChatScrollBottomButton } from './chat-scroll-bottom';
import { createChatStatus } from './chat-status';
import { fetchGitPullComments } from './diff-comments/api';
import { initAppsModal } from './document-generator/apps-modal';
import { initDocGenMode } from './document-generator/doc-gen-mode';
import { appendRepoParams, buildVcsUrl } from '../../shared/vcs-repo-query.js';

export function initAuditorPage() {
    if (!document.getElementById('ai-response-area')) return;
    initSidebar();
    initThemeToggle();
    initDiffViewer();
    initImportUi();
    initGitRepoModal();
    initDiffUploadModal();
    initChatPrelayout();
    initChatInput();
    initChatScrollBottomButton();
    initVoiceInput();
    initAutoAudit();
    initProfileModal();
    initProfileAiKey();
    initSettingsModal();
    initSettingsAiKey();
    initSettingsAiPreferences();
    initLoadingInteractions();
    initAppsModal();
    initDocGenMode();

    const responseArea = document.getElementById('ai-response-area');
    const vcsApiBase = '/api/vcs';

    const appendMessage = (text, role) => {
        if (!responseArea) return null;
        const message = document.createElement('div');
        message.className = `msg ${role}`;
        message.textContent = text;
        responseArea.appendChild(message);
        responseArea.scrollTop = responseArea.scrollHeight;
        return message;
    };

    const fetchPendingDiff = async (pending) => {
        if (!pending?.repo) return null;
        const provider = pending?.provider || pending?.source || 'github';
        const repoPayload = {
            full_name: pending.repo,
            provider_repo_id: pending.repoId || null,
            provider_project: pending.project || null,
            provider_organization: pending.organization || null,
            provider_workspace: pending.workspace || null,
            provider_repo_slug: pending.repoSlug || null,
        };
        if (pending?.prNumber) {
            const url = new URL(buildVcsUrl(vcsApiBase, provider, 'pull-diff'), window.location.origin);
            appendRepoParams(url, repoPayload);
            url.searchParams.set('pr_number', pending.prNumber);
            const res = await fetch(url.toString());
            if (!res.ok) throw new Error('Failed to fetch pull request diff');
            return await res.text();
        }
        if (pending?.commitHash) {
            const res = await fetch(`/api/git/commit-diff?commit=${encodeURIComponent(pending.commitHash)}`);
            if (!res.ok) throw new Error('Failed to fetch commit diff');
            return await res.text();
        }
        if (pending?.branch && pending?.base) {
            const url = new URL(buildVcsUrl(vcsApiBase, provider, 'branch-diff'), window.location.origin);
            appendRepoParams(url, repoPayload);
            url.searchParams.set('base', pending.base);
            url.searchParams.set('head', pending.branch);
            const res = await fetch(url.toString());
            if (!res.ok) throw new Error('Failed to fetch branch diff');
            return await res.text();
        }
        return null;
    };

    const fetchPendingComments = async (pending) => {
        if (pending?.repo && pending?.prNumber) {
            return await fetchGitPullComments({
                full_name: pending.repo,
                provider_repo_id: pending.repoId || null,
                provider_project: pending.project || null,
                provider_organization: pending.organization || null,
                provider_workspace: pending.workspace || null,
                provider_repo_slug: pending.repoSlug || null,
            }, pending.prNumber, pending?.provider || pending?.source || 'github', vcsApiBase);
        }

        return [];
    };

    // Check for pending audit from Imports page
    const pending = sessionStorage.getItem('pending_audit');
    if (pending) {
        try {
            const data = JSON.parse(pending);
            sessionStorage.removeItem('pending_audit');

            // Small delay to ensure all listeners (auto-audit.js) are ready
            setTimeout(() => {
                const dispatchDiff = (diffText, comments = []) => {
                    const compareType = data?.compareType
                        || (data?.prNumber ? 'pull_request' : (data?.commitHash ? 'commit' : (data?.branch && data?.base ? 'branch_vs_main' : 'upload')));
                    document.dispatchEvent(new CustomEvent('auditor:diff-selected', {
                        detail: {
                            ...data,
                            diffText: diffText || '',
                            comments,
                            compareType: compareType,
                            baseBranch: data?.base || null,
                            headBranch: data?.branch || null,
                            prTitle: data?.prTitle || null,
                            auditTitle: data?.auditTitle || null,
                            auditKind: data?.auditKind || null,
                        }
                    }));
                };

                if (data?.diffText) {
                    dispatchDiff(data.diffText, data?.comments || []);
                    return;
                }

                if (data?.repo) {
                    const status = createChatStatus({ container: responseArea, anchorNode: null });
                    const progress = createLoadingProgress({
                        onUpdate: (text) => status.set(text),
                        label: data?.commitHash ? 'Fetching commit diff' : `Fetching diff from ${(data?.provider || data?.source || 'provider')}`
                    });
                    Promise.all([
                        fetchPendingDiff(data),
                        fetchPendingComments(data),
                    ])
                        .then(([diffText, comments]) => {
                            if (!diffText || !diffText.trim()) {
                                progress.stop();
                                status.markError('Diff is empty.');
                                appendMessage('Fetched diff was empty.', 'ai');
                                return;
                            }
                            progress.stop('Diff received. Starting audit...');
                            dispatchDiff(diffText, comments);
                            status.remove(700);
                        })
                        .catch((error) => {
                            progress.stop();
                            status.markError('Failed to fetch diff.');
                            appendMessage(error?.message || 'Could not fetch diff from the selected provider.', 'ai');
                        });
                    return;
                }
            }, 100);
        } catch (e) {
            console.error('Failed to parse pending audit:', e);
        }
    }
}
