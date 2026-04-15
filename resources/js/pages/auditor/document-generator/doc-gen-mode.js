import { createChatStatus } from '../chat-status';
import {
    resetDocGenState,
    setDocGenActive,
} from './doc-gen-store';

let isActive = false;

export function isDocGenModeEnabled() {
    return isActive;
}

export function initDocGenMode() {
    const chipWrap = document.getElementById('doc-gen-chip-wrap');
    if (!chipWrap) return;

    const showStatus = (msg) => {
        const responseArea = document.getElementById('ai-response-area');
        if (!responseArea) return;
        
        const status = createChatStatus({ container: responseArea, anchorNode: null });
        status.markSuccess(msg);
        status.remove(3000);
    };

    const deactivate = () => {
        isActive = false;
        chipWrap.classList.add('is-hidden');
        resetDocGenState();
        showStatus('DocGen Mode Disabled');
    };

    document.addEventListener('auditor:doc-gen-activated', () => {
        isActive = true;
        chipWrap.classList.remove('is-hidden');
        resetDocGenState({ keepActive: true });
        setDocGenActive(true);
        showStatus('DocGen Mode Enabled');
        document.querySelector('.chat-container')?.classList.add('is-active');
    });

    document.addEventListener('auditor:doc-gen-deactivated', deactivate);

    const closeBtn = document.getElementById('doc-gen-close-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            document.dispatchEvent(new CustomEvent('auditor:doc-gen-deactivated'));
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
