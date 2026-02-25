import { initMonacoEditor } from './monaco';
import { initGitRepoModal } from './git-repo-modal';
import { initDiffUploadModal } from './diff-upload-modal';

export function initAuditorPage() {
    if (!document.getElementById('monaco-editor')) return;
    initMonacoEditor();
    initGitRepoModal();
    initDiffUploadModal();
}
