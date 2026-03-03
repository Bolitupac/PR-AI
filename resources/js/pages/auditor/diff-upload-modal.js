import { readDiffFile } from './diff-file-reader';
import { setButtonLoading } from './button-loading';

// Controls the diff upload modal and emits selected diff content.
export function initDiffUploadModal() {
    const openBtn = document.getElementById('upload-diff-btn');
    const modal = document.getElementById('diff-upload-modal');
    const closeBtns = document.querySelectorAll('[data-close="diff-upload-modal"]');
    const dropzone = document.getElementById('diff-dropzone');
    const fileInput = document.getElementById('diff-file-input');
    const fileName = document.getElementById('diff-file-name');
    const state = document.getElementById('diff-upload-state');
    const actionBtn = document.getElementById('diff-upload-action');

    if (!openBtn || !modal || !dropzone || !fileInput || !fileName || !state || !actionBtn) return;

    let selectedDiff = null;

    const setState = (text, tone = 'info') => {
        state.textContent = text;
        state.classList.remove('is-info', 'is-loading', 'is-success', 'is-error');
        state.classList.add(`is-${tone}`);
    };

    const setActionEnabled = (enabled) => {
        actionBtn.disabled = !enabled;
    };

    const openModal = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    const loadFile = async (file) => {
        setState('Reading file...', 'loading');
        setActionEnabled(false);
        try {
            selectedDiff = await readDiffFile(file);
            fileName.textContent = selectedDiff.name;
            setState('Diff file loaded.', 'success');
            setActionEnabled(true);
            modal.dataset.diffContent = selectedDiff.content;
            modal.dataset.diffName = selectedDiff.name;
        } catch (error) {
            selectedDiff = null;
            fileName.textContent = 'No file selected.';
            modal.dataset.diffContent = '';
            modal.dataset.diffName = '';
            setState(error.message || 'Invalid file', 'error');
            setActionEnabled(false);
        }
    };

    openBtn.addEventListener('click', function () {
        openModal();
    });

    closeBtns.forEach((btn) => {
        btn.addEventListener('click', function () {
            closeModal();
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });

    fileInput.addEventListener('change', function () {
        const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
        if (!file) return;
        loadFile(file);
    });

    dropzone.addEventListener('dragover', function (event) {
        event.preventDefault();
        dropzone.classList.add('is-dragging');
    });

    dropzone.addEventListener('dragleave', function () {
        dropzone.classList.remove('is-dragging');
    });

    dropzone.addEventListener('drop', function (event) {
        event.preventDefault();
        dropzone.classList.remove('is-dragging');
        const file = event.dataTransfer?.files?.[0];
        if (!file) return;
        loadFile(file);
    });

    actionBtn.addEventListener('click', function () {
        if (!selectedDiff) return;
        const run = async () => {
            setButtonLoading(actionBtn, true, 'Loading');
            setState(`Using ${selectedDiff.name}`, 'success');
            document.dispatchEvent(new CustomEvent('auditor:diff-selected', {
                detail: {
                    source: 'upload',
                    name: selectedDiff.name,
                    diffText: selectedDiff.content,
                },
            }));
            setState('Auto audit started.', 'success');

            setTimeout(() => {
                closeModal();
                setButtonLoading(actionBtn, false);
            }, 520);
        };
        run();
    });
}
