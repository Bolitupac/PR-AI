export function initMonacoEditor() {
    const editorEl = document.getElementById('monaco-editor');
    if (!editorEl) return;

    // Loader script is still provided by the Blade template.
    if (typeof window.require !== 'function') return;

    const defaultCode =
        "# Paste git diff code here...\n" +
        "# Example:\n" +
        "# diff --git a/file.js b/file.js\n" +
        "# @@ -1,2 +1,2 @@\n" +
        "# -old line\n" +
        "# +new line";

    window.require.config({
        paths: {
            vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs',
        },
    });

    window.require(['vs/editor/editor.main'], function () {
        if (!window.monaco) return;
        // Keep a global ref for other ui actions
        window.editor = window.monaco.editor.create(editorEl, {
            value: defaultCode,
            language: 'diff',
            theme: 'vs-dark',
            automaticLayout: true,
            fontSize: 13,
            lineHeight: 21,
            fontFamily: "'SF Mono', 'JetBrains Mono', Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace",
            roundedSelection: true,
            scrollBeyondLastLine: false,
            readOnly: false,
            minimap: { enabled: false },
        });
    });
}
