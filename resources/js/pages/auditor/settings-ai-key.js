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

export function initSettingsAiKey() {
    const box = document.getElementById('settings-ai-key-box');
    if (!box) return;

    const statusUrl = box.dataset.statusUrl;
    const saveUrl = box.dataset.saveUrl;
    const removeUrl = box.dataset.removeUrl;
    const modeUrl = box.dataset.modeUrl;

    const modeSelect = document.getElementById('settings-ai-key-mode');
    const input = document.getElementById('settings-api-input');
    const saveBtn = document.getElementById('settings-api-save-btn');
    const removeBtn = document.getElementById('settings-api-remove-btn');
    const state = document.getElementById('settings-ai-key-state');
    const hint = document.getElementById('settings-ai-key-hint');

    if (!statusUrl || !saveUrl || !removeUrl || !modeUrl || !modeSelect || !input || !saveBtn || !removeBtn) {
        return;
    }

    const updateUi = (payload) => {
        const mode = payload?.mode === 'personal' ? 'personal' : 'developer';
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

export function initSettingsDeepSeekKey() {
    const box = document.getElementById('settings-deepseek-key-box');
    if (!box) return;

    const statusUrl = box.dataset.statusUrl;
    const saveUrl = box.dataset.saveUrl;
    const removeUrl = box.dataset.removeUrl;
    const modeUrl = box.dataset.modeUrl;

    const modeSelect = document.getElementById('settings-deepseek-key-mode');
    const input = document.getElementById('settings-deepseek-api-input');
    const saveBtn = document.getElementById('settings-deepseek-api-save-btn');
    const removeBtn = document.getElementById('settings-deepseek-api-remove-btn');
    const state = document.getElementById('settings-deepseek-key-state');
    const hint = document.getElementById('settings-deepseek-key-hint');

    if (!statusUrl || !saveUrl || !removeUrl || !modeUrl || !modeSelect || !input || !saveBtn || !removeBtn) {
        return;
    }

    const updateUi = (payload) => {
        const mode = payload?.mode === 'personal' ? 'personal' : 'developer';
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
