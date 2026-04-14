import { initThemeToggle } from '../auditor/theme-toggle';
import { initSidebar } from '../auditor/sidebar';
import { initSettingsModal } from '../auditor/settings-modal';
import { initSettingsAiKey } from '../auditor/settings-ai-key';
import { initSettingsAiPreferences } from '../auditor/settings-ai-preferences';
import { initReportsUI } from './reports-ui';
import { initReportsRepoSelect } from './reports-repo-select';

export function initReportsPage() {
    const page = document.getElementById('reports-page');
    if (!page) return;

    initSidebar();
    initThemeToggle();
    initSettingsModal();
    initSettingsAiKey();
    initSettingsAiPreferences();

    initReportsUI();
    initReportsRepoSelect();
}
