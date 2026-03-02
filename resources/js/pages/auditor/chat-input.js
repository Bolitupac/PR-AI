import { createChatStatus } from './chat-status';
import { renderChatMarkdown } from './chat-markdown';

// Pushes user input into the chat area as a new message.
export function initChatInput() {
    const responseArea = document.getElementById('ai-response-area');
    const promptInput = document.getElementById('user-prompt');
    const sendButton = document.getElementById('send-btn');

    if (!responseArea || !promptInput || !sendButton) return;
    const sendButtonDefaultHtml = sendButton.innerHTML;
    let activeRequest = null;

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

    const sendMessage = async () => {
        if (activeRequest) return;
        const rawText = promptInput.value;
        const text = rawText.trim();
        if (!text) {
            const status = createChatStatus({ container: responseArea, anchorNode: null });
            status.set('Validating message...');
            status.markError('Message is empty.');
            return;
        }

        const previewAnchor = appendMessage(text, 'user');
        const status = createChatStatus({ container: responseArea, anchorNode: previewAnchor });
        status.set('Validating message...');
        status.set('Message validated.');
        status.set('Preparing request...');

        promptInput.value = '';
        resizeInput();

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
                body: JSON.stringify({ message: text }),
                signal: abortController.signal,
            });
            if (requestState.stopped) {
                status.markError('Response stopped.');
                return;
            }
            clearTimeout(awaitingTimer);
            if (!switchedToAwaiting) {
                status.stopDots();
            }
            status.set('Backend responded.');

            const data = await res.json().catch(() => ({}));
            if (requestState.stopped) {
                status.markError('Response stopped.');
                return;
            }
            if (!res.ok) {
                status.markError('Request failed.');
                if (!requestState.stopped) {
                    requestState.replyNode = appendMessage(data?.message || 'Chat request failed.', 'ai');
                }
                return;
            }

            status.set('Rendering AI response...');
            if (requestState.stopped) {
                status.markError('Response stopped.');
                return;
            }
            requestState.replyNode = appendMessage(data?.reply || 'No response from AI.', 'ai');
            status.markSuccess('Request sent.');
            status.remove(450);
        } catch (error) {
            clearTimeout(awaitingTimer);
            if (error?.name === 'AbortError' || requestState.stopped) {
                requestState.replyNode?.remove();
                status.markError('Response stopped.');
            } else {
                status.markError('Request failed.');
                appendMessage('Could not reach AI service.', 'ai');
            }
        } finally {
            activeRequest = null;
            sendButton.classList.remove('is-stop');
            sendButton.setAttribute('aria-label', 'Send');
            sendButton.innerHTML = sendButtonDefaultHtml;
            promptInput.focus();
        }
    };

    sendButton.addEventListener('click', function () {
        if (activeRequest) {
            activeRequest.stopped = true;
            activeRequest.abortController.abort();
            activeRequest.replyNode?.remove();
            activeRequest.status.markError('Response stopped.');
            return;
        }
        sendMessage();
    });
    promptInput.addEventListener('input', resizeInput);
    promptInput.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;
        if (activeRequest) return;
        if (event.ctrlKey || event.metaKey || event.shiftKey) {
            return;
        }
        event.preventDefault();
        sendMessage();
    });

    resizeInput();
}
