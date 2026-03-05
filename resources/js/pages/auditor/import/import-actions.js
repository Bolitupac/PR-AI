export function dispatchImportAction(kind) {
    if (kind === 'repo') {
        document.dispatchEvent(new CustomEvent('auditor:open-repo-modal'));
        return;
    }
    if (kind === 'upload') {
        document.dispatchEvent(new CustomEvent('auditor:open-import-monaco-modal'));
        return;
    }
    if (kind === 'paste') {
        document.dispatchEvent(new CustomEvent('auditor:open-import-paste-modal'));
    }
}

