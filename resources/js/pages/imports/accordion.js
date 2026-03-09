// Handles simple expand/collapse for repo and branch rows on the imports page.
export function initImportsAccordion() {
    const root = document.getElementById('imports-page');
    if (!root) return;

    root.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-import-toggle]');
        if (!trigger) return;

        const targetId = trigger.getAttribute('data-import-toggle');
        if (!targetId) return;

        const panel = document.getElementById(targetId);
        if (!panel) return;

        const expanded = trigger.getAttribute('aria-expanded') === 'true';
        trigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        panel.hidden = expanded;
    });
}