function isNearBottom(node, threshold = 18) {
    return node.scrollTop + node.clientHeight >= node.scrollHeight - threshold;
}

// Adds a floating "scroll to latest" button for long chat threads.
export function initChatScrollBottomButton() {
    const responseArea = document.getElementById('ai-response-area');
    const aiPanel = document.querySelector('.ai-panel');
    const chatContainer = document.querySelector('.chat-container');
    if (!responseArea || !aiPanel || !chatContainer) return;

    const button = document.createElement('button');
    button.type = 'button';
    button.id = 'chat-scroll-bottom-btn';
    button.className = 'chat-scroll-bottom-btn';
    button.setAttribute('aria-label', 'Scroll to latest messages');
    button.innerHTML = `
        <span class="chat-scroll-bottom-gloss" aria-hidden="true"></span>
        <span class="chat-scroll-bottom-core" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <path d="M12 5.5v11.2M8.4 13.1 12 16.9 15.6 13.1" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
    `;
    aiPanel.appendChild(button);

    const syncAnchor = () => {
        const panelRect = aiPanel.getBoundingClientRect();
        const chatRect = chatContainer.getBoundingClientRect();
        const gapPx = 8;
        const bottom = Math.max(16, Math.round(panelRect.bottom - chatRect.top + gapPx));
        button.style.bottom = `${bottom}px`;
    };

    const syncVisibility = () => {
        const canScroll = responseArea.scrollHeight > responseArea.clientHeight + 20;
        const atBottom = isNearBottom(responseArea);
        button.classList.toggle('is-visible', canScroll && !atBottom);
        syncAnchor();
    };

    button.addEventListener('click', () => {
        responseArea.scrollTo({
            top: responseArea.scrollHeight,
            behavior: 'smooth',
        });
    });

    responseArea.addEventListener('scroll', syncVisibility, { passive: true });
    window.addEventListener('resize', syncVisibility);
    window.addEventListener('scroll', syncVisibility, { passive: true });

    if ('ResizeObserver' in window) {
        const ro = new ResizeObserver(syncVisibility);
        ro.observe(chatContainer);
        ro.observe(aiPanel);
    }

    const observer = new MutationObserver(syncVisibility);
    observer.observe(responseArea, {
        childList: true,
        subtree: true,
        characterData: true,
    });

    syncVisibility();
}
