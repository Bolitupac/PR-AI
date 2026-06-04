const MAX_HISTORY = 16;
const state = {
    conversationId: null,
    history: [],
};

function normalizeRole(role) {
    return role === 'assistant' ? 'assistant' : 'user';
}

function push(role, content) {
    const text = String(content ?? '').trim();
    if (!text) return;
    state.history.push({
        role: normalizeRole(role),
        content: text,
    });
    if (state.history.length > MAX_HISTORY) {
        state.history = state.history.slice(state.history.length - MAX_HISTORY);
    }
}

function list() {
    return state.history.slice();
}

function clear() {
    state.conversationId = null;
    state.history = [];
}

function setConversationId(id) {
    state.conversationId = id;
}

function getConversationId() {
    return state.conversationId;
}

export const chatContextStore = { push, list, clear, setConversationId, getConversationId };

