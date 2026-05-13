document.addEventListener('DOMContentLoaded', function () {
    const hero = document.getElementById('hero-interactive');
    if (hero) {
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
    }

    const revealItems = document.querySelectorAll('[data-reveal]');
    if (!revealItems.length) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.05,
            rootMargin: '0px 0px -10px 0px',
        }
    );

    revealItems.forEach((item, index) => {
        item.style.transitionDelay = `${Math.min(index * 16, 80)}ms`;
        observer.observe(item);
    });
});
