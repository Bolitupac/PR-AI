import { createChatStatus } from '../chat-status.js';

export async function exportDocGenDocument({ format, title, markdown, responseArea }) {
    if (!markdown || !responseArea) return false;

    const status = createChatStatus({ container: responseArea, anchorNode: null });

    try {
        status.startDots(`Preparing ${format.toUpperCase()} export`);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/api/ai/docgen/export', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/octet-stream',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                format,
                title,
                markdown,
            }),
            credentials: 'same-origin',
        });

        if (!res.ok) {
            const payload = await res.json().catch(() => ({}));
            throw new Error(payload?.message || 'Export failed.');
        }

        const blob = await res.blob();
        const downloadUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        const disposition = res.headers.get('Content-Disposition') || '';
        const match = disposition.match(/filename="?([^"]+)"?/i);
        link.href = downloadUrl;
        link.download = match?.[1] || `${title}.${format}`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(downloadUrl);
        status.markSuccess(`${format.toUpperCase()} export ready.`);
        status.remove(900);
        return true;
    } catch (error) {
        status.markError(error?.message || 'Export failed.');
        return false;
    }
}
