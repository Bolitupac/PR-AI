function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function setState(stateNode, message, tone = '') {
    if (!stateNode) return;
    stateNode.textContent = message;
    stateNode.classList.remove('is-ok', 'is-error');
    if (tone) stateNode.classList.add(tone);
}

async function requestJson(url, method = 'GET', body = null) {
    const headers = {
        Accept: 'application/json',
    };

    if (method !== 'GET') {
        headers['Content-Type'] = 'application/json';
        headers['X-CSRF-TOKEN'] = getCsrfToken();
    }

    const response = await fetch(url, {
        method,
        headers,
        credentials: 'same-origin',
        body: body ? JSON.stringify(body) : undefined,
    });

    const json = await response.json().catch(() => ({}));
    return { ok: response.ok, status: response.status, json };
}

function initKeyBox({
    boxId,
    modeSelectId,
    inputId,
    saveBtnId,
    removeBtnId,
    stateId,
    hintId,
}) {
    const box = document.getElementById(boxId);
    if (!box) return;

    const statusUrl = box.dataset.statusUrl;
    const saveUrl = box.dataset.saveUrl;
    const removeUrl = box.dataset.removeUrl;
    const modeUrl = box.dataset.modeUrl;

    const modeSelect = document.getElementById(modeSelectId);
    const input = document.getElementById(inputId);
    const saveBtn = document.getElementById(saveBtnId);
    const removeBtn = document.getElementById(removeBtnId);
    const state = document.getElementById(stateId);
    const hint = document.getElementById(hintId);

    if (!statusUrl || !saveUrl || !removeUrl || !modeUrl || !modeSelect || !input || !saveBtn || !removeBtn) return;

    const updateUi = (payload) => {
        const mode = payload?.mode === 'personal' ? 'personal' : 'system';
        const hasKey = Boolean(payload?.has_personal_key);
        const masked = payload?.masked_key || '';

        modeSelect.value = mode;
        removeBtn.disabled = !hasKey;
        hint.textContent = hasKey ? `Saved key: ${masked}` : 'No personal key saved yet.';
    };

    const loadStatus = async () => {
        setState(state, 'Loading key settings...');
        const result = await requestJson(statusUrl);
        if (!result.ok) {
            setState(state, 'Could not load key settings.', 'is-error');
            return;
        }
        updateUi(result.json);
        setState(state, 'Ready.', 'is-ok');
    };

    saveBtn.addEventListener('click', async () => {
        const apiKey = input.value.trim();
        if (!apiKey) {
            setState(state, 'Enter an API key first.', 'is-error');
            return;
        }

        setState(state, 'Validating and saving key...');
        const result = await requestJson(saveUrl, 'POST', { api_key: apiKey });
        if (!result.ok) {
            setState(state, result.json?.message || 'Failed to save key.', 'is-error');
            return;
        }

        input.value = '';
        updateUi(result.json);
        setState(state, result.json?.message || 'Key saved.', 'is-ok');
    });

    removeBtn.addEventListener('click', async () => {
        setState(state, 'Removing key...');
        const result = await requestJson(removeUrl, 'DELETE');
        if (!result.ok) {
            setState(state, result.json?.message || 'Failed to remove key.', 'is-error');
            return;
        }

        updateUi(result.json);
        setState(state, result.json?.message || 'Key removed.', 'is-ok');
    });

    modeSelect.addEventListener('change', async () => {
        setState(state, 'Switching key source...');
        const result = await requestJson(modeUrl, 'POST', { mode: modeSelect.value });
        if (!result.ok) {
            setState(state, result.json?.message || 'Failed to switch key source.', 'is-error');
            await loadStatus();
            return;
        }

        updateUi(result.json);
        setState(state, result.json?.message || 'Key source updated.', 'is-ok');
    });

    loadStatus().catch(() => {
        setState(state, 'Could not load key settings.', 'is-error');
    });
}

export function initProfileAiKey() {
    initKeyBox({
        boxId: 'profile-ai-key-box',
        modeSelectId: 'profile-ai-key-mode',
        inputId: 'profile-api-input',
        saveBtnId: 'profile-api-save-btn',
        removeBtnId: 'profile-api-remove-btn',
        stateId: 'profile-ai-key-state',
        hintId: 'profile-ai-key-hint',
    });

    initKeyBox({
        boxId: 'settings-profile-ai-key-box',
        modeSelectId: 'settings-profile-ai-key-mode',
        inputId: 'settings-profile-api-input',
        saveBtnId: 'settings-profile-api-save-btn',
        removeBtnId: 'settings-profile-api-remove-btn',
        stateId: 'settings-profile-ai-key-state',
        hintId: 'settings-profile-ai-key-hint',
    });
}
