// Renders a safe markdown subset for AI replies.
export function renderChatMarkdown(markdownText) {
    const text = String(markdownText ?? '');
    const lines = text.replace(/\r\n?/g, '\n').split('\n');

    const html = [];
    let inUl = false;
    let inOl = false;
    let inCodeBlock = false;
    let codeFenceLang = '';
    let codeBuffer = [];
    const listStack = [];
    let pendingTable = null;

    const closeLists = () => {
        while (listStack.length) {
            html.push(`</${listStack.pop()}>`);
        }
        inUl = false;
        inOl = false;
    };

    const flushTable = () => {
        if (!pendingTable) return;
        const { header, align, rows } = pendingTable;
        const renderRow = (cells, cellTag) =>
            `<tr>${cells.map((cell, i) => {
                const alignAttr = align[i] ? ` style="text-align:${align[i]}"` : '';
                return `<${cellTag}${alignAttr}>${formatInline(escapeHtml(cell))}</${cellTag}>`;
            }).join('')}</tr>`;
        html.push('<table class="msg-table">');
        if (header.length) {
            html.push('<thead>');
            html.push(renderRow(header, 'th'));
            html.push('</thead>');
        }
        if (rows.length) {
            html.push('<tbody>');
            rows.forEach(row => html.push(renderRow(row, 'td')));
            html.push('</tbody>');
        }
        html.push('</table>');
        pendingTable = null;
    };

    const parseTableSeparator = (line) => {
        const cells = line.split('|').map(s => s.trim()).filter(Boolean);
        if (!cells.length) return null;
        const align = cells.map(cell => {
            const left = cell.startsWith(':');
            const right = cell.endsWith(':');
            if (left && right) return 'center';
            if (right) return 'right';
            if (left) return 'left';
            return '';
        });
        const isSeparator = cells.every(cell => /^:?-{3,}:?$/.test(cell));
        return isSeparator ? align : null;
    };

    const closeCodeBlock = () => {
        if (!inCodeBlock) return;
        const codeText = codeBuffer.join('\n');
        const escapedCode = escapeHtml(codeText);
        if (codeFenceLang === 'mermaid') {
            html.push(`<pre class="mermaid">${escapedCode}</pre>`);
        } else {
            html.push(`<pre><code>${escapedCode}</code></pre>`);
        }
        inCodeBlock = false;
        codeBuffer = [];
        codeFenceLang = '';
    };

    for (const rawLine of lines) {
        const line = rawLine.trim();

        if (line.toLowerCase().startsWith('<details')) {
            closeLists();
            flushTable();
            html.push('<details class="msg-details">');
            continue;
        }

        if (line.toLowerCase().startsWith('<summary')) {
            closeLists();
            flushTable();
            const summaryText = line.replace(/<\/?summary>/gi, '').trim();
            html.push(`<summary>${formatInline(escapeHtml(summaryText))}</summary>`);
            continue;
        }

        if (line.toLowerCase().startsWith('</details')) {
            closeLists();
            flushTable();
            html.push('</details>');
            continue;
        }

        if (line.startsWith('```')) {
            closeLists();
            flushTable();
            if (inCodeBlock) {
                closeCodeBlock();
            } else {
                inCodeBlock = true;
                codeBuffer = [];
                codeFenceLang = line.slice(3).trim().toLowerCase();
            }
            continue;
        }

        if (inCodeBlock) {
            codeBuffer.push(rawLine);
            continue;
        }

        if (line === '') {
            flushTable();
            if (listStack.length > 0) {
                continue;
            }
            closeLists();
            html.push('<p></p>');
            continue;
        }

        const hr = line.match(/^(?:-{3,}|\*{3,}|_{3,})$/);
        if (hr) {
            closeLists();
            flushTable();
            html.push('<hr class="msg-hr">');
            continue;
        }

        const listMatch = rawLine.match(/^(\s*)([-*]|\d+[.)])\s+(.+)$/);
        if (listMatch) {
            const indentRaw = (listMatch[1] || '').replace(/\t/g, '    ');
            const indent = Math.floor(indentRaw.length / 2);
            const isOrdered = /\d/.test(listMatch[2]);
            const listType = isOrdered ? 'ol' : 'ul';

            while (listStack.length - 1 > indent) {
                html.push(`</${listStack.pop()}>`);
            }

            if (listStack.length === 0 || listStack.length - 1 < indent || listStack[listStack.length - 1] !== listType) {
                html.push(`<${listType}>`);
                listStack.push(listType);
            }

            html.push(`<li>${formatInline(escapeHtml(listMatch[3]))}</li>`);
            continue;
        }

        const tableSepAlign = parseTableSeparator(line);
        if (tableSepAlign && pendingTable && pendingTable.header.length) {
            pendingTable.align = tableSepAlign;
            continue;
        }

        if (line.includes('|')) {
            const cells = line.split('|').map(s => s.trim()).filter(Boolean);
            if (cells.length >= 2) {
                if (!pendingTable) {
                    pendingTable = { header: cells, align: [], rows: [] };
                } else if (pendingTable.align.length) {
                    pendingTable.rows.push(cells);
                } else if (!pendingTable.align.length && pendingTable.header.length) {
                    // still waiting for separator; treat as header row replacement
                    pendingTable.header = cells;
                }
                continue;
            }
        }

        flushTable();

        const heading = line.match(/^(#{1,6})\s+(.+)$/);
        if (heading) {
            closeLists();
            const level = Math.min(6, heading[1].length);
            html.push(`<h${level}>${formatInline(escapeHtml(heading[2]))}</h${level}>`);
            continue;
        }

        const blockquote = line.match(/^>\s+(.+)$/);
        if (blockquote) {
            closeLists();
            html.push(`<blockquote>${formatInline(escapeHtml(blockquote[1]))}</blockquote>`);
            continue;
        }

        closeLists();
        html.push(`<p>${formatInline(escapeHtml(line))}</p>`);
    }

    closeCodeBlock();
    closeLists();
    flushTable();
    return html.join('');
}

function formatInline(input) {
    let out = input;
    out = out.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
    out = out.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    out = out.replace(/(^|[^*])\*(?!\*)([^*]+)\*(?!\*)/g, '$1<em>$2</em>');
    out = out.replace(/`([^`]+)`/g, '<code>$1</code>');
    out = out.replace(/\[(LOW|MEDIUM|HIGH|CRITICAL)\]/gi, (_m, level) => `<span class="severity-tag is-${String(level).toLowerCase()}">[${String(level).toUpperCase()}]</span>`);
    out = out.replace(
        /(^|[\s(])([A-Za-z0-9_./-]+\.[A-Za-z0-9_+-]+:\d+(?:-\d+)?)(?=$|[\s),.;])/g,
        '$1<span class="file-line-ref">$2</span>'
    );
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
