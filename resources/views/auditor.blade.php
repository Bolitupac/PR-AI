<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Git PULL Assistant | Auditor</title>
    @vite(['resources/css/auditor-ui.css', 'resources/js/app.js'])
</head>
<body>

<div class="app-shell">
    <section class="hero-view">
        <aside class="sidebar-bg">
            <button class="icon-btn" type="button" aria-label="Menu">
                <img src="{{ asset('images/menu.png') }}" alt="Menu" class="ui-logo">
            </button>

            <button class="icon-btn bottom" type="button" aria-label="GitHub">
                <img src="{{ asset('images/github.png') }}" alt="GitHub" class="ui-logo">
            </button>
        </aside>

        <main class="main-workspace">
            <section class="codebox-outer">
                <header class="panel-head">
                    <span class="panel-title">Logic Auditor</span>
                    <button type="button" class="import-btn">IMPORT REPO</button>
                </header>
                <div id="monaco-editor" aria-label="Code Editor"></div>
            </section>

            <section class="ai-panel">
                <div class="ai-content">
                    <header class="panel-head ai-head">
                        <h3>Gemini Audit</h3>
                    </header>
                    <hr class="line-sep">

                    <div id="ai-response-area" class="chat-demo-list">
                        <div class="msg user">Can you summarize the most risky changes in this pull request?</div>
                        <div class="msg ai">I found 2 high-risk logic changes in payment validation and role checks.</div>
                        <div class="msg user">Show me one critical issue quickly.</div>
                        <div class="msg ai">In <code>CheckoutService.php</code>, discount can go negative after stacked promos. Add a floor at zero.</div>
                        <div class="msg ai">Scroll down to review the diff and all detected issues.</div>
                    </div>
                </div>

                <div class="chat-container">
                    <button class="action-btn ghost" id="mic-btn" type="button" aria-label="Mic">
                        <img src="{{ asset('images/mic.png') }}" alt="Mic" class="action-icon">
                    </button>

                    <input type="text" class="chat-input" id="user-prompt" placeholder="Ask Gemini...">

                    <button class="action-btn" id="send-btn" type="button" aria-label="Send">
                        <img src="{{ asset('images/send.png') }}" alt="Send" class="action-icon">
                    </button>
                </div>
            </section>
        </main>
    </section>

    <section class="diff-section" id="diff-section">
        <div class="diff-wrap">
            <div class="diff-head">
                <h2>Pull Request Diff Summary</h2>
                <span class="badge-total">5 Demo Errors</span>
            </div>
            <div id="diff2html-container"></div>
        </div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>
<script>
    require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs' } });
    require(['vs/editor/editor.main'], function () {
        const defaultCode = '<' + '?php\n\n// Paste logic here to audit...\n\nclass LogicAuditor {\n    public function check() {\n        return true;\n    }\n}';

        window.editor = monaco.editor.create(document.getElementById('monaco-editor'), {
            value: defaultCode,
            language: 'php',
            theme: 'vs-dark',
            automaticLayout: true,
            fontSize: 15,
            lineHeight: 24,
            roundedSelection: true,
            scrollBeyondLastLine: false,
            readOnly: false,
            minimap: { enabled: false }
        });
    });

   
</script>

</body>
</html>
