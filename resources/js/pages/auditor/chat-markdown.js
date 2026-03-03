// Renders a safe markdown subset for AI replies.
export function renderChatMarkdown(markdownText) {
    const text = String(markdownText ?? '');
    const escaped = escapeHtml(text);
    const lines = escaped.replace(/\r\n?/g, '\n').split('\n');

    const html = [];
    let inUl = false;
    let inOl = false;
    let inCodeBlock = false;
    let codeBuffer = [];

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

    const closeCodeBlock = () => {
        if (!inCodeBlock) return;
        html.push(`<pre><code>${codeBuffer.join('\n')}</code></pre>`);
        inCodeBlock = false;
        codeBuffer = [];
    };

    for (const rawLine of lines) {
        const line = rawLine.trim();

        if (line.startsWith('```')) {
            closeLists();
            if (inCodeBlock) {
                closeCodeBlock();
            } else {
                inCodeBlock = true;
                codeBuffer = [];
            }
            continue;
        }

        if (inCodeBlock) {
            codeBuffer.push(rawLine);
            continue;
        }

        if (line === '') {
            if (inUl || inOl) {
                continue;
            }
            closeLists();
            html.push('<p></p>');
            continue;
        }

        const hr = line.match(/^([-*_])\1{2,}$/);
        if (hr) {
            closeLists();
            html.push('<hr class="msg-hr">');
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

        const ol = line.match(/^\d+[.)]\s*(.+)$/);
        if (ol) {
            if (!inOl) {
                closeLists();
                html.push('<ol>');
                inOl = true;
            }
            html.push(`<li>${formatInline(ol[1])}</li>`);
            continue;
        }

        const heading = line.match(/^(#{1,6})\s+(.+)$/);
        if (heading) {
            closeLists();
            const level = Math.min(6, heading[1].length);
            html.push(`<h${level}>${formatInline(heading[2])}</h${level}>`);
            continue;
        }

        const blockquote = line.match(/^>\s+(.+)$/);
        if (blockquote) {
            closeLists();
            html.push(`<blockquote>${formatInline(blockquote[1])}</blockquote>`);
            continue;
        }

        closeLists();
        html.push(`<p>${formatInline(line)}</p>`);
    }

    closeCodeBlock();
    closeLists();
    return html.join('');
}

function formatInline(input) {
    let out = input;
    out = out.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
    out = out.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    out = out.replace(/(^|[^*])\*(?!\*)([^*]+)\*(?!\*)/g, '$1<em>$2</em>');
    out = out.replace(/`([^`]+)`/g, '<code>$1</code>');
    out = out.replace(/\[(LOW|MEDIUM|HIGH|CRITICAL)\]/gi, (_m, level) => `<span class="severity-tag is-${String(level).toLowerCase()}">[${String(level).toUpperCase()}]</span>`);
    out = out.replace(/(^|[\s(])([A-Za-z0-9_./-]+\.[A-Za-z0-9_+-]+:\d+(?:-\d+)?)(?=$|[\s),.;])/g, '$1<span class="file-line-ref">$2</span>');
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
