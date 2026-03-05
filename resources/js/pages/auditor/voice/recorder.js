const PREFERRED_MIME_TYPES = [
    'audio/webm;codecs=opus',
    'audio/webm',
    'audio/ogg;codecs=opus',
    'audio/ogg',
];

function getSupportedMimeType() {
    if (typeof MediaRecorder === 'undefined') return '';
    for (const type of PREFERRED_MIME_TYPES) {
        if (MediaRecorder.isTypeSupported(type)) return type;
    }
    return '';
}

export function createVoiceRecorder() {
    let mediaStream = null;
    let mediaRecorder = null;
    let chunks = [];
    let recording = false;

    const startRecording = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Browser does not support microphone access.');
        }
        if (typeof MediaRecorder === 'undefined') {
            throw new Error('Browser does not support recording.');
        }
        if (recording) return;

        const mimeType = getSupportedMimeType();
        mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        chunks = [];
        mediaRecorder = mimeType ? new MediaRecorder(mediaStream, { mimeType }) : new MediaRecorder(mediaStream);

        mediaRecorder.addEventListener('dataavailable', (event) => {
            if (event.data && event.data.size > 0) {
                chunks.push(event.data);
            }
        });

        mediaRecorder.start(250);
        recording = true;
    };

    const stopRecording = async () => {
        if (!mediaRecorder || !recording) {
            throw new Error('Recorder is not active.');
        }

        const done = new Promise((resolve) => {
            mediaRecorder.addEventListener('stop', () => {
                const blob = new Blob(chunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                resolve(blob);
            }, { once: true });
        });

        mediaRecorder.stop();
        mediaStream?.getTracks().forEach((track) => track.stop());
        mediaStream = null;
        recording = false;

        const blob = await done;
        mediaRecorder = null;
        chunks = [];
        return blob;
    };

    return {
        startRecording,
        stopRecording,
        isRecording: () => recording,
    };
}

