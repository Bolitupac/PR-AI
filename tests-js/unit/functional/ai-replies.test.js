import test from 'node:test';
import assert from 'node:assert/strict';

import { renderChatMarkdown } from '../../../resources/js/pages/auditor/chat-markdown.js';

// Verifies that a normal AI reply is turned into readable, safe HTML for the chat surface.
test('ai replies render as safe chat paragraphs with inline formatting', () => {
    const reply = [
        'Here is the summary.',
        '',
        'Use **bold** for the main point and `inline code` for commands.',
        '',
        '<script>alert("xss")</script>',
    ].join('\n');

    const html = renderChatMarkdown(reply);

    assert.match(html, /<p>Here is the summary\.<\/p>/);
    assert.match(html, /<strong>bold<\/strong>/);
    assert.match(html, /<code>inline code<\/code>/);
    assert.match(html, /&lt;script&gt;alert\(&quot;xss&quot;\)&lt;\/script&gt;/);
});
