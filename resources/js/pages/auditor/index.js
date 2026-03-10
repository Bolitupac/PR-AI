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

    // Check for pending audit from Imports page
    const pending = sessionStorage.getItem('pending_audit');
    if (pending) {
        try {
            const data = JSON.parse(pending);
            sessionStorage.removeItem('pending_audit');

            // Small delay to ensure all listeners (auto-audit.js) are ready
            setTimeout(() => {
                document.dispatchEvent(new CustomEvent('auditor:diff-selected', {
                    detail: data
                }));
            }, 100);
        } catch (e) {
            console.error('Failed to parse pending audit:', e);
        }
    }
}
