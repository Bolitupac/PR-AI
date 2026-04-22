import test from 'node:test';
import assert from 'node:assert/strict';

import { appendElement, createFakeDom, FakeCustomEvent } from '../../helpers/fake-dom.js';
import { initGitRepoModal } from '../../../resources/js/pages/auditor/git-repo-modal.js';

function installAsyncStubs() {
    const originalSetTimeout = global.setTimeout;
    const originalSetInterval = global.setInterval;
    const originalClearInterval = global.clearInterval;

    global.setTimeout = (fn, delayMs = 0) => {
        if (delayMs <= 0) fn();
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

async function flushAsyncWork() {
    for (let index = 0; index < 10; index += 1) {
        await Promise.resolve();
    }
}

function buildRepoModalDom(document) {
    appendElement(document, {
        tag: 'button',
        id: 'repo-select',
        attributes: {
            'data-repos-url': '/api/github/repos',
            'data-pulls-url': '/api/github/pulls',
            'data-pull-diff-url': '/api/github/pull-diff',
        },
    });

    appendElement(document, {
        tag: 'div',
        id: 'repo-modal',
        attributes: { 'aria-hidden': 'true' },
    }).dataset.connectUrl = '/auth/github';

    appendElement(document, { tag: 'select', id: 'repo-import-select' });
    appendElement(document, { tag: 'div', id: 'repo-import-help' });
    appendElement(document, { tag: 'div', id: 'repo-import-actions-row' });
    appendElement(document, { tag: 'a', id: 'repo-connect-github-btn' });
    appendElement(document, { tag: 'button', id: 'repo-retry-btn' });
    appendElement(document, { tag: 'div', id: 'repo-pr-box' });
    appendElement(document, { tag: 'div', id: 'repo-pr-state' });
    appendElement(document, { tag: 'div', id: 'repo-pr-list' });
    appendElement(document, { tag: 'div', id: 'repo-load-cue' });
    appendElement(document, { tag: 'button', id: 'load-repo-btn' });
    appendElement(document, { tag: 'button', attributes: { 'data-close': 'repo-modal' } });
}

// Verifies the repo import modal loads repositories and then renders pull requests in the import UI.
test('repo import modal displays repositories and pull requests for the selected repository', async () => {
    const { document } = createFakeDom();
    global.document = document;
    global.CustomEvent = FakeCustomEvent;
    global.window = { location: { origin: 'https://example.test' } };

    const restoreTimers = installAsyncStubs();
    buildRepoModalDom(document);

    global.fetch = async (url) => {
        const normalized = String(url);

        if (normalized.includes('/api/github/repos')) {
            return {
                ok: true,
                async json() {
                    return {
                        repos: [
                            { full_name: 'acme/app-one' },
                            { full_name: 'acme/app-two' },
                        ],
                    };
                },
            };
        }

        if (normalized.includes('/api/github/pulls') && normalized.includes('repo=acme%2Fapp-one')) {
            return {
                ok: true,
                async json() {
                    return {
                        pulls: [
                            {
                                number: 42,
                                title: 'Fix export issues',
                                author: 'jane',
                                state: 'open',
                                updated_at: new Date().toISOString(),
                            },
                            {
                                number: 43,
                                title: 'Add repo loading polish',
                                author: 'sam',
                                state: 'open',
                                updated_at: new Date().toISOString(),
                            },
                        ],
                    };
                },
            };
        }

        throw new Error(`Unexpected fetch: ${normalized}`);
    };

    initGitRepoModal();

    const repoSelectTrigger = document.getElementById('repo-select');
    const repoSelectModal = document.getElementById('repo-import-select');
    const repoPrList = document.getElementById('repo-pr-list');
    const repoPrState = document.getElementById('repo-pr-state');
    const repoImportHelp = document.getElementById('repo-import-help');

    repoSelectTrigger.click();
    await flushAsyncWork();

    assert.equal(document.getElementById('repo-modal')?.classList.contains('is-open'), true);
    assert.equal(repoSelectModal.children.length, 3);
    assert.equal(repoSelectModal.children[1].textContent, 'acme/app-one');
    assert.equal(repoSelectModal.children[2].textContent, 'acme/app-two');
    assert.equal(repoImportHelp.textContent, 'Choose a repository to load its pull requests.');

    repoSelectModal.value = 'acme/app-one';
    repoSelectModal.dispatchEvent({ type: 'change', target: repoSelectModal });
    await flushAsyncWork();

    restoreTimers();

    assert.equal(repoPrList.children.length, 2);
    assert.match(repoPrList.children[0].innerHTML, /Fix export issues/);
    assert.match(repoPrList.children[1].innerHTML, /Add repo loading polish/);
    assert.equal(repoPrState.textContent, 'Open pull requests (2)');
    assert.equal(repoImportHelp.textContent, 'Loaded pull requests for acme/app-one.');
});
