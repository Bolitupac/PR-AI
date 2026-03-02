// Renders a safe markdown subset for AI replies.
export function renderChatMarkdown(markdownText) {
    const text = String(markdownText ?? '');
    const escaped = escapeHtml(text);
    const lines = escaped.replace(/\r\n?/g, '\n').split('\n');

    const html = [];
    let inUl = false;
    let inOl = false;

    const closeLists = () => {
        if (inUl) {
            html.push('</ul>');
            inUl = false;
        }
        if (inOl) {
            html.push('</ol>');
            inOl = false;
        }
    };

    for (const rawLine of lines) {
        const line = rawLine.trim();

        if (line === '') {
            closeLists();
            html.push('<p></p>');
            continue;
        }

        const ul = line.match(/^[-*]\s+(.+)$/);
        if (ul) {
            if (!inUl) {
                closeLists();
                html.push('<ul>');
                inUl = true;
            }
            html.push(`<li>${formatInline(ul[1])}</li>`);
            continue;
        }

        const ol = line.match(/^\d+\.\s+(.+)$/);
        if (ol) {
            if (!inOl) {
                closeLists();
                html.push('<ol>');
                inOl = true;
            }
            html.push(`<li>${formatInline(ol[1])}</li>`);
            continue;
        }

        closeLists();
        html.push(`<p>${formatInline(line)}</p>`);
    }

    closeLists();
    return html.join('');
}

function formatInline(input) {
    let out = input;
    out = out.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    out = out.replace(/`([^`]+)`/g, '<code>$1</code>');
    return out;
}

function escapeHtml(input) {
    return input
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

