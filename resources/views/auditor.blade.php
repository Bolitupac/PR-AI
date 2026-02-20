<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logic Auditor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #121212; color: #e0e0e0; font-family: sans-serif; margin: 0; display: flex; flex-direction: column; height: 100vh; }
        nav { background: #1e1e1e; padding: 1rem 2rem; border-bottom: 1px solid #333; }
        main { flex: 1; display: flex; flex-direction: column; padding: 20px; }
        #editor-container { flex: 1; border: 1px solid #333; border-radius: 8px; overflow: hidden; background: #1e1e1e; }
        .command-bar { margin-top: 20px; background: #1e1e1e; padding: 15px; border-radius: 8px; border: 1px solid #333; }
        input[type="text"] { width: 100%; background: #2d2d2d; border: 1px solid #444; color: white; padding: 12px; border-radius: 6px; outline: none; }
    </style>
</head>
<body>

<nav><div style="font-weight: bold;">Git PULL assistant</div></nav>

<main>
    <div id="editor-container">
        <div id="monaco-editor" style="height: 100%;"></div>
    </div>
    <div class="command-bar">
        <input type="text" placeholder="Type a command...">
    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>
<script>
    require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs' } });
    require(['vs/editor/editor.main'], function () {
        window.editor = monaco.editor.create(document.getElementById('monaco-editor'), {
            value: "// Editor is Ready!\nfunction hello() {\n    console.log('No more Vite errors.');\n}",
            language: 'php',  //i will change this later to match the lang
            theme: 'vs-dark',
            automaticLayout: true
        });
    });
</script>
</body>
</html>