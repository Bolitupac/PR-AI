import { validateDiffText } from '../diff-validator';

export function initImportMonacoModal() {
    const modal = document.getElementById('import-monaco-modal');
    const editorEl = document.getElementById('import-monaco-editor');
    const renderBtn = document.getElementById('import-monaco-render-btn');
    const statusEl = document.getElementById('import-monaco-status');
    const closeNodes = document.querySelectorAll('[data-close="import-monaco-modal"]');

    if (!modal || !editorEl || !renderBtn || !statusEl) return;

    let editor = null;
    let loading = false;

    const setStatus = (text, tone = 'info') => {
        statusEl.textContent = text;
        statusEl.classList.remove('is-info', 'is-success', 'is-error');
        statusEl.classList.add(`is-${tone}`);
    };

    const openModal = async () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        if (!editor && !loading) {
            loading = true;
            setStatus('Loading editor...', 'info');
            try {
                await loadEditor();
                setStatus('Editor ready.', 'success');
            } catch (error) {
                setStatus('Could not load editor.', 'error');
            } finally {
                loading = false;
            }
        }
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    const loadEditor = () => {
        return new Promise((resolve, reject) => {
            if (typeof window.require !== 'function') {
                reject(new Error('Monaco loader missing'));
                return;
            }

            window.require.config({
                paths: {
                    vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs',
                },
            });

            window.require(['vs/editor/editor.main'], () => {
                if (!window.monaco) {
                    reject(new Error('Monaco is unavailable'));
                    return;
                }

                editor = window.monaco.editor.create(editorEl, {
                    value:
                        "# Paste git diff code here...\n" +
                        "# Example:\n" +
                        "# diff --git a/file.js b/file.js\n" +
                        "# @@ -1,2 +1,2 @@\n" +
                        "# -old line\n" +
                        "# +new line",
                    language: 'diff',
                    theme: 'vs-dark',
                    automaticLayout: true,
                    fontSize: 13,
                    lineHeight: 21,
                    fontFamily: "'SF Mono', 'JetBrains Mono', Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace",
                    roundedSelection: true,
                    scrollBeyondLastLine: false,
                    minimap: { enabled: false },
                });
                resolve();
            });
        });
    };

    renderBtn.addEventListener('click', () => {
        if (!editor) {
            setStatus('Editor is not ready yet. Try again.', 'error');
            return;
        }
        const diffText = editor.getValue();
        const validation = validateDiffText(diffText);
        if (!validation.valid) {
            setStatus(validation.reason, 'error');
            return;
        }

        document.dispatchEvent(new CustomEvent('auditor:diff-selected', {
            detail: { source: 'editor', diffText },
        }));
        setStatus('Diff rendered. Auto audit started.', 'success');
        setTimeout(() => closeModal(), 420);
    });

    closeNodes.forEach((node) => node.addEventListener('click', closeModal));

    document.addEventListener('auditor:open-import-monaco-modal', () => {
        openModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
}
