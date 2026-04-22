import test from 'node:test';
import assert from 'node:assert/strict';

import { parseDocGenMarkers, stripDocGenMarkers } from '../../../resources/js/pages/auditor/document-generator/doc-gen-markers.js';

// Verifies that generated question ids and export formats are normalized before reaching the UI.
test('parseDocGenMarkers normalizes generated question ids and lowercase format values', () => {
    const input = [
        'Preparing document...',
        '[DOC_QUESTION]{"question":"Pick output style","options":[" Summary ","Detailed"],"id":""}[/DOC_QUESTION]',
        '[DOC_FORMATS][" PDF ","DocX "][/DOC_FORMATS]',
        '[DOC_READY]',
    ].join('\n');

    const parsed = parseDocGenMarkers(input);

    assert.equal(parsed.ready, true);
    assert.equal(parsed.questions.length, 1);
    assert.deepEqual(parsed.questions[0], {
        id: 'docgen-question-1',
        question: 'Pick output style',
        options: ['Summary', 'Detailed'],
    });
    assert.deepEqual(parsed.formats, {
        allowed: ['pdf', 'docx'],
        default: 'pdf',
    });
    assert.equal(parsed.visibleText, 'Preparing document...');
});

// Verifies that malformed question and format blocks fail safely and fall back to defaults.
test('parseDocGenMarkers ignores malformed questions and falls back to default formats', () => {
    const input = [
        'Drafting...',
        '[DOC_QUESTION]{"question":"","options":["Yes"]}[/DOC_QUESTION]',
        '[DOC_QUESTION]{"question":"Missing options"}[/DOC_QUESTION]',
        '[DOC_QUESTION]not-json[/DOC_QUESTION]',
        '[DOC_FORMATS]not-json[/DOC_FORMATS]',
    ].join('\n');

    const parsed = parseDocGenMarkers(input);

    assert.deepEqual(parsed.questions, []);
    assert.deepEqual(parsed.formats, {
        allowed: ['pdf'],
        default: 'pdf',
    });
    assert.equal(parsed.visibleText, 'Drafting...');
});

// Verifies that an unfinished streamed tag prefix is removed from the text shown to the user.
test('stripDocGenMarkers removes dangling streamed prefixes from visible output', () => {
    const input = 'Generating preview...\n[DOC_PRE';

    assert.equal(stripDocGenMarkers(input), 'Generating preview...');
});
