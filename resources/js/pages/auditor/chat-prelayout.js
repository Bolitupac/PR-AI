// Keeps composer centered below empty state before first prompt,
// then restores default bottom placement after chat starts.
export function initChatPrelayout() {
    const panel = document.querySelector('.ai-panel');
    const responseArea = document.getElementById('ai-response-area');
    const emptyState = document.getElementById('chat-empty-state');
    const chatContainer = document.querySelector('.chat-container');

    if (!panel || !responseArea || !emptyState || !chatContainer) return;

    const originalParent = chatContainer.parentNode;
    const originalNextSibling = chatContainer.nextSibling;
    let hasEnteredPrechat = false;

    const animateRelocationToDefault = (moveFn) => {
        const first = chatContainer.getBoundingClientRect();
        moveFn();
        const last = chatContainer.getBoundingClientRect();

        // Keep the horizontal position stable and only animate the downward settle.
        const dx = 0;
        const dy = first.top - last.top;

        if (Math.abs(dx) < 1 && Math.abs(dy) < 1) return;

        chatContainer.animate(
            [
                { transform: `translate(${dx}px, ${dy}px)` },
                { transform: 'translate(0, 0)' },
            ],
            {
                duration: 420,
                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
            }
        );
    };

    const moveToDefaultSlot = () => {
        if (!originalParent) return;
        if (originalNextSibling && originalNextSibling.parentNode === originalParent) {
            originalParent.insertBefore(chatContainer, originalNextSibling);
            return;
        }
        originalParent.appendChild(chatContainer);
    };

    const syncLayout = () => {
        const isPrechat = !emptyState.classList.contains('is-hidden');
        panel.classList.toggle('is-prechat', isPrechat);

        if (isPrechat) {
            if (chatContainer.previousElementSibling !== emptyState || chatContainer.parentNode !== responseArea) {
                emptyState.insertAdjacentElement('afterend', chatContainer);
            }
            hasEnteredPrechat = true;
            return;
        }

        if (chatContainer.parentNode !== originalParent) {
            if (!hasEnteredPrechat) {
                moveToDefaultSlot();
            } else {
                animateRelocationToDefault(moveToDefaultSlot);
            }
        }
    };

    const observer = new MutationObserver(syncLayout);
    observer.observe(emptyState, { attributes: true, attributeFilter: ['class'] });

    document.addEventListener('auditor:chat-reset-layout', syncLayout);
    syncLayout();
}
