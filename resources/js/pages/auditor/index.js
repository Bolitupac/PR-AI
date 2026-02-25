import { initMonacoEditor } from './monaco';
import { initGitRepoModal } from './git-repo-modal';

export function initAuditorPage() {
    if (!document.getElementById('monaco-editor')) return;
    initMonacoEditor();
    initGitRepoModal();
}
