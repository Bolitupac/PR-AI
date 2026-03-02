// Creates and manages one status line tied to a single user message.
export function createChatStatus({ container, anchorNode }) {
    const node = document.createElement('div');
    node.className = 'chat-status-line is-info';
    node.textContent = '';

    const next = anchorNode?.nextSibling || null;
    container.insertBefore(node, next);
    container.scrollTop = container.scrollHeight;

    let ticker = null;

    const set = (text, tone = 'info') => {
        stopDots();
        node.textContent = text;
        node.classList.remove('is-info', 'is-success', 'is-error');
        node.classList.add(`is-${tone}`);
        container.scrollTop = container.scrollHeight;
    };

    const startDots = (textBase, tone = 'info') => {
        stopDots();
        node.classList.remove('is-info', 'is-success', 'is-error');
        node.classList.add(`is-${tone}`);
        const dots = ['.', '..', '...'];
        let i = 0;
        node.textContent = `${textBase}${dots[i]}`;
        ticker = setInterval(() => {
            i = (i + 1) % dots.length;
            node.textContent = `${textBase}${dots[i]}`;
        }, 420);
        container.scrollTop = container.scrollHeight;
    };

    const stopDots = () => {
        if (ticker) {
            clearInterval(ticker);
            ticker = null;
        }
    };

    const markSuccess = (text) => set(text, 'success');
    const markError = (text) => set(text, 'error');

    const remove = (delayMs = 0) => {
        stopDots();
        const run = () => {
            node.classList.add('is-fading');
            setTimeout(() => node.remove(), 220);
        };
        if (delayMs > 0) {
            setTimeout(run, delayMs);
            return;
        }
        run();
    };

    return { set, startDots, stopDots, markSuccess, markError, remove, node };
}

