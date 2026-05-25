function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

export function initConflictViewer(container) {
    if (!container) return { render: () => {}, clear: () => {} };

    let state = {
        files: [],
        activeFileIndex: 0,
        activeHunkIndex: 0,
    };

    const render = (conflictData = {}) => {
        state.files = Array.isArray(conflictData.files) ? conflictData.files : [];
        state.activeFileIndex = 0;
        state.activeHunkIndex = 0;
        draw();
    };

    const clear = () => {
        state.files = [];
        container.innerHTML = '<div class="diff-empty">No merge conflicts to display.</div>';
    };

    const draw = () => {
        if (state.files.length === 0) {
            container.innerHTML = '<div class="diff-empty">No conflict hunks parsed. The PR may still be unmergeable—check provider status.</div>';
            return;
        }

        const file = state.files[state.activeFileIndex];
        const hunks = file.hunks || [];
        const hunk = hunks[state.activeHunkIndex] || null;

        const fileTabs = state.files.map((entry, index) => {
            const active = index === state.activeFileIndex ? ' is-active' : '';
            return `<button type="button" class="conflict-file-tab${active}" data-file-index="${index}">${escapeHtml(entry.path)} (${entry.conflict_count || hunks.length})</button>`;
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
