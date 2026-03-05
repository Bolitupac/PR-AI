import { createChatStatus } from './chat-status';
import { renderChatMarkdown } from './chat-markdown';
import { chatContextStore } from './chat-context-store';

let chatApi = {
    sendTextToChat: async () => false,
    isBusy: () => false,
    getSelectedModel: () => '',
};

export function sendTextToChat(text, options = {}) {
    return chatApi.sendTextToChat(text, options);
}

export function isChatBusy() {
    return chatApi.isBusy();
}

export function getSelectedChatModel() {
    return chatApi.getSelectedModel();
}

// Pushes user input into the chat area as a new message.
export function initChatInput() {
    const responseArea = document.getElementById('ai-response-area');
    const promptInput = document.getElementById('user-prompt');
    const sendButton = document.getElementById('send-btn');
    const modelSelect = document.getElementById('chat-model-select');
    const emptyState = document.getElementById('chat-empty-state');

    if (!responseArea || !promptInput || !sendButton) return;
    const sendButtonDefaultHtml = sendButton.innerHTML;
    let activeRequest = null;
    let selectedModel = modelSelect?.value || '';

    const hideEmptyState = () => {
        if (!emptyState) return;
        emptyState.classList.add('is-hidden');
    };

    const resizeInput = () => {
        promptInput.style.height = 'auto';
        const next = Math.min(promptInput.scrollHeight, 180);
        promptInput.style.height = `${Math.max(next, 46)}px`;
        promptInput.style.overflowY = promptInput.scrollHeight > 180 ? 'auto' : 'hidden';
    };

    const appendMessage = (text, role) => {
        const message = document.createElement('div');
        message.className = `msg ${role}`;
        if (role === 'ai') {
            message.innerHTML = renderChatMarkdown(text);
        } else {
            message.textContent = text;
        }
        responseArea.appendChild(message);
        responseArea.scrollTop = responseArea.scrollHeight;
        return message;
    };

    const sendTextInternal = async (rawText, { source = 'text' } = {}) => {
        if (activeRequest) return false;
        const text = String(rawText ?? '').trim();
        if (!text) {
            const status = createChatStatus({ container: responseArea, anchorNode: null });
            status.set('Validating message...');
            status.markError('Message is empty.');
            return false;
        }

        hideEmptyState();
        const previewAnchor = appendMessage(text, 'user');
        const historyBefore = chatContextStore.list();
        chatContextStore.push('user', text);
        const status = createChatStatus({ container: responseArea, anchorNode: previewAnchor });
        status.set('Validating message...');
        status.set('Message validated.');
        status.set('Preparing request...');

        if (source === 'text') {
            promptInput.value = '';
            resizeInput();
        }

        const chatUrl = sendButton.dataset.chatUrl || '/api/ai/chat';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const abortController = new AbortController();
        const requestState = { abortController, status, stopped: false, replyNode: null };
        activeRequest = requestState;
        sendButton.classList.add('is-stop');
        sendButton.setAttribute('aria-label', 'Stop');
        sendButton.textContent = 'Stop';
        status.startDots('Sending request to backend');
        let switchedToAwaiting = false;
        const awaitingTimer = setTimeout(() => {
            switchedToAwaiting = true;
            status.startDots('Awaiting backend response');
        }, 550);

        try {
            const res = await fetch(chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message: text,
                    model: selectedModel || undefined,
                    history: historyBefore,
                }),
                signal: abortController.signal,
            });
            if (requestState.stopped) {
                status.markError('Response stopped.');
                return false;
            }
            clearTimeout(awaitingTimer);
            if (!switchedToAwaiting) {
                status.stopDots();
            }
            status.set('Backend responded.');

            const data = await res.json().catch(() => ({}));
            if (requestState.stopped) {
                status.markError('Response stopped.');
                return false;
            }
            if (!res.ok) {
                status.markError('Request failed.');
                if (!requestState.stopped) {
                    requestState.replyNode = appendMessage(data?.message || 'Chat request failed.', 'ai');
                }
                return false;
            }

            status.set('Rendering AI response...');
            if (requestState.stopped) {
                status.markError('Response stopped.');
                return false;
            }
            requestState.replyNode = appendMessage(data?.reply || 'No response from AI.', 'ai');
            chatContextStore.push('assistant', data?.reply || 'No response from AI.');
            status.markSuccess('Request sent.');
            status.remove(450);
            return true;
        } catch (error) {
            clearTimeout(awaitingTimer);
            if (error?.name === 'AbortError' || requestState.stopped) {
                requestState.replyNode?.remove();
                status.markError('Response stopped.');
            } else {
                status.markError('Request failed.');
                appendMessage('Could not reach AI service.', 'ai');
            }
            return false;
        } finally {
            activeRequest = null;
            sendButton.classList.remove('is-stop');
            sendButton.setAttribute('aria-label', 'Send');
            sendButton.innerHTML = sendButtonDefaultHtml;
            promptInput.focus();
        }
    };

    chatApi = {
        sendTextToChat: sendTextInternal,
        isBusy: () => Boolean(activeRequest),
        getSelectedModel: () => selectedModel,
    };

    sendButton.addEventListener('click', function () {
        if (activeRequest) {
            activeRequest.stopped = true;
            activeRequest.abortController.abort();
            activeRequest.replyNode?.remove();
            activeRequest.status.markError('Response stopped.');
            return;
        }
        sendTextInternal(promptInput.value, { source: 'text' });
    });
    promptInput.addEventListener('input', resizeInput);
    modelSelect?.addEventListener('change', function () {
        selectedModel = modelSelect.value;
        const status = createChatStatus({ container: responseArea, anchorNode: null });
        status.markSuccess(`Switched to ${selectedModel}.`);
        status.remove(700);
    });
    promptInput.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;
        if (activeRequest) return;
        if (event.ctrlKey || event.metaKey || event.shiftKey) {
            return;
        }
        event.preventDefault();
        sendTextInternal(promptInput.value, { source: 'text' });
    });

    resizeInput();
}
