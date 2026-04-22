import test from 'node:test';
import assert from 'node:assert/strict';
import { performance } from 'node:perf_hooks';

import { renderChatMarkdown } from '../../../resources/js/pages/auditor/chat-markdown.js';

// Measures local AI reply rendering latency so we can catch unusually slow markdown formatting in the client.
test('chat reply rendering completes within an acceptable local timing budget', () => {
    const reply = Array.from({ length: 250 }, (_, index) => (
        `## Section ${index + 1}\n- Item A\n- Item B\n| File | Status |\n| --- | --- |\n| app.js | ok |\n`
    )).join('\n');

    const startedAt = performance.now();
    const html = renderChatMarkdown(reply);
    const elapsedMs = performance.now() - startedAt;

    assert.ok(html.length > 0);
    assert.ok(elapsedMs < 250, `Expected chat reply rendering under 250ms, got ${elapsedMs.toFixed(2)}ms`);
});
