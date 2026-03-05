import { dispatchImportAction } from './import-actions';

export function initImportMenu() {
    const choices = document.querySelectorAll('[data-import-choice]');
    if (!choices.length) return;

    choices.forEach((choice) => {
        choice.addEventListener('click', () => {
            const kind = choice.dataset.importChoice || '';
            dispatchImportAction(kind);
        });
    });
}
