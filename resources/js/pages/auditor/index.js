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

export function initAuditorPage() {
    if (!document.getElementById('ai-response-area')) return;
    initDiffViewer();
    initImportUi();
    initGitRepoModal();
    initDiffUploadModal();
    initChatInput();
    initVoiceInput();
    initAutoAudit();
    initProfileModal();
    initProfileAiKey();
    initLoadingInteractions();
}
