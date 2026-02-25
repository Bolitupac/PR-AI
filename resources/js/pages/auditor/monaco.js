export function initMonacoEditor() {
    const editorEl = document.getElementById('monaco-editor');
    if (!editorEl) return;

    // Loader script is still provided by the Blade template.
    if (typeof window.require !== 'function') return;

    const defaultCode =
        "<" +
        "?php\n\n// Paste logic here to audit...\n\nclass LogicAuditor {\n    public function check() {\n        return true;\n    }\n}";

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
            language: '',
            theme: 'vs-dark',
            automaticLayout: true,
            fontSize: 15,
            lineHeight: 24,
            roundedSelection: true,
            scrollBeyondLastLine: false,
            readOnly: false,
            minimap: { enabled: false },
        });
    });
}
