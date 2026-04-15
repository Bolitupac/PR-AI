const initialState = () => ({
    active: false,
    previewMarkdown: '',
    questions: [],
    ready: false,
    autoTriggerPending: false,
    lastUserPrompt: '',
});

let state = initialState();

function emitChange() {
    document.dispatchEvent(new CustomEvent('auditor:doc-gen-state-changed', {
        detail: getDocGenState(),
    }));
}

export function getDocGenState() {
    return {
        ...state,
        questions: Array.isArray(state.questions) ? [...state.questions] : [],
    };
}

export function resetDocGenState({ keepActive = false } = {}) {
    state = {
        ...initialState(),
        active: keepActive ? state.active : false,
    };
    emitChange();
}

export function setDocGenActive(active) {
    state = {
        ...state,
        active: Boolean(active),
    };
    emitChange();
}

export function setDocGenPreview(markdown) {
    state = {
        ...state,
        previewMarkdown: String(markdown || '').trim(),
    };
    emitChange();
}

export function setDocGenQuestions(questions) {
    state = {
        ...state,
        questions: Array.isArray(questions) ? questions : [],
    };
    emitChange();
}

export function setDocGenReady(ready) {
    state = {
        ...state,
        ready: Boolean(ready),
    };
    emitChange();
}

export function setDocGenAutoTriggerPending(pending) {
    state = {
        ...state,
        autoTriggerPending: Boolean(pending),
    };
    emitChange();
}

export function setDocGenLastUserPrompt(prompt) {
    state = {
        ...state,
        lastUserPrompt: String(prompt || ''),
    };
    emitChange();
}
