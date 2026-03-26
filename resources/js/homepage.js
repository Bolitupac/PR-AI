document.addEventListener('DOMContentLoaded', function () {
    const hero = document.getElementById('hero-interactive');
    if (!hero) {
        return;
    }

    const setPointer = (event) => {
        const rect = hero.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        hero.style.setProperty('--mx', `${x}px`);
        hero.style.setProperty('--my', `${y}px`);
    };

    hero.addEventListener('mousemove', setPointer);

    hero.addEventListener('mouseleave', function () {
        hero.style.setProperty('--mx', '50%');
        hero.style.setProperty('--my', '36%');
    });
});
