const SETTINGS_KEY = 'auditor-ai-preferences';

function setState(node, text, tone = '') {
    if (!node) return;
    node.textContent = text;
    node.classList.remove('is-ok', 'is-error');
    if (tone) node.classList.add(tone);
}

export function initSettingsAiPreferences() {
    const wrap = document.getElementById('settings-ai-preferences');
    if (!wrap) return;

    const personality = document.getElementById('ai-pref-personality');
    const verbosity = document.getElementById('ai-pref-verbosity');
    const tone = document.getElementById('ai-pref-tone');
    const customPrompt = document.getElementById('ai-pref-custom-prompt');
    const saveBtn = document.getElementById('ai-pref-save-btn');
    const state = document.getElementById('ai-pref-state');

    if (!personality || !verbosity || !tone || !customPrompt || !saveBtn || !state) return;

    const load = () => {
        const raw = localStorage.getItem(SETTINGS_KEY);
        if (!raw) return;

        try {
            const parsed = JSON.parse(raw);
            if (parsed?.personality) personality.value = parsed.personality;
            if (parsed?.verbosity) verbosity.value = parsed.verbosity;
            if (parsed?.tone) tone.value = parsed.tone;
            if (typeof parsed?.customPrompt === 'string') customPrompt.value = parsed.customPrompt;
            setState(state, 'Loaded saved AI preferences.', 'is-ok');
        } catch {
            setState(state, 'Could not load saved AI preferences.', 'is-error');
        }
    };

    saveBtn.addEventListener('click', () => {
        const payload = {
            personality: personality.value,
            verbosity: verbosity.value,
            tone: tone.value,
            customPrompt: customPrompt.value.trim(),
        };

        localStorage.setItem(SETTINGS_KEY, JSON.stringify(payload));
        document.dispatchEvent(new CustomEvent('auditor:ai-preferences-updated', { detail: payload }));
        setState(state, 'AI preferences saved.', 'is-ok');
    });

    load();
}
