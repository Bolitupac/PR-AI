import { createChatStatus } from './chat-status';
import { isDocGenModeEnabled } from './document-generator/doc-gen-mode';
import { parseDocGenMarkers } from './document-generator/doc-gen-markers';
import { renderChatMarkdown } from './chat-markdown';
import { renderMermaidIn } from './mermaid';
import { chatContextStore } from './chat-context-store';
import { extractInlineComments, stripInlineCommentsBlock } from './ai-inline-comments';
import { attachFollowUpSuggestions, clearFollowUpSuggestions, fetchFollowUpSuggestions } from './chat-followups';
import {
    resetDocGenState,
    setDocGenLastUserPrompt,
    setDocGenPreview,
    setDocGenQuestions,
    setDocGenReady,
} from './document-generator/doc-gen-store';
import { renderDocGenMessage } from './document-generator/doc-gen-renderer';

let chatApi = {
    sendTextToChat: async () => false,
    isBusy: () => false,
    getSelectedModel: () => '',
};

const STOP_ICON_HTML = `
    <svg class="stop-icon" viewBox="0 0 24 24" aria-hidden="true">
        <rect x="7" y="7" width="10" height="10" rx="2.4" fill="currentColor"></rect>
    </svg>
`;

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
    const chatContainer = document.querySelector('.chat-container');
    const modelSelect = document.getElementById('chat-model-select');
    const emptyState = document.getElementById('chat-empty-state');

    if (!responseArea || !promptInput || !sendButton || !chatContainer) return;
    chatContextStore.clear();
    const sendButtonDefaultHtml = sendButton.innerHTML;
    let activeRequest = null;
    let selectedModel = modelSelect?.value || '';
    let lastBusyState = false;
    const loginUrl = '/login';

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

    const syncComposerState = () => {
        const hasText = promptInput.value.trim().length > 0;
        const hasFocus = document.activeElement === promptInput;
        const isBusy = Boolean(activeRequest);
        chatContainer.classList.toggle('is-active', hasText || hasFocus || isBusy || isDocGenModeEnabled());
        if (isBusy !== lastBusyState) {
            document.dispatchEvent(new CustomEvent('auditor:chat-busy-changed', { detail: { busy: isBusy } }));
            lastBusyState = isBusy;
        }
    };

    const showBusyBlockedStatus = () => {
        const status = createChatStatus({ container: responseArea, anchorNode: null });
        status.markError('AI is still responding. Wait or press Stop.');
        status.remove(1050);
        chatContainer.classList.add('is-busy-blocked');
        setTimeout(() => chatContainer.classList.remove('is-busy-blocked'), 260);
    };

    const appendMessage = (text, role) => {
        const message = document.createElement('div');
        message.className = `msg ${role}`;
        if (role === 'ai') {
            message.innerHTML = renderChatMarkdown(stripInlineCommentsBlock(text));
            renderMermaidIn(message);
        } else {
            message.textContent = text;
        }
        responseArea.appendChild(message);
        responseArea.scrollTop = responseArea.scrollHeight;
        return message;
    };

    const showLoginRequiredMessage = (status, customMessage = null) => {
        const message = customMessage || 'You need to log in with GitHub before you can use PR ai. Please log in to continue.';
        status.markError('Login required.');
        appendMessage(message, 'ai');
    };

    const parseSseBlock = (blockText) => {
        const lines = String(blockText || '').split('\n');
        let eventName = 'message';
        const dataParts = [];

        for (const line of lines) {
            if (line.startsWith('event:')) {
                eventName = line.slice(6).trim() || 'message';
                continue;
            }
            if (line.startsWith('data:')) {
                dataParts.push(line.slice(5).trim());
            }
        }

        const payloadRaw = dataParts.join('\n');
        let payload = {};
        try {
            payload = payloadRaw ? JSON.parse(payloadRaw) : {};
        } catch {
            payload = {};
        }

        return { eventName, payload, payloadRaw };
    };

    const extractOpenAiToken = (payload) => {
        if (!payload || typeof payload !== 'object') return '';
        const token = String(payload?.choices?.[0]?.delta?.content ?? '');
        return token;
    };

    const wantsInlineCommentsRequest = (text) => /\b(comment|inline comment|leave comments|annotate|review the code|point out lines|auto edit|suggest edits|fix this|what should change|high risk lines|risky lines)\b/i.test(String(text || ''));

    const commitInlineComments = (replyText) => {
        const strippedReply = stripInlineCommentsBlock(replyText);
        const aiInlineComments = extractInlineComments(replyText);
        document.dispatchEvent(new CustomEvent('auditor:ai-comments-updated', {
            detail: { comments: aiInlineComments },
        }));
        return {
            comments: aiInlineComments,
            visibleReply: strippedReply,
        };
    };

    const fetchInlineCommentFallback = async (text, historyBefore) => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/api/ai/inline-comments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                message: text,
                model: selectedModel || undefined,
                history: historyBefore,
                docgen_mode_active: isDocGenModeEnabled(),
            }),
            credentials: 'same-origin',
        });

        if (res.status === 401 || (res.redirected && res.url && res.url.includes(loginUrl))) {
            throw new Error('LOGIN_REQUIRED');
        }

        if (!res.ok) {
            return [];
        }

        const data = await res.json().catch(() => ({}));
        return extractInlineComments(String(data?.reply || ''));
    };

    const fetchChatFallback = async (text, historyBefore) => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const chatUrl = isDocGenModeEnabled()
            ? '/api/ai/docgen/chat'
            : (sendButton.dataset.chatUrl || '/api/ai/chat');
        const res = await fetch(chatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                message: text,
                model: selectedModel || undefined,
                history: historyBefore,
                docgen_mode_active: isDocGenModeEnabled(),
            }),
            credentials: 'same-origin',
        });

        if (res.status === 401 || (res.redirected && res.url && res.url.includes(loginUrl))) {
            throw new Error('LOGIN_REQUIRED');
        }

        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data?.message || data?.reply || 'Chat request failed.');
        }

        return String(data?.reply || '');
    };

    let followUpRequestId = 0;

    const syncDocGenState = (replyText) => {
        const parsed = parseDocGenMarkers(replyText);
        if (parsed.previewMarkdown) {
            setDocGenPreview(parsed.previewMarkdown);
        }
        setDocGenQuestions(parsed.questions);
        setDocGenReady(parsed.ready);
        return parsed;
    };

    const renderAiFollowUps = async (messageNode, assistantText, userText) => {
        const requestId = ++followUpRequestId;
        const suggestions = await fetchFollowUpSuggestions({
            assistantText,
            userText,
            model: selectedModel,
            docGenModeActive: isDocGenModeEnabled(),
        }).catch(() => []);

        if (requestId !== followUpRequestId) return;
        if (!messageNode?.isConnected) return;

        attachFollowUpSuggestions({
            responseArea,
            messageNode,
            suggestions,
            onSelect: handleFollowUpSelection,
        });
    };

    const handleFollowUpSelection = (suggestion) => {
        sendTextInternal(suggestion, { source: 'suggestion' });
    };

    const sendTextInternal = async (
        rawText,
        {
            source = 'text',
            appendUserMessage = true,
            recordUserMessage = true,
        } = {}
    ) => {
        if (activeRequest) return false;
        const text = String(rawText ?? '').trim();
        if (!text) {
            syncComposerState();
            return false;
        }

        hideEmptyState();
        clearFollowUpSuggestions(responseArea);
        followUpRequestId += 1;
        if (isDocGenModeEnabled()) {
            setDocGenLastUserPrompt(text);
            resetDocGenState({ keepActive: true });
        }
        const previewAnchor = appendUserMessage ? appendMessage(text, 'user') : null;
        const historyBefore = chatContextStore.list();
        if (recordUserMessage) {
            chatContextStore.push('user', text);
        }
        const status = createChatStatus({ container: responseArea, anchorNode: previewAnchor });
        status.set('Validating message...');
        status.set('Message validated.');
        status.set('Preparing request...');

        if (source === 'text') {
            promptInput.value = '';
            resizeInput();
        }
        syncComposerState();

        const chatUrl = isDocGenModeEnabled()
            ? '/api/ai/docgen/chat-stream'
            : (sendButton.dataset.chatStreamUrl || '/api/ai/chat-stream');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const abortController = new AbortController();
        const requestState = { abortController, status, stopped: false, replyNode: null };
        activeRequest = requestState;
        sendButton.classList.add('is-stop');
        sendButton.setAttribute('aria-label', 'Stop');
        sendButton.innerHTML = STOP_ICON_HTML;
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
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message: text,
                    model: selectedModel || undefined,
                    history: historyBefore,
                    docgen_mode_active: isDocGenModeEnabled(),
                }),
                credentials: 'same-origin',
                signal: abortController.signal,
            });
            if (requestState.stopped) {
                status.markError('Response stopped.');
                return false;
            }

            if (res.status === 401 || (res.redirected && res.url && res.url.includes(loginUrl))) {
                showLoginRequiredMessage(status);
                return false;
            }

            clearTimeout(awaitingTimer);
            if (!switchedToAwaiting) {
                status.stopDots();
            }
            status.set('Backend responded.');
            if (!res.ok) {
                const fallbackText = await res.text().catch(() => '');
                let message = 'Chat request failed.';
                try {
                    const parsed = fallbackText ? JSON.parse(fallbackText) : null;
                    message = parsed?.message || message;
                } catch {
                    if (fallbackText) message = fallbackText;
                }
                status.markError('Request failed.');
                requestState.replyNode = appendMessage(message, 'ai');
                return false;
            }
            const reader = res.body?.getReader?.();
            if (!reader) {
                status.markError('Request failed.');
                appendMessage('Could not read AI stream.', 'ai');
                return false;
            }

            status.set('Rendering AI response...');
            requestState.replyNode = appendMessage('', 'ai');
            const decoder = new TextDecoder('utf-8');
            let fullReply = '';
            let buffer = '';
            let lastMermaidRender = 0;

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                if (requestState.stopped) {
                    break;
                }

                buffer += decoder.decode(value, { stream: true });
                let splitPos = buffer.search(/\r?\n\r?\n/);
                while (splitPos !== -1) {
                    const block = buffer.slice(0, splitPos);
                    const sepMatch = buffer.match(/\r?\n\r?\n/);
                    const sepLen = sepMatch ? sepMatch[0].length : 2;
                    buffer = buffer.slice(splitPos + sepLen);
                    const { eventName, payload, payloadRaw } = parseSseBlock(block);
                    if (payloadRaw === '[DONE]') {
                        break;
                    }

                    if (eventName === 'message' || eventName === 'token') {
                        const token = extractOpenAiToken(payload) || String(payload?.text ?? '');
                        if (token !== '') {
                            fullReply += token;
                            const parsed = syncDocGenState(fullReply);
                            if (requestState.replyNode) {
                                try {
                                    if (parsed.previewMarkdown || parsed.questions.length > 0) {
                                        const now = Date.now();
                                        if (parsed.previewStreaming || now - lastMermaidRender > 160) {
                                            await renderDocGenMessage(requestState.replyNode, parsed, responseArea);
                                            lastMermaidRender = now;
                                        }
                                    } else {
                                        requestState.replyNode.innerHTML = renderChatMarkdown(stripInlineCommentsBlock(parsed.visibleText));
                                        const now = Date.now();
                                        if (parsed.visibleText.includes('```mermaid') && (now - lastMermaidRender > 800)) {
                                            renderMermaidIn(requestState.replyNode);
                                            lastMermaidRender = now;
                                        }
                                    }
                                } catch (renderError) {
                                    console.error('DocGen stream render error:', renderError);
                                }
                                responseArea.scrollTop = responseArea.scrollHeight;
                            }
                        }
                    } else if (eventName === 'error') {
                        status.markError('Request failed.');
                        if (requestState.replyNode) {
                            requestState.replyNode.innerHTML = renderChatMarkdown(String(payload?.message || 'Chat request failed.'));
                            renderMermaidIn(requestState.replyNode);
                        }
                        return false;
                    } else if (eventName === 'done') {
                        if (typeof payload?.reply === 'string' && payload.reply.trim() !== '') {
                            fullReply = payload.reply;
                        }
                        break;
                    }

                    splitPos = buffer.search(/\r?\n\r?\n/);
                }
            }

            if (requestState.stopped) {
                status.markError('Response stopped.');
                return false;
            }

            if (fullReply.trim() === '') {
                fullReply = 'No response from AI.';
                if (requestState.replyNode) {
                    requestState.replyNode.innerHTML = renderChatMarkdown(fullReply);
                }
            }

            const docGenPayload = syncDocGenState(fullReply);

            const wantsInlineComments = wantsInlineCommentsRequest(text);
            status.set('Stripping inline comment format from chat response...');
            let { comments: inlineComments, visibleReply } = commitInlineComments(docGenPayload.visibleText);
            status.set('Converting inline comment data...');

            if (wantsInlineComments && inlineComments.length === 0) {
                status.set('No inline comments found in chat response.');
                status.set('Requesting structured inline comments...');
                inlineComments = await fetchInlineCommentFallback(text, historyBefore).catch(() => []);
                status.set('Converting fallback comment data...');
                document.dispatchEvent(new CustomEvent('auditor:ai-comments-updated', {
                    detail: { comments: inlineComments },
                }));
            }

            if (wantsInlineComments && inlineComments.length === 0) {
                status.markError('No renderable inline comments were produced.');
                if (requestState.replyNode) {
                    requestState.replyNode.innerHTML = renderChatMarkdown(visibleReply);
                    renderMermaidIn(requestState.replyNode);
                    await renderAiFollowUps(requestState.replyNode, visibleReply, text);
                }
                chatContextStore.push('assistant', visibleReply);
                return true;
            }

            if (requestState.replyNode) {
                status.set('Rendering chat response...');
                if (docGenPayload.previewMarkdown || docGenPayload.questions.length > 0) {
                    await renderDocGenMessage(requestState.replyNode, docGenPayload, responseArea);
                } else {
                    requestState.replyNode.innerHTML = renderChatMarkdown(visibleReply);
                    renderMermaidIn(requestState.replyNode);
                }
                await renderAiFollowUps(requestState.replyNode, visibleReply, text);
            }

            if (inlineComments.length > 0) {
                status.set(`Rendering ${inlineComments.length} inline comment${inlineComments.length === 1 ? '' : 's'} in diff viewer...`);
            }

            chatContextStore.push('assistant', visibleReply);
            status.markSuccess(inlineComments.length > 0 ? 'Response and inline comments rendered.' : 'Response rendered.');
            status.remove(450);
            return true;
        } catch (error) {
            clearTimeout(awaitingTimer);
            if (error?.name === 'AbortError' || requestState.stopped) {
                requestState.replyNode?.remove();
                status.markError('Response stopped.');
            } else if (error?.message === 'LOGIN_REQUIRED') {
                showLoginRequiredMessage(status);
            } else {
                try {
                    status.set('Streaming failed. Retrying without streaming...');
                    const fallbackReply = await fetchChatFallback(text, historyBefore);
                    const parsed = syncDocGenState(fallbackReply);
                    const { comments: inlineComments, visibleReply } = commitInlineComments(parsed.visibleText);
                    if (requestState.replyNode) {
                        if (parsed.previewMarkdown || parsed.questions.length > 0) {
                            await renderDocGenMessage(requestState.replyNode, parsed, responseArea);
                        } else {
                            requestState.replyNode.innerHTML = renderChatMarkdown(visibleReply || 'No response from AI.');
                            renderMermaidIn(requestState.replyNode);
                        }
                    } else {
                        requestState.replyNode = appendMessage(visibleReply || 'No response from AI.', 'ai');
                    }
                    await renderAiFollowUps(requestState.replyNode, visibleReply || 'No response from AI.', text);
                    document.dispatchEvent(new CustomEvent('auditor:ai-comments-updated', {
                        detail: { comments: inlineComments },
                    }));
                    chatContextStore.push('assistant', visibleReply || 'No response from AI.');
                    status.markSuccess('Response rendered via fallback.');
                    status.remove(700);
                    return true;
                } catch (fallbackError) {
                    if (fallbackError?.message === 'LOGIN_REQUIRED') {
                        showLoginRequiredMessage(status);
                    } else {
                        status.markError(`Request failed: ${fallbackError?.message || error?.message || 'Unknown error'}`);
                        appendMessage(String(fallbackError?.message || 'Could not reach AI service.'), 'ai');
                    }
                }
            }
            return false;
        } finally {
            activeRequest = null;
            sendButton.classList.remove('is-stop');
            sendButton.setAttribute('aria-label', 'Send');
            sendButton.innerHTML = sendButtonDefaultHtml;
            syncComposerState();
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
            syncComposerState();
            return;
        }
        sendTextInternal(promptInput.value, { source: 'text' });
    });
    promptInput.addEventListener('input', () => {
        resizeInput();
        syncComposerState();
    });
    promptInput.addEventListener('focus', syncComposerState);
    promptInput.addEventListener('blur', () => {
        setTimeout(syncComposerState, 0);
    });
    modelSelect?.addEventListener('change', function () {
        selectedModel = modelSelect.value;
        const status = createChatStatus({ container: responseArea, anchorNode: null });
        status.markSuccess(`Switched to ${selectedModel}.`);
        status.remove(700);
    });
    promptInput.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;
        if (activeRequest) {
            event.preventDefault();
            showBusyBlockedStatus();
            return;
        }
        if (event.ctrlKey || event.metaKey || event.shiftKey) {
            return;
        }
        event.preventDefault();
        sendTextInternal(promptInput.value, { source: 'text' });
    });

    resizeInput();
    syncComposerState();

    document.addEventListener('auditor:doc-gen-activated', syncComposerState);
    document.addEventListener('auditor:doc-gen-deactivated', syncComposerState);
    document.addEventListener('auditor:doc-gen-answer-selected', (event) => {
        const answer = String(event?.detail?.answer || '').trim();
        if (!answer || activeRequest) return;
        const status = createChatStatus({ container: responseArea, anchorNode: null });
        status.set('User answered question.');
        status.remove(900);
        sendTextInternal(answer, {
            source: 'docgen-question',
            appendUserMessage: false,
            recordUserMessage: true,
        });
    });
}
