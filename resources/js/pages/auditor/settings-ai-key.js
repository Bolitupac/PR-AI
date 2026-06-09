function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function setState(stateNode, message, tone = '') {
    if (!stateNode) return;
    stateNode.textContent = message;
    stateNode.classList.remove('is-ok', 'is-error');
    if (tone) stateNode.classList.add(tone);
}

function updateStatusBadge(badgeEl, isPersonal) {
    if (!badgeEl) return;
    if (isPersonal) {
        badgeEl.textContent = 'Personal key active';
        badgeEl.style.background = 'rgba(245,158,11,0.12)';
        badgeEl.style.color = '#b45309';
        badgeEl.style.borderColor = 'rgba(245,158,11,0.3)';
    } else {
        badgeEl.textContent = 'Developer key active';
        badgeEl.style.background = 'rgba(45,164,78,0.1)';
        badgeEl.style.color = '#1a7f37';
        badgeEl.style.borderColor = 'rgba(45,164,78,0.3)';
    }
}

function updateStatusText(textEl, isPersonal, masked) {
    if (!textEl) return;
    if (isPersonal && masked) {
        textEl.textContent = `Personal key: ${masked}`;
    } else if (isPersonal) {
        textEl.textContent = 'Personal key active';
    } else {
        textEl.textContent = 'Developer key active';
    }
}

async function requestJson(url, method = 'GET', body = null) {
    const headers = { Accept: 'application/json' };
    if (method !== 'GET') {
        headers['Content-Type'] = 'application/json';
        headers['X-CSRF-TOKEN'] = getCsrfToken();
    }
    const response = await fetch(url, {
        method, headers, credentials: 'same-origin',
        body: body ? JSON.stringify(body) : undefined,
    });
    const json = await response.json().catch(() => ({}));
    return { ok: response.ok, status: response.status, json };
}

function initProviderKeyBox(boxId, modeSelectId, inputId, saveBtnId, removeBtnId, stateId, keyRowId, badgeId, statusTextId, profileBadgeId) {
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
    const keyRow = document.getElementById(keyRowId);
    const badge = document.getElementById(badgeId);
    const statusText = document.getElementById(statusTextId);
    const profileBadge = document.getElementById(profileBadgeId);

    if (!statusUrl || !saveUrl || !removeUrl || !modeUrl || !modeSelect) return;

    const updateUi = (payload) => {
        const mode = payload?.mode === 'personal' ? 'personal' : 'developer';
        const hasKey = Boolean(payload?.has_personal_key);
        const masked = payload?.masked_key || '';

        modeSelect.value = mode;
        if (keyRow) keyRow.style.display = mode === 'personal' ? 'block' : 'none';
        if (input && saveBtn && removeBtn) {
            if (removeBtn) removeBtn.disabled = !hasKey;
        }

        updateStatusBadge(badge, mode === 'personal' && hasKey);
        updateStatusBadge(profileBadge, mode === 'personal' && hasKey);
        updateStatusText(statusText, mode === 'personal' && hasKey, masked);
    };

    const loadStatus = async () => {
        setState(state, 'Loading...');
        const result = await requestJson(statusUrl);
        if (!result.ok) {
            setState(state, 'Could not load key settings.', 'is-error');
            return;
        }
        updateUi(result.json);
        setState(state, 'Ready.', 'is-ok');
    };

    if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
            const apiKey = input?.value?.trim();
            if (!apiKey) {
                setState(state, 'Enter an API key first.', 'is-error');
                return;
            }
            setState(state, 'Saving...');
            const result = await requestJson(saveUrl, 'POST', { api_key: apiKey });
            if (!result.ok) {
                setState(state, result.json?.message || 'Failed to save key.', 'is-error');
                return;
            }
            if (input) input.value = '';
            updateUi(result.json);
            setState(state, result.json?.message || 'Key saved.', 'is-ok');
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', async () => {
            setState(state, 'Removing...');
            const result = await requestJson(removeUrl, 'DELETE');
            if (!result.ok) {
                setState(state, result.json?.message || 'Failed to remove key.', 'is-error');
                return;
            }
            updateUi(result.json);
            setState(state, result.json?.message || 'Key removed.', 'is-ok');
        });
    }

    modeSelect.addEventListener('change', async () => {
        setState(state, 'Switching...');
        const result = await requestJson(modeUrl, 'POST', { mode: modeSelect.value });
        if (!result.ok) {
            setState(state, result.json?.message || 'Failed to switch.', 'is-error');
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

export function initSettingsAiKey() {
    initProviderKeyBox(
        'settings-ai-key-box',
        'settings-ai-key-mode',
        'settings-api-input',
        'settings-api-save-btn',
        'settings-api-remove-btn',
        'settings-ai-key-state',
        'settings-openai-key-row',
        'settings-openai-status-badge',
        'settings-openai-status-text',
        'settings-profile-openai-badge'
    );
}

export function initSettingsDeepSeekKey() {
    initProviderKeyBox(
        'settings-deepseek-key-box',
        'settings-deepseek-key-mode',
        'settings-deepseek-api-input',
        'settings-deepseek-api-save-btn',
        'settings-deepseek-api-remove-btn',
        'settings-deepseek-key-state',
        'settings-deepseek-key-row',
        'settings-deepseek-status-badge',
        'settings-deepseek-status-text',
        'settings-profile-deepseek-badge'
    );
}
