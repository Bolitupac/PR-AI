<div class="diff-upload-modal" id="diff-upload-modal" aria-hidden="true">
    <div class="diff-upload-backdrop" data-close="diff-upload-modal"></div>
    <div class="diff-upload-card" role="dialog" aria-label="Upload diff file">
        <button class="diff-upload-close" type="button" aria-label="Close" data-close="diff-upload-modal">&times;</button>

        <div class="diff-upload-head">
            <div class="diff-upload-title">Upload diff file</div>
            <div class="diff-upload-sub">Drop a .diff/.patch file or browse from your device.</div>
        </div>

        <div class="diff-dropzone" id="diff-dropzone">
            <input class="diff-file-input" id="diff-file-input" type="file" accept=".diff,.patch,.txt,text/plain">
            <div class="diff-dropzone-main">Drop diff file here</div>
            <div class="diff-dropzone-sub">or click to browse</div>
        </div>

        <div class="diff-file-name" id="diff-file-name">No file selected.</div>
        <div class="diff-upload-state is-info" id="diff-upload-state">Waiting for diff file.</div>

        <button class="diff-upload-action" id="diff-upload-action" type="button" disabled>Use this diff</button>
    </div>
</div>
