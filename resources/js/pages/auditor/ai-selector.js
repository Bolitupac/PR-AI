import { chatContextStore } from './chat-context-store';

export function initAiSelectors() {
    const providerSelect = document.getElementById('chat-provider-select');
    const providerTrigger = document.getElementById('provider-trigger');
    const modelSelect = document.getElementById('chat-model-select');
    const modelTrigger = document.getElementById('model-trigger');
    const modelChoicesContainer = document.getElementById('model-choices-container');

    if (!providerSelect || !providerTrigger || !modelSelect || !modelTrigger || !modelChoicesContainer) return;

    // Helper to rebuild model options in custom hover menu
    const rebuildCustomModelMenu = () => {
        modelTrigger.textContent = modelSelect.value || 'Select Model';
        modelChoicesContainer.innerHTML = '';
        Array.from(modelSelect.options).forEach(option => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'import-hover-item model-choice-item';
            if (option.selected) {
                btn.classList.add('is-active');
            }
            btn.dataset.value = option.value;

            const label = document.createElement('span');
            label.className = 'import-hover-label';
            label.textContent = option.textContent;
            btn.appendChild(label);

            btn.addEventListener('click', () => {
                modelSelect.value = option.value;
                modelSelect.dispatchEvent(new Event('change'));
                rebuildCustomModelMenu();
            });

            modelChoicesContainer.appendChild(btn);
        });
    };

    // Helper to sync provider selector
    const initCustomProviderMenu = () => {
        providerTrigger.textContent = providerSelect.options[providerSelect.selectedIndex]?.textContent || 'Select Provider';

        document.querySelectorAll('.provider-choice-item').forEach(btn => {
            btn.addEventListener('click', () => {
                const val = btn.dataset.value;
                providerSelect.value = val;
                providerSelect.dispatchEvent(new Event('change'));
                providerTrigger.textContent = btn.querySelector('.import-hover-label').textContent;
            });
        });

        providerSelect.addEventListener('change', () => {
            providerTrigger.textContent = providerSelect.options[providerSelect.selectedIndex]?.textContent || 'Select Provider';
            rebuildCustomModelMenu();
        });
    };

    // Listen to changes on modelSelect
    modelSelect.addEventListener('change', () => {
        modelTrigger.textContent = modelSelect.value || 'Select Model';
        // highlight active item
        modelChoicesContainer.querySelectorAll('.model-choice-item').forEach(btn => {
            if (btn.dataset.value === modelSelect.value) {
                btn.classList.add('is-active');
            } else {
                btn.classList.remove('is-active');
            }
        });
    });

    initCustomProviderMenu();
    rebuildCustomModelMenu();
}
