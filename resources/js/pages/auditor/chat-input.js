// Pushes user input into the chat area as a new message.
export function initChatInput() {
    const responseArea = document.getElementById('ai-response-area');
    const promptInput = document.getElementById('user-prompt');
    const sendButton = document.getElementById('send-btn');

    if (!responseArea || !promptInput || !sendButton) return;

    const appendMessage = (text, role) => {
        const message = document.createElement('div');
        message.className = `msg ${role}`;
        message.textContent = text;
        responseArea.appendChild(message);
        responseArea.scrollTop = responseArea.scrollHeight;
    };

    const sendMessage = async () => {
        const text = promptInput.value.trim();
        if (!text) return;
        appendMessage(text, 'user');

        promptInput.value = '';
        sendButton.disabled = true;

        const chatUrl = sendButton.dataset.chatUrl || '/api/ai/chat';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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

            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                appendMessage(data?.message || 'Chat request failed.', 'ai');
                return;
            }

            appendMessage(data?.reply || 'No response from AI.', 'ai');
        } catch (error) {
            appendMessage('Could not reach AI service.', 'ai');
        } finally {
            sendButton.disabled = false;
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
