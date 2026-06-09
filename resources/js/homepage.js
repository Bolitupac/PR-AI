document.addEventListener('DOMContentLoaded', function () {
    // Hero interactive glow effect
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

    // Instant reveal on scroll — no staggered delays, just show immediately
    const revealItems = document.querySelectorAll('[data-reveal]');
    if (revealItems.length) {
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
                threshold: 0.02,
                rootMargin: '0px 0px -5px 0px',
            }
        );

        revealItems.forEach((item) => {
            observer.observe(item);
        });
    }

    // See More / See Less toggle for feature cards
    const seeMoreBtn = document.getElementById('see-more-btn');
    const hiddenWrapper = document.getElementById('feature-hidden-wrapper');

    if (seeMoreBtn && hiddenWrapper) {
        const seeMoreText = seeMoreBtn.querySelector('.see-more-text');

        seeMoreBtn.addEventListener('click', function () {
            const isOpen = hiddenWrapper.classList.contains('is-expanded');

            if (isOpen) {
                // Collapse
                hiddenWrapper.classList.remove('is-expanded');
                seeMoreBtn.classList.remove('is-open');
                seeMoreBtn.setAttribute('aria-expanded', 'false');
                if (seeMoreText) seeMoreText.textContent = 'See more';

                // Scroll back to feature section top
                const features = document.getElementById('features');
                if (features) {
                    features.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                // Expand
                hiddenWrapper.classList.add('is-expanded');
                seeMoreBtn.classList.add('is-open');
                seeMoreBtn.setAttribute('aria-expanded', 'true');
                if (seeMoreText) seeMoreText.textContent = 'See less';

                // Reveal any hidden cards that come into view
                setTimeout(() => {
                    hiddenWrapper.querySelectorAll('[data-reveal]').forEach((item) => {
                        item.classList.add('is-visible');
                    });
                }, 100);
            }
        });
    }
});
