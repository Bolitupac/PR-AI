import { getDocGenState } from './doc-gen-store.js';

export function initDocGenQuestions() {
    const container = document.getElementById('doc-gen-questions');
    if (!container) return;

    const sync = () => {
        const state = getDocGenState();
        const questions = Array.isArray(state.questions) ? state.questions : [];

        container.innerHTML = '';
        container.classList.toggle('is-visible', questions.length > 0);

        questions.forEach((item) => {
            const card = document.createElement('section');
            card.className = 'doc-gen-question-card';

            const title = document.createElement('h4');
            title.className = 'doc-gen-question-title';
            title.textContent = item.question;
            card.appendChild(title);

            const options = document.createElement('div');
            options.className = 'doc-gen-question-options';

            item.options.forEach((option) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'doc-gen-question-option';
                button.textContent = option;
                button.addEventListener('click', () => {
                    document.dispatchEvent(new CustomEvent('auditor:doc-gen-answer-selected', {
                        detail: {
                            questionId: item.id,
                            answer: option,
                        },
                    }));
                });
                options.appendChild(button);
            });

            card.appendChild(options);
            container.appendChild(card);
        });
    };

    document.addEventListener('auditor:doc-gen-state-changed', sync);
    sync();
}
