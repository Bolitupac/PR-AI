import { validateDiffText } from './diff-validator';
import { saveAuditSnapshot } from './audit-snapshot-api';

// Reads Monaco diff text, validates it, and pushes it to the shared diff viewer.
export function initMonacoDiffBridge() {
    const renderButton = document.getElementById('render-diff-btn');
    const statusEl = document.getElementById('editor-diff-status');
    if (!renderButton || !statusEl) return;
    const snapshotUrl = renderButton.dataset.snapshotUrl;
    let statusTicker = null;

    const stopStatusTicker = () => {
        if (statusTicker) {
            clearInterval(statusTicker);
            statusTicker = null;
        }
    };

    const setStatus = (text, tone = 'info') => {
        stopStatusTicker();
        statusEl.textContent = text;
        statusEl.classList.remove('is-info', 'is-success', 'is-error');
        statusEl.classList.add(`is-${tone}`);
    };

    const startStatusDots = (baseText) => {
        stopStatusTicker();
        const dots = ['.', '..', '...'];
        let index = 0;
        setStatus(`${baseText}${dots[index]}`, 'info');
        statusTicker = setInterval(() => {
            index = (index + 1) % dots.length;
            statusEl.textContent = `${baseText}${dots[index]}`;
        }, 420);
    };

    renderButton.addEventListener('click', async function () {
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

        if (snapshotUrl) {
            startStatusDots('Saving snapshot file');
            renderButton.disabled = true;
            try {
                const snapshot = await saveAuditSnapshot(snapshotUrl, {
                    source: 'upload',
                    file_name: 'editor.diff',
                    diff_text: diffText,
                });
                setStatus(`Diff rendered. Snapshot saved: ${snapshot.path}`, 'success');
            } catch (error) {
                setStatus('Diff rendered. Snapshot save failed.', 'error');
            } finally {
                renderButton.disabled = false;
            }
        } else {
            setStatus('Diff rendered from editor.', 'success');
        }

        document.getElementById('diff-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}
