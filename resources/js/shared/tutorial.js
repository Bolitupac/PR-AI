/**
 * Interactive Tutorial Engine
 *
 * Walks users through the UI step-by-step with a blur overlay, spotlight
 * highlighting, and a positioned info card. Supports both the Auditor and
 * Imports page tutorials. Tracks completion via a server API call.
 */

const TUTORIAL_COMPLETE_URL = '/api/tutorial/complete';

let activeTutorial = null;

// ── DOM helpers ──────────────────────────────────────────────────────────

function createOverlay() {
    const el = document.createElement('div');
    el.className = 'tutorial-overlay';
    document.body.appendChild(el);
    return el;
}

function createSpotlight() {
    const el = document.createElement('div');
    el.className = 'tutorial-spotlight';
    document.body.appendChild(el);
    return el;
}

function createCard() {
    const el = document.createElement('div');
    el.className = 'tutorial-card';
    document.body.appendChild(el);
    return el;
}

function getRect(target) {
    if (typeof target === 'string') {
        const el = document.querySelector(target);
        if (!el) return null;
        return el.getBoundingClientRect();
    }
    if (target instanceof HTMLElement) {
        return target.getBoundingClientRect();
    }
    return null;
}

// ── Spotlight positioning ────────────────────────────────────────────────

function positionSpotlight(spotlight, rect, padding = 8) {
    spotlight.style.left   = (rect.left - padding) + 'px';
    spotlight.style.top    = (rect.top - padding) + 'px';
    spotlight.style.width  = (rect.width + padding * 2) + 'px';
    spotlight.style.height = (rect.height + padding * 2) + 'px';
    spotlight.style.borderRadius = '14px';
}

// ── Card positioning ─────────────────────────────────────────────────────

function positionCard(card, rect, placement = 'bottom') {
    const cardW = card.offsetWidth  || 400;
    const cardH = card.offsetHeight || 180;
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const gap = 16;

    let left, top;

    switch (placement) {
        case 'bottom':
            left = Math.max(12, Math.min(rect.left + rect.width / 2 - cardW / 2, vw - cardW - 12));
            top = rect.bottom + gap;
            if (top + cardH > vh - 20) top = rect.top - cardH - gap; // flip above
            break;
        case 'top':
            left = Math.max(12, Math.min(rect.left + rect.width / 2 - cardW / 2, vw - cardW - 12));
            top = rect.top - cardH - gap;
            if (top < 20) top = rect.bottom + gap; // flip below
            break;
        case 'right':
            left = rect.right + gap;
            if (left + cardW > vw - 12) left = rect.left - cardW - gap; // flip left
            top = Math.max(12, Math.min(rect.top + rect.height / 2 - cardH / 2, vh - cardH - 12));
            break;
        case 'left':
            left = rect.left - cardW - gap;
            if (left < 12) left = rect.right + gap; // flip right
            top = Math.max(12, Math.min(rect.top + rect.height / 2 - cardH / 2, vh - cardH - 12));
            break;
        case 'center':
        default:
            left = (vw - cardW) / 2;
            top = (vh - cardH) / 2;
            break;
    }

    card.style.left = Math.round(left) + 'px';
    card.style.top  = Math.round(top) + 'px';
}

// ── Tutorial Runner ──────────────────────────────────────────────────────

