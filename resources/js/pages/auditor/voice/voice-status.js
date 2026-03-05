export function createVoiceStatus(container) {
    if (!container) {
        return {
            set: () => {},
            startDots: () => {},
            stopDots: () => {},
            clear: () => {},
        };
    }

    const node = document.createElement('div');
    node.id = 'voice-transcribe-status';
    node.className = 'chat-status-line is-info';
    container.appendChild(node);

    let ticker = null;

    const resetTone = () => {
        node.classList.remove('is-info', 'is-success', 'is-error');
    };

    const set = (text, tone = 'info') => {
        if (ticker) {
            clearInterval(ticker);
            ticker = null;
        }
        resetTone();
        node.classList.add(`is-${tone}`);
        node.textContent = text;
        container.scrollTop = container.scrollHeight;
    };

    const startDots = (baseText, tone = 'info') => {
        if (ticker) {
            clearInterval(ticker);
            ticker = null;
        }
        resetTone();
        node.classList.add(`is-${tone}`);
        const dots = ['.', '..', '...'];
        let i = 0;
        node.textContent = `${baseText}${dots[i]}`;
        ticker = setInterval(() => {
            i = (i + 1) % dots.length;
            node.textContent = `${baseText}${dots[i]}`;
        }, 420);
        container.scrollTop = container.scrollHeight;
    };

    const stopDots = () => {
        if (!ticker) return;
        clearInterval(ticker);
        ticker = null;
    };

    const clear = () => {
        stopDots();
        node.remove();
    };

    return { set, startDots, stopDots, clear };
}
