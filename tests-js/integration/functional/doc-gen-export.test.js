import test from 'node:test';
import assert from 'node:assert/strict';

import { appendElement, createFakeDom, FakeCustomEvent } from '../../helpers/fake-dom.js';
import { exportDocGenDocument } from '../../../resources/js/pages/auditor/document-generator/doc-gen-export.js';

// Makes timer-driven status helpers deterministic so export tests can assert final UI state immediately.
function installTimerStubs() {
    const originalSetTimeout = global.setTimeout;
    const originalSetInterval = global.setInterval;
    const originalClearInterval = global.clearInterval;

    global.setTimeout = (fn, delayMs = 0) => {
        if (delayMs <= 0) {
            fn();
        }
        return 1;
    };
    global.setInterval = () => 1;
    global.clearInterval = () => {};

    return () => {
        global.setTimeout = originalSetTimeout;
        global.setInterval = originalSetInterval;
        global.clearInterval = originalClearInterval;
    };
}

// Verifies that a successful export request posts the expected payload and finishes with a ready status.
test('doc-gen export posts markdown, downloads the returned file, and reports success', async () => {
    const { document } = createFakeDom();
    global.document = document;
    global.CustomEvent = FakeCustomEvent;

    const restoreTimers = installTimerStubs();
    const responseArea = appendElement(document, { id: 'ai-response-area' });
    appendElement(document, {
        tag: 'meta',
        parent: document.body,
        attributes: { name: 'csrf-token', content: 'csrf-123' },
    });

    const fetchCalls = [];
    global.fetch = async (url, options) => {
        fetchCalls.push({ url, options });
        return {
            ok: true,
            headers: {
                get(name) {
                    return name === 'Content-Disposition' ? 'attachment; filename="custom-report.pdf"' : null;
                },
            },
            async blob() {
                return { size: 42 };
            },
        };
    };

    let revokedUrl = '';
    global.URL = {
        createObjectURL() {
            return 'blob:docgen-file';
        },
        revokeObjectURL(url) {
            revokedUrl = url;
        },
    };

    const result = await exportDocGenDocument({
        format: 'pdf',
        title: 'report',
        markdown: '# Export Me',
        responseArea,
    });

    const link = document.body.children.find((child) => child.tagName === 'A');
    const statusNode = responseArea.children[0];

    restoreTimers();

    assert.equal(result, true);
    assert.equal(fetchCalls.length, 1);
    assert.equal(fetchCalls[0].url, '/api/ai/docgen/export');
    assert.equal(fetchCalls[0].options.headers['X-CSRF-TOKEN'], 'csrf-123');
    assert.deepEqual(JSON.parse(fetchCalls[0].options.body), {
        format: 'pdf',
        title: 'report',
        markdown: '# Export Me',
    });
    assert.equal(link, undefined);
    assert.equal(revokedUrl, 'blob:docgen-file');
    assert.equal(statusNode.textContent, 'PDF export ready.');
});

// Verifies that export exits safely without making a network call when required input is missing.
test('doc-gen export returns early when required inputs are missing', async () => {
    const { document } = createFakeDom();
    global.document = document;
    global.CustomEvent = FakeCustomEvent;

    let called = false;
    global.fetch = async () => {
        called = true;
        return null;
    };

    const resultWithoutMarkdown = await exportDocGenDocument({
        format: 'pdf',
        title: 'report',
        markdown: '',
        responseArea: appendElement(document, { id: 'ai-response-area' }),
    });

    const resultWithoutContainer = await exportDocGenDocument({
        format: 'pdf',
        title: 'report',
        markdown: '# Draft',
        responseArea: null,
    });

    assert.equal(resultWithoutMarkdown, false);
    assert.equal(resultWithoutContainer, false);
    assert.equal(called, false);
});

// Verifies that API failures are surfaced back to the user as an error status message.
test('doc-gen export surfaces API failures as an error status', async () => {
    const { document } = createFakeDom();
    global.document = document;
    global.CustomEvent = FakeCustomEvent;

    const restoreTimers = installTimerStubs();
    const responseArea = appendElement(document, { id: 'ai-response-area' });

    global.fetch = async () => ({
        ok: false,
        async json() {
            return { message: 'Formatter unavailable.' };
        },
    });

    const result = await exportDocGenDocument({
        format: 'docx',
        title: 'report',
        markdown: '# Draft',
        responseArea,
    });

    const statusNode = responseArea.children[0];

    restoreTimers();

    assert.equal(result, false);
    assert.equal(statusNode.textContent, 'Formatter unavailable.');
    assert.equal(statusNode.classList.contains('is-error'), true);
});
