export async function transcribeAudioBlob({
    blob,
    url,
    model = 'gpt-4o-mini-transcribe',
    language = 'en',
}) {
    if (!blob || blob.size === 0) {
        throw new Error('Mic audio is empty.');
    }

    const formData = new FormData();
    formData.append('audio', blob, 'voice-input.webm');
    formData.append('model', model);
    formData.append('language', language);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: formData,
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(data?.message || 'Transcription request failed.');
    }

    const text = String(data?.text || '').trim();
    if (!text) {
        throw new Error('No speech recognized from recording.');
    }
    return text;
}

