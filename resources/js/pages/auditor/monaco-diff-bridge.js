import { validateDiffText } from './diff-validator';

// Reads Monaco diff text, validates it, and pushes it to the shared diff viewer.
export function initMonacoDiffBridge() {
    const renderButton = document.getElementById('render-diff-btn');
    const statusEl = document.getElementById('editor-diff-status');
    if (!renderButton || !statusEl) return;

    const setStatus = (text, tone = 'info') => {
        statusEl.textContent = text;
        statusEl.classList.remove('is-info', 'is-success', 'is-error');
        statusEl.classList.add(`is-${tone}`);
    };

    renderButton.addEventListener('click', function () {
        if (!window.editor || typeof window.editor.getValue !== 'function') {
            setStatus('Editor is not ready yet. Try again.', 'error');
            return;
        }

        const diffText = window.editor.getValue();
        const validation = validateDiffText(diffText);
        if (!validation.valid) {
            setStatus(validation.reason, 'error');
            return;
        }

        document.dispatchEvent(new CustomEvent('auditor:diff-selected', {
            detail: { source: 'editor', diffText },
        }));

        setStatus('Diff rendered from editor.', 'success');
        document.getElementById('diff-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}
