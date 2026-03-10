import mermaid from 'mermaid';

const getMermaidTheme = () =>
    document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'default';

export function initMermaid() {
    if (window.mermaid) return;
    window.mermaid = mermaid;
    mermaid.initialize({
        startOnLoad: false,
        theme: getMermaidTheme(),
    });
}

export function refreshMermaidTheme() {
    if (!window.mermaid) return;
    mermaid.initialize({
        startOnLoad: false,
        theme: getMermaidTheme(),
    });
}

export function renderMermaidIn(container) {
    if (!container || !window.mermaid) return;
    const nodes = Array.from(container.querySelectorAll('.mermaid'));
    if (nodes.length === 0) return;
    window.mermaid.run({ nodes });
}

