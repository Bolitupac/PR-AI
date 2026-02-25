import { initMonacoEditor } from './monaco';
import { initGitRepoModal } from './git-repo-modal';
import { initDiffUploadModal } from './diff-upload-modal';
import { initDiffViewer } from './diff-viewer';

export function initAuditorPage() {
    if (!document.getElementById('monaco-editor')) return;
    initMonacoEditor();
    initDiffViewer();
    initGitRepoModal();
    initDiffUploadModal();
}
