import test from 'node:test';
import assert from 'node:assert/strict';

import { parseDocGenMarkers, stripDocGenMarkers } from '../../../resources/js/pages/auditor/document-generator/doc-gen-markers.js';

// Verifies that preview blocks, questions, formats, and ready state are extracted from DocGen protocol markers.
test('parseDocGenMarkers extracts preview questions and ready state', () => {
    const input = [
        'Working on it.',
        '[DOC_PREVIEW]# Draft',
        '',
        '- Intro[/DOC_PREVIEW]',
        '[DOC_QUESTION]{"question":"Choose a tone","options":["Formal","Friendly"]}[/DOC_QUESTION]',
        '[DOC_FORMATS]{"default":"docx","allowed":["pdf","docx","md"]}[/DOC_FORMATS]',
        '[DOC_READY]',
    ].join('\n');

    const parsed = parseDocGenMarkers(input);

    assert.equal(parsed.ready, true);
    assert.equal(parsed.autoTrigger, false);
    assert.equal(parsed.previewMarkdown, '# Draft\n\n- Intro');
    assert.equal(parsed.questions.length, 1);
    assert.equal(parsed.questions[0].question, 'Choose a tone');
    assert.deepEqual(parsed.questions[0].options, ['Formal', 'Friendly']);
    assert.equal(parsed.formats.default, 'docx');
    assert.deepEqual(parsed.formats.allowed, ['pdf', 'docx', 'md']);
    assert.equal(parsed.visibleText, 'Working on it.');
});

// Verifies that hidden protocol markers are stripped from the visible chat response.
test('stripDocGenMarkers removes hidden protocol markers from visible text', () => {
    const input = 'Hello\n[DOC_AUTO_TRIGGER]\n[DOC_PREVIEW]# Draft[/DOC_PREVIEW]\n[DOC_READY]';
    assert.equal(stripDocGenMarkers(input), 'Hello');
});

// Verifies that partial preview output is supported while the preview is still streaming.
test('parseDocGenMarkers supports partial preview streaming without leaking tags', () => {
    const input = 'Starting draft...\n[DOC_PREVIEW]# Heading\n\nPartial body';
    const parsed = parseDocGenMarkers(input);

    assert.equal(parsed.previewStreaming, true);
    assert.equal(parsed.previewMarkdown, '# Heading\n\nPartial body');
    assert.equal(parsed.visibleText, 'Starting draft...');
});
