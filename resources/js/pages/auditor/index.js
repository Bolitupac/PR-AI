import { initGitRepoModal } from './git-repo-modal';
import { initDiffUploadModal } from './diff-upload-modal';
import { initDiffViewer } from './diff-viewer';
import { initChatInput } from './chat-input';
import { initImportUi } from './import';
import { initVoiceInput } from './voice';
import { initAutoAudit } from './auto-audit';
import { initProfileModal } from './profile-modal';
import { initProfileAiKey } from './profile-ai-key';
import { initLoadingInteractions } from './loading-interactions';
import { initThemeToggle } from './theme-toggle';
import { initSidebar } from './sidebar';
import { initChatScrollBottomButton } from './chat-scroll-bottom';
import { createChatStatus } from './chat-status';
import { createLoadingProgress } from './loading-progress';
import { fetchGitPullComments } from './diff-comments/api';

export function initAuditorPage() {
    if (!document.getElementById('ai-response-area')) return;
    initSidebar();
    initThemeToggle();
    initDiffViewer();
    initImportUi();
    initGitRepoModal();
    initDiffUploadModal();
    initChatInput();
    initChatScrollBottomButton();
    initVoiceInput();
    initAutoAudit();
    initProfileModal();
    initProfileAiKey();
    initLoadingInteractions();

    const responseArea = document.getElementById('ai-response-area');

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
        if (pending?.prNumber) {
            const res = await fetch(`/api/github/pull-diff?repo=${encodeURIComponent(pending.repo)}&pr_number=${pending.prNumber}`);
            if (!res.ok) throw new Error('Failed to fetch pull request diff');
            return await res.text();
        }
        if (pending?.commitHash) {
            const res = await fetch(`/api/git/commit-diff?commit=${encodeURIComponent(pending.commitHash)}`);
            if (!res.ok) throw new Error('Failed to fetch commit diff');
            return await res.text();
        }
        if (pending?.branch && pending?.base) {
            const res = await fetch(`/api/github/branch-diff?repo=${encodeURIComponent(pending.repo)}&base=${encodeURIComponent(pending.base)}&head=${encodeURIComponent(pending.branch)}`);
            if (!res.ok) throw new Error('Failed to fetch branch diff');
            return await res.text();
        }
        return null;
    };

    const fetchPendingComments = async (pending) => {
        if (pending?.repo && pending?.prNumber) {
            return await fetchGitPullComments(pending.repo, pending.prNumber);
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
                        label: data?.commitHash ? 'Fetching commit diff' : 'Fetching diff from GitHub'
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
                            appendMessage(error?.message || 'Could not fetch diff from GitHub.', 'ai');
                        });
                    return;
                }
            }, 100);
        } catch (e) {
            console.error('Failed to parse pending audit:', e);
        }
    }
}
