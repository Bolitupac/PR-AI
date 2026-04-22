import test from 'node:test';
import assert from 'node:assert/strict';

import { renderChatMarkdown } from '../../../resources/js/pages/auditor/chat-markdown.js';

// Verifies that richer markdown structures used in chat are rendered into the expected HTML blocks.
test('chat markup renders lists, tables, headings, and mermaid fences', () => {
    const markup = [
        '# Review Notes',
        '',
        '- First item',
        '- Second item',
        '',
        '| File | Status |',
        '| --- | :---: |',
        '| app.js | ok |',
        '',
        '```mermaid',
        'graph TD',
        'A-->B',
        '```',
    ].join('\n');

    const html = renderChatMarkdown(markup);

    assert.match(html, /<h1>Review Notes<\/h1>/);
    assert.match(html, /<li>First item<\/li>/);
    assert.match(html, /<li>Second item<\/li>/);
    assert.match(html, /<table class="msg-table">/);
    assert.match(html, /<th style="text-align:center">Status<\/th>/);
    assert.match(html, /<pre class="mermaid" data-mermaid-src="graph TD\nA--&gt;B">graph TD\nA--&gt;B<\/pre>/);
});
