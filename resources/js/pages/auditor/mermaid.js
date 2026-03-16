import mermaid from 'mermaid';

const getMermaidTheme = () =>
    document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'default';

export function initMermaid() {
    if (window.mermaid) return;
    window.mermaid = mermaid;
    mermaid.initialize({
        startOnLoad: false,
        theme: getMermaidTheme(),
        suppressErrorRendering: true,
    });
}

export function refreshMermaidTheme() {
    if (!window.mermaid) return;
    mermaid.initialize({
        startOnLoad: false,
        theme: getMermaidTheme(),
        suppressErrorRendering: true,
    });
}

export async function renderMermaidIn(container) {
    if (!container || !window.mermaid) return;
    const nodes = Array.from(container.querySelectorAll('.mermaid'));
    if (nodes.length === 0) return;

    for (const node of nodes) {
        try {
            // Check if it's already rendered or in progress to avoid double work
            if (node.getAttribute('data-processed')) continue;
            
            const originalSrc = node.getAttribute('data-mermaid-src');
            await window.mermaid.run({ nodes: [node], suppressErrors: true });
            
            // If Mermaid cleared the node but didn't render anything (suppressed error)
            // or if it threw internally, we might need to restore.
            if (node.innerHTML.trim() === '' && originalSrc) {
                node.innerHTML = originalSrc;
            }
        } catch (err) {
            const originalSrc = node.getAttribute('data-mermaid-src');
            if (originalSrc) {
                node.innerHTML = originalSrc;
            }
        }
    }
}

