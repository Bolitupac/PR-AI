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

// Verifies the full import flow dispatches a selected diff payload after a repo and pull request are chosen.
test('repo import flow dispatches diff-selected with repo, pull request, diff, and comments', { timeout: 5000 }, async () => {
    const { document } = createFakeDom();
    global.document = document;
    global.CustomEvent = FakeCustomEvent;
    global.window = { location: { origin: 'https://example.test' } };

    const restoreTimers = installAsyncStubs();
    buildRepoModalDom(document);

    const dispatched = [];
    document.addEventListener('auditor:diff-selected', (event) => {
        dispatched.push(event.detail);
    });

    global.fetch = async (url) => {
        const normalized = String(url);

        if (normalized.includes('/api/github/repos')) {
            return {
                ok: true,
                async json() {
                    return { repos: [{ full_name: 'acme/platform' }] };
                },
            };
        }

        if (normalized.includes('/api/github/pulls')) {
            return {
                ok: true,
                async json() {
                    return {
                        pulls: [
                            {
                                number: 12,
                                title: 'Refine repo import flow',
                                author: 'tobi',
                                state: 'open',
                                updated_at: new Date().toISOString(),
                            },
                        ],
                    };
                },
            };
        }

        if (normalized.includes('/api/github/pull-diff')) {
            return {
                ok: true,
                headers: { get() { return 'text/plain'; } },
                async text() {
                    return 'diff --git a/app.js b/app.js';
                },
            };
        }

        if (normalized.includes('/api/github/pull-comments')) {
            return {
                ok: true,
                async json() {
                    return {
                        comments: [
                            { path: 'app.js', line: 5, body: 'Watch this edge case.' },
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
    const loadRepoButton = document.getElementById('load-repo-btn');

    repoSelectTrigger.click();
    await flushAsyncWork();

    repoSelectModal.value = 'acme/platform';
    repoSelectModal.dispatchEvent({ type: 'change', target: repoSelectModal });
    await flushAsyncWork();

    repoPrList.children[0].click();
    loadRepoButton.click();
    await flushAsyncWork();

    restoreTimers();

    assert.equal(dispatched.length, 1);
    assert.deepEqual(dispatched[0], {
        source: 'github',
        repo: 'acme/platform',
        prNumber: 12,
        prTitle: 'Refine repo import flow',
        auditStatus: 'open',
        auditTitle: 'acme/platform pull request audit Refine repo import flow',
        auditKind: 'pull_request_audit',
        diffText: 'diff --git a/app.js b/app.js',
        comments: [
            { path: 'app.js', line: 5, body: 'Watch this edge case.' },
        ],
    });
});
