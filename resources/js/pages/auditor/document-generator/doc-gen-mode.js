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

    const chatContainer = document.querySelector('.chat-container');
    const chatInputWrap = document.querySelector('.chat-input-wrap');
    const chatToolsRow = document.querySelector('.chat-tools-row');
    const sendBtn = document.getElementById('send-btn');
    
    if (chatContainer && chatInputWrap && chatToolsRow && sendBtn) {
        const observer = new MutationObserver(() => {
            if (chatContainer.classList.contains('is-active')) {
                if (chipWrap.parentElement !== chatToolsRow) {
                    chatToolsRow.appendChild(chipWrap);
                }
            } else {
                if (chipWrap.parentElement !== chatInputWrap) {
                    chatInputWrap.insertBefore(chipWrap, sendBtn);
                }
            }
        });
        observer.observe(chatContainer, { attributes: true, attributeFilter: ['class'] });
        
        if (!chatContainer.classList.contains('is-active')) {
            chatInputWrap.insertBefore(chipWrap, sendBtn);
        }
    }
}

