import { initMonacoEditor } from './monaco';
import { initGitRepoModal } from './git-repo-modal';
import { initDiffUploadModal } from './diff-upload-modal';
import { initDiffViewer } from './diff-viewer';
import { initMonacoDiffBridge } from './monaco-diff-bridge';
import { initChatInput } from './chat-input';
import { initMicListeningModal } from './mic-listening-modal';

export function initAuditorPage() {
    if (!document.getElementById('monaco-editor')) return;
    initMonacoEditor();
    initDiffViewer();
    initGitRepoModal();
    initDiffUploadModal();
    initMonacoDiffBridge();
    initChatInput();
    initMicListeningModal();
}
