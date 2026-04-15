import { renderChatMarkdown } from '../chat-markdown';
import { renderMermaidIn } from '../mermaid';
import { exportDocGenDocument } from './doc-gen-export';

function createExportButtons(formats) {
    const allowed = Array.isArray(formats?.allowed) && formats.allowed.length > 0
        ? formats.allowed
        : [String(formats?.default || 'pdf').toLowerCase()];
    const defaultFormat = String(formats?.default || allowed[0] || 'pdf').toLowerCase();

    return allowed.map((format) => {
        const normalized = String(format || '').toLowerCase();
        const isDefault = normalized === defaultFormat;
        return `<button
            type="button"
            class="doc-gen-export-link${isDefault ? ' is-default' : ''}"
            data-doc-gen-export="${escapeHtml(normalized)}"
            data-doc-gen-title="document"
        >Download document.${escapeHtml(normalized)}</button>`;
    }).join('');
}

function createQuestionCards(questions) {
    if (!Array.isArray(questions) || questions.length === 0) return '';

    return `
        <div class="doc-gen-inline-questions">
            ${questions.map((item) => `
                <section class="doc-gen-question-card">
                    <h4 class="doc-gen-question-title">${escapeHtml(item.question)}</h4>
                    <div class="doc-gen-question-options">
                        ${item.options.map((option) => `
                            <button
                                type="button"
                                class="doc-gen-question-option"
                                data-doc-gen-answer="${escapeHtml(option)}"
                                data-doc-gen-question-id="${escapeHtml(item.id)}"
                            >${escapeHtml(option)}</button>
                        `).join('')}
                    </div>
                </section>
            `).join('')}
        </div>
    `;
}

function escapeHtml(input) {
    return String(input ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

export async function renderDocGenMessage(messageNode, parsed, responseArea) {
    if (!messageNode || !parsed) return;

    const visibleHtml = parsed.visibleText
        ? `<div class="doc-gen-visible-copy">${renderChatMarkdown(parsed.visibleText)}</div>`
        : '';
    const hasPreview = Boolean(parsed.previewMarkdown);
    const docCard = hasPreview ? `
        <section class="doc-gen-inline-card">
            <div class="doc-gen-inline-card__head">
                <div>
                    <div class="doc-gen-inline-card__kicker">Generated Document</div>
                    <h4 class="doc-gen-inline-card__title">Structured Output</h4>
                </div>
                ${parsed.ready ? '<span class="doc-gen-inline-card__status">Ready to download</span>' : '<span class="doc-gen-inline-card__status is-drafting">Streaming draft</span>'}
            </div>
            <div class="doc-gen-inline-card__body msg ai">${renderChatMarkdown(parsed.previewMarkdown)}</div>
            ${parsed.ready ? `<div class="doc-gen-export-row is-visible">${createExportButtons(parsed.formats)}</div>` : ''}
        </section>
    ` : '';

    messageNode.innerHTML = `
        <div class="doc-gen-message">
            ${visibleHtml}
            ${docCard}
            ${createQuestionCards(parsed.questions)}
        </div>
    `;

    const docBody = messageNode.querySelector('.doc-gen-inline-card__body');
    if (docBody) {
        await renderMermaidIn(docBody);
    }

    messageNode.querySelectorAll('[data-doc-gen-answer]').forEach((button) => {
        button.addEventListener('click', () => {
            document.dispatchEvent(new CustomEvent('auditor:doc-gen-answer-selected', {
                detail: {
                    questionId: button.getAttribute('data-doc-gen-question-id') || '',
                    answer: button.getAttribute('data-doc-gen-answer') || '',
                },
            }));
        });
    });

    messageNode.querySelectorAll('[data-doc-gen-export]').forEach((button) => {
        button.addEventListener('click', async () => {
            await exportDocGenDocument({
                format: button.getAttribute('data-doc-gen-export') || 'pdf',
                title: button.getAttribute('data-doc-gen-title') || 'docgen-report',
                markdown: parsed.previewMarkdown,
                responseArea,
            });
        });
    });
}
