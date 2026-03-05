import { initImportMenu } from './import-menu';
import { initInputPlusMenu } from './import-plus-menu';
import { initImportMonacoModal } from './import-monaco-modal';
import { initImportPasteModal } from './import-paste-modal';

export function initImportUi() {
    initImportMenu();
    initInputPlusMenu();
    initImportMonacoModal();
    initImportPasteModal();
}
