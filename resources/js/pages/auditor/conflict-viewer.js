function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function renderMetadataOnlyPanel(conflictData) {
    const commands = (conflictData?.suggested_git_commands || [])
        .map((cmd) => `<li><code>${escapeHtml(cmd)}</code></li>`)
        .join('');

    return `
        <div class="conflict-viewer conflict-viewer--metadata">
            <div class="conflict-metadata-banner">
                <strong>Metadata-only conflict</strong>
                <p>${escapeHtml(conflictData?.message || 'This pull request is reported as conflicted, but the provider API did not return line-level conflict hunks.')}</p>
            </div>
            <dl class="conflict-metadata-facts">
                <dt>Repository</dt><dd>${escapeHtml(conflictData?.repo || '—')}</dd>
                <dt>PR / MR</dt><dd>#${escapeHtml(conflictData?.pr_number || '—')}</dd>
                <dt>Title</dt><dd>${escapeHtml(conflictData?.title || '—')}</dd>
                <dt>Base → Head</dt><dd><code>${escapeHtml(conflictData?.base_ref || '—')}</code> ← <code>${escapeHtml(conflictData?.head_ref || '—')}</code></dd>
                <dt>mergeable_state</dt><dd><code>${escapeHtml(conflictData?.mergeable_state || '—')}</code></dd>
                <dt>Source</dt><dd><code>${escapeHtml(conflictData?.conflict_source || 'metadata_only')}</code></dd>
            </dl>
            <div class="conflict-metadata-steps">
                <h4>Resolve locally</h4>
                <ol>
                    <li>Fetch latest branches from the remote.</li>
                    <li>Check out the head branch and merge (or rebase) onto the base branch.</li>
                    <li>Your Git client will show real <code>&lt;&lt;&lt;&lt;&lt;&lt;&lt;</code> markers—resolve those, then commit.</li>
                    <li>Use <code>git merge --abort</code> if you need to undo the merge attempt.</li>
                </ol>
                ${commands ? `<ul class="conflict-metadata-commands">${commands}</ul>` : ''}
            </div>
            <p class="conflict-metadata-note">The AI audit below uses this metadata. It will not show a fake side-by-side conflict diff.</p>
        </div>
    `;
}

export function initConflictViewer(container) {
    if (!container) return { render: () => {}, clear: () => {} };

    let state = {
        conflictData: null,
        files: [],
        activeFileIndex: 0,
        activeHunkIndex: 0,
    };

    const render = (conflictData = {}) => {
        state.conflictData = conflictData;
        state.files = Array.isArray(conflictData.files) ? conflictData.files : [];
        state.activeFileIndex = 0;
        state.activeHunkIndex = 0;
        draw();
    };

    const clear = () => {
        state.conflictData = null;
        state.files = [];
        container.innerHTML = '<div class="diff-empty">No merge conflicts to display.</div>';
    };

    const draw = () => {
        const metadataOnly = state.conflictData?.has_hunks === false
            || state.conflictData?.conflict_source === 'github_metadata_only'
            || state.files.length === 0;

        if (metadataOnly) {
            container.innerHTML = renderMetadataOnlyPanel(state.conflictData || {});
            return;
        }

        const file = state.files[state.activeFileIndex];
        const hunks = file?.hunks || [];
        const hunk = hunks[state.activeHunkIndex] || null;

        const fileTabs = state.files.map((entry, index) => {
            const active = index === state.activeFileIndex ? ' is-active' : '';
            const count = entry.conflict_count || (entry.hunks || []).length;
            return `<button type="button" class="conflict-file-tab${active}" data-file-index="${index}">${escapeHtml(entry.path)} (${count})</button>`;
        }).join('');

        const hunkNav = hunks.length > 1
            ? `<div class="conflict-hunk-nav">
                <button type="button" class="conflict-hunk-btn" data-hunk-nav="prev" ${state.activeHunkIndex === 0 ? 'disabled' : ''}>Previous hunk</button>
                <span>Hunk ${state.activeHunkIndex + 1} of ${hunks.length}</span>
                <button type="button" class="conflict-hunk-btn" data-hunk-nav="next" ${state.activeHunkIndex >= hunks.length - 1 ? 'disabled' : ''}>Next hunk</button>
               </div>`
            : '';

        const hunkBody = hunk
            ? `<div class="conflict-hunk-meta">Lines ${escapeHtml(hunk.start_line)}–${escapeHtml(hunk.end_line)} · Ours: ${escapeHtml(hunk.ours_label || 'HEAD')} · Theirs: ${escapeHtml(hunk.theirs_label || 'incoming')}</div>
               <div class="conflict-columns">
                 <div class="conflict-col conflict-col--ours">
                   <div class="conflict-col-head">Ours (${escapeHtml(hunk.ours_label || 'HEAD')})</div>
                   <pre class="conflict-snippet">${escapeHtml(hunk.ours_snippet || '')}</pre>
                 </div>
                 <div class="conflict-col conflict-col--theirs">
                   <div class="conflict-col-head">Theirs (${escapeHtml(hunk.theirs_label || 'incoming')})</div>
                   <pre class="conflict-snippet">${escapeHtml(hunk.theirs_snippet || '')}</pre>
                 </div>
               </div>
               <details class="conflict-raw-details">
                 <summary>Raw conflict markers</summary>
                 <pre class="conflict-raw-block">${escapeHtml(hunk.raw_marker_block || '')}</pre>
               </details>`
            : '<div class="diff-empty">No hunks in this file.</div>';

        container.innerHTML = `
            <div class="conflict-viewer">
                <div class="conflict-file-tabs">${fileTabs}</div>
                ${hunkNav}
                ${hunkBody}
            </div>
        `;
    };

    container.addEventListener('click', (event) => {
        const fileTab = event.target.closest('.conflict-file-tab');
        if (fileTab) {
            state.activeFileIndex = Number(fileTab.dataset.fileIndex) || 0;
            state.activeHunkIndex = 0;
            draw();
            return;
        }

        const nav = event.target.closest('[data-hunk-nav]');
        if (!nav) return;

        const file = state.files[state.activeFileIndex];
        const max = (file?.hunks?.length || 1) - 1;
        if (nav.dataset.hunkNav === 'prev') {
            state.activeHunkIndex = Math.max(0, state.activeHunkIndex - 1);
        } else {
            state.activeHunkIndex = Math.min(max, state.activeHunkIndex + 1);
        }
        draw();
    });

    return { render, clear };
}
