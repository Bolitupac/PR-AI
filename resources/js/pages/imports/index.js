import { initThemeToggle } from '../auditor/theme-toggle';
import { initSidebar } from '../auditor/sidebar';
import { initImportsAccordion } from './accordion';

// Bootstraps the static imports page.
export function initImportsPage() {
    if (!document.getElementById('imports-page')) return;
    initSidebar();
    initThemeToggle();
    initImportsAccordion();
}

