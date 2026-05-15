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

    // Initial state: save button is disabled until changes are made
    saveBtn.disabled = true;
    saveBtn.style.opacity = '0.5';

    const enableSaveBtn = () => {
        saveBtn.disabled = false;
        saveBtn.style.opacity = '1';
        setState(state, 'Unsaved changes', '');
    };

    [personality, verbosity, tone].forEach(el => el.addEventListener('change', enableSaveBtn));
    customPrompt.addEventListener('input', enableSaveBtn);

    const load = async () => {
        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) return;

            const res = await fetch('/profile/ai-preferences', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Failed to load preferences');
            const data = await res.json();
            
            if (data.preferences) {
                const parsed = data.preferences;
                if (parsed.personality) personality.value = parsed.personality;
                if (parsed.verbosity) verbosity.value = parsed.verbosity;
                if (parsed.tone) tone.value = parsed.tone;
                if (typeof parsed.custom_prompt === 'string') customPrompt.value = parsed.custom_prompt;
            }
        } catch {
            setState(state, 'Could not load saved AI preferences.', 'is-error');
        }
    };

    saveBtn.addEventListener('click', async () => {
        const payload = {
            personality: personality.value,
            verbosity: verbosity.value,
            tone: tone.value,
            custom_prompt: customPrompt.value.trim(),
        };

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            setState(state, 'Saving...', '');
            saveBtn.disabled = true;

            const res = await fetch('/profile/ai-preferences', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : ''
                },
                body: JSON.stringify(payload)
            });

            if (!res.ok) throw new Error('Save failed');
            const data = await res.json();
            
            document.dispatchEvent(new CustomEvent('auditor:ai-preferences-updated', { detail: data.preferences }));
            setState(state, 'AI preferences saved.', 'is-ok');
            saveBtn.disabled = true;
            saveBtn.style.opacity = '0.5';
        } catch (e) {
            setState(state, 'Error saving preferences.', 'is-error');
            saveBtn.disabled = false;
            saveBtn.style.opacity = '1';
        }
    });

    load();
}
