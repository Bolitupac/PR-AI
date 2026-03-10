// Simple percent-based progress ticker for async fetch operations.
export function createLoadingProgress({ onUpdate, label = 'Loading', start = 6, max = 92, step = 7, intervalMs = 360 }) {
    let percent = start;
    let timer = null;

    const tick = () => {
        percent = Math.min(max, percent + step);
        if (onUpdate) onUpdate(`${label} ${percent}%`);
    };

    if (onUpdate) onUpdate(`${label} ${percent}%`);
    timer = setInterval(tick, intervalMs);

    return {
        stop(finalLabel = '') {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
            if (finalLabel && onUpdate) {
                onUpdate(finalLabel);
            }
        }
    };
}

