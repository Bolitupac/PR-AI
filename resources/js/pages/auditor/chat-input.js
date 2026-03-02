import { createChatStatus } from './chat-status';
import { renderChatMarkdown } from './chat-markdown';

// Pushes user input into the chat area as a new message.
export function initChatInput() {
    const responseArea = document.getElementById('ai-response-area');
    const promptInput = document.getElementById('user-prompt');
    const sendButton = document.getElementById('send-btn');

    if (!responseArea || !promptInput || !sendButton) return;

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

        const chatUrl = sendButton.dataset.chatUrl || '/api/ai/chat';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
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
            });
            clearTimeout(awaitingTimer);
            if (!switchedToAwaiting) {
                status.stopDots();
            }
            status.set('Backend responded.');

            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                status.markError('Request failed.');
                appendMessage(data?.message || 'Chat request failed.', 'ai');
                return;
            }

            status.set('Rendering AI response...');
            appendMessage(data?.reply || 'No response from AI.', 'ai');
            status.markSuccess('Request sent.');
            status.remove(450);
        } catch (error) {
            clearTimeout(awaitingTimer);
            status.markError('Request failed.');
            appendMessage('Could not reach AI service.', 'ai');
        } finally {
            promptInput.focus();
        }
    };

    sendButton.addEventListener('click', sendMessage);
    promptInput.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        sendMessage();
    });
}
