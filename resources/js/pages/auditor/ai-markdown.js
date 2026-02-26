// Escapes HTML to keep user/model text safe before formatting.
function escapeHtml(input) {
    return input
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

// Renders a small markdown subset used by AI response fields.
export function renderMarkdown(markdownText) {
    if (!markdownText) return '';

    const escaped = escapeHtml(String(markdownText));
    const lines = escaped.split('\n');
    const html = [];
    let inList = false;

    const closeList = () => {
        if (inList) {
            html.push('</ul>');
            inList = false;
        }
    };

    lines.forEach((line) => {
        const listMatch = line.match(/^\s*[-*]\s+(.+)$/);
        if (listMatch) {
            if (!inList) {
                html.push('<ul>');
                inList = true;
            }
            html.push(`<li>${formatInline(listMatch[1])}</li>`);
            return;
        }

        closeList();

        if (line.trim() === '') {
            html.push('<br>');
            return;
        }

        html.push(`<p>${formatInline(line)}</p>`);
    });

    closeList();
    return html.join('');
}

// Applies inline markdown for bold and code.
function formatInline(text) {
    return text
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
}

