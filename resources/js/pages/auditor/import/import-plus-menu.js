export function initInputPlusMenu() {
    const wrap = document.getElementById('import-plus-wrap');
    const trigger = document.getElementById('import-plus-trigger');
    if (!wrap || !trigger) return;

    const setOpen = (open) => {
        wrap.classList.toggle('is-open', open);
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        setOpen(!wrap.classList.contains('is-open'));
    });

    wrap.querySelectorAll('[data-import-choice]').forEach((item) => {
        item.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('click', (event) => {
        if (!wrap.contains(event.target)) setOpen(false);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
}