export function runTutorial(steps, options = {}) {
    if (activeTutorial) {
        activeTutorial.destroy();
    }

    const {
        page = 'auditor',
        onComplete = null,
        autoStart = false,
    } = options;

    const overlay   = createOverlay();
    const spotlight = createSpotlight();
    const card      = createCard();
    let currentIdx  = 0;

    // ── Render current step ───────────────────────────────────────────

    const render = () => {
        const step = steps[currentIdx];
        if (!step) return;

        const isWelcome = step.welcome === true;
        const isLast    = currentIdx === steps.length - 1;

        card.className = 'tutorial-card' + (isWelcome ? ' tutorial-welcome-card' : '');

        if (isWelcome) {
            card.innerHTML = `
                <span class="tutorial-welcome-emoji">👋</span>
                <div class="tutorial-card__step">${step.stepLabel || 'Welcome'}</div>
                <div class="tutorial-card__title">${step.title || 'Welcome to PR-AI'}</div>
                ${step.subtitle ? `<div class="tutorial-card__subtitle">${step.subtitle}</div>` : ''}
                <div class="tutorial-card__text">${step.text || ''}</div>
                ${step.extraHtml || ''}
                <div class="tutorial-card__actions">
                    <button class="tutorial-btn tutorial-btn--skip" data-action="skip">Skip Tutorial</button>
                    <button class="tutorial-btn tutorial-btn--primary" data-action="next">
                        ${step.nextLabel || 'Start Tour →'}
                    </button>
                </div>
            `;
        } else {
            card.innerHTML = `
                <div class="tutorial-card__step">Step ${currentIdx} of ${steps.length - (steps[0]?.welcome ? 1 : 0)}</div>
                <div class="tutorial-card__title">${step.title || ''}</div>
                <div class="tutorial-card__text">${step.text || ''}</div>
                <div class="tutorial-card__actions">
                    <div class="tutorial-card__nav">
                        <button class="tutorial-btn tutorial-btn--ghost" data-action="prev" ${currentIdx === 0 ? 'disabled' : ''}>
                            ← Previous
                        </button>
                        <button class="tutorial-btn tutorial-btn--primary" data-action="next">
                            ${isLast ? (step.nextLabel || 'Finish ✓') : (step.nextLabel || 'Next →')}
                        </button>
                    </div>
                    <button class="tutorial-btn tutorial-btn--skip" data-action="skip">Skip</button>
                </div>
            `;
        }

        // ── Position overlay / spotlight / card ──────────────────────

        if (step.target) {
            const rect = getRect(step.target);
            if (rect && rect.width > 0 && rect.height > 0) {
                // Highlight the target
                positionSpotlight(spotlight, rect, step.padding || 8);
                spotlight.classList.add('is-active');
                overlay.classList.add('is-active');

                // Show card near the target
                positionCard(card, rect, step.placement || 'bottom');
            } else {
                // Target not found — show centered
                spotlight.classList.remove('is-active');
                overlay.classList.add('is-active');
                positionCard(card, null, 'center');
            }
        } else {
            // No target — center card on blurred overlay
            spotlight.classList.remove('is-active');
            overlay.classList.add('is-active');
            positionCard(card, null, 'center');
        }

        // Brief delay so the card positions before fading in
        requestAnimationFrame(() => {
            card.classList.add('is-active');
        });

        // Scroll target into view if needed
        if (step.target) {
            const el = typeof step.target === 'string'
                ? document.querySelector(step.target)
                : step.target;
            if (el && typeof el.scrollIntoView === 'function') {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    };

    // ── Navigation ───────────────────────────────────────────────────

    const goNext = () => {
        if (currentIdx < steps.length - 1) {
            currentIdx++;
            card.classList.remove('is-active');
            setTimeout(render, 200);
        } else {
            finish(true);
        }
    };

    const goPrev = () => {
        if (currentIdx > 0) {
            currentIdx--;
            card.classList.remove('is-active');
            setTimeout(render, 200);
        }
    };

    const skip = () => {
        finish(false);
    };

    const finish = async (completed) => {
        // Remove tutorial UI
        card.classList.remove('is-active');
        spotlight.classList.remove('is-active');
        overlay.classList.remove('is-active');

        setTimeout(() => {
            card.remove();
            spotlight.remove();
            overlay.remove();
        }, 350);

        // Track completion
        if (completed) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                await fetch(TUTORIAL_COMPLETE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
            } catch {
                // Silently fail — tutorial still shows as completed locally
            }
        }

        if (onComplete) onComplete(completed);
        activeTutorial = null;
    };

    // ── Event delegation on the card ─────────────────────────────────

    card.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        if (action === 'next') goNext();
        else if (action === 'prev') goPrev();
        else if (action === 'skip') skip();
    });

    // Close on Escape
    const onKey = (e) => {
        if (e.key === 'Escape') skip();
    };
    document.addEventListener('keydown', onKey);

    // ── Public API ───────────────────────────────────────────────────

    activeTutorial = {
        destroy() {
            document.removeEventListener('keydown', onKey);
            card.remove();
            spotlight.remove();
            overlay.remove();
            activeTutorial = null;
        },
        goTo(idx) {
            currentIdx = Math.max(0, Math.min(idx, steps.length - 1));
            card.classList.remove('is-active');
            setTimeout(render, 200);
        },
    };

    // ── Start ────────────────────────────────────────────────────────

    if (autoStart !== false) {
        render();
    }

    return activeTutorial;
}

// ── Step Builders ────────────────────────────────────────────────────

export function welcomeStep(options = {}) {
    const termsUrl = options.termsUrl || '/terms';
    return {
        welcome: true,
        stepLabel: 'Welcome',
        title: options.title || 'Welcome to PR-AI 👋',
        subtitle: options.subtitle || 'Your AI-powered pull request auditing assistant',
        text: options.text || 'This short walkthrough will introduce the core features of the platform and show you how to navigate the workspace.',
        nextLabel: options.nextLabel || 'Start Tour →',
        extraHtml: `
            <a href="${termsUrl}" class="tutorial-terms-link" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Terms of Service &amp; Privacy Policy
            </a>
        `,
    };
}

export function step(title, target, text, options = {}) {
    return {
        title,
        target,
        text,
        placement: options.placement || 'bottom',
        padding: options.padding || 8,
        nextLabel: options.nextLabel || null,
    };
}
