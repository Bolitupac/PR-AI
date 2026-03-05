import { initMonacoEditor } from './monaco';
import { initGitRepoModal } from './git-repo-modal';
import { initDiffUploadModal } from './diff-upload-modal';
import { initDiffViewer } from './diff-viewer';
import { initMonacoDiffBridge } from './monaco-diff-bridge';
import { initChatInput } from './chat-input';
import { initVoiceInput } from './voice';
import { initAutoAudit } from './auto-audit';
import { initProfileModal } from './profile-modal';
import { initProfileAiKey } from './profile-ai-key';
import { initLoadingInteractions } from './loading-interactions';

export function initAuditorPage() {
    if (!document.getElementById('monaco-editor')) return;
    initMonacoEditor();
    initDiffViewer();
    initGitRepoModal();
    initDiffUploadModal();
    initMonacoDiffBridge();
    initChatInput();
    initVoiceInput();
    initAutoAudit();
    initProfileModal();
    initProfileAiKey();
    initLoadingInteractions();
}
