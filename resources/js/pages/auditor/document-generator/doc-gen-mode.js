export function initDocGenMode() {
    const chipWrap = document.getElementById('doc-gen-chip-wrap');
    if (!chipWrap) return;

    let isDocGenActive = false;

    document.addEventListener('auditor:doc-gen-activated', () => {
        isDocGenActive = true;
        chipWrap.classList.remove('is-hidden');
    });

    const closeBtn = document.getElementById('doc-gen-close-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            isDocGenActive = false;
            chipWrap.classList.add('is-hidden');
        });
    }
}
