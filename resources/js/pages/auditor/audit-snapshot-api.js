// Saves the current audit context into backend newfile.txt snapshot.
export async function saveAuditSnapshot(snapshotUrl, payload) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const res = await fetch(snapshotUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        throw new Error(data?.message || 'Failed to save snapshot');
    }

    return data;
}

