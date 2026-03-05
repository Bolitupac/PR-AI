import { createVoiceRecorder } from './recorder';
import { transcribeAudioBlob } from './transcribe-api';
import { createVoiceStatus } from './voice-status';
import { sendTextToChat, isChatBusy } from '../chat-input';

function mapRecorderError(error) {
    const name = error?.name || '';
    if (name === 'NotAllowedError' || name === 'SecurityError') return 'Microphone permission denied.';
    if (name === 'NotFoundError') return 'No microphone detected.';
    if (name === 'NotReadableError') return 'Microphone is busy or unavailable.';
    return error?.message || 'Could not start microphone recording.';
}

export function initVoiceController() {
    const micButton = document.getElementById('mic-btn');
    const transcribeUrl = document.getElementById('send-btn')?.dataset.transcribeUrl || '/api/ai/transcribe';
    const responseArea = document.getElementById('ai-response-area');
    const recordChip = document.getElementById('voice-record-chip');
    const recordTimer = document.getElementById('voice-record-timer');

    if (!micButton || !responseArea || !recordChip || !recordTimer) return;

    const recorder = createVoiceRecorder();
    let status = null;
    let handling = false;
    let startedAt = 0;
    let timerRef = null;

    const getStatus = () => {
        if (!status) {
            status = createVoiceStatus(responseArea);
        }
        return status;
    };

    const formatElapsed = (seconds) => {
        const mins = String(Math.floor(seconds / 60)).padStart(2, '0');
        const secs = String(seconds % 60).padStart(2, '0');
        return `${mins}:${secs}`;
    };

    const startTimer = () => {
        stopTimer();
        startedAt = Date.now();
        recordTimer.textContent = '00:00';
        timerRef = setInterval(() => {
            const elapsedSeconds = Math.floor((Date.now() - startedAt) / 1000);
            recordTimer.textContent = formatElapsed(elapsedSeconds);
        }, 500);
    };

    const stopTimer = () => {
        if (timerRef) {
            clearInterval(timerRef);
            timerRef = null;
        }
        recordTimer.textContent = '00:00';
    };

    const setRecordingButton = (recording) => {
        micButton.classList.toggle('is-stop', recording);
        recordChip.classList.toggle('is-recording', recording);
        micButton.setAttribute('aria-label', recording ? 'Stop recording' : 'Start voice recording');
        if (recording) {
            startTimer();
        } else {
            stopTimer();
        }
    };

    const startRecordingFlow = async () => {
        const statusRef = getStatus();
        if (handling || isChatBusy()) {
            statusRef.set('AI is busy. Wait for current response to finish.', 'error');
            return;
        }
        handling = true;
        statusRef.set('Requesting microphone...', 'info');
        try {
            await recorder.startRecording();
            setRecordingButton(true);
            statusRef.startDots('Microphone ready. Recording', 'info');
        } catch (error) {
            statusRef.set(mapRecorderError(error), 'error');
            setRecordingButton(false);
        } finally {
            handling = false;
        }
    };

    const stopRecordingFlow = async () => {
        const statusRef = getStatus();
        if (handling) return;
        handling = true;
        setRecordingButton(false);
        statusRef.stopDots();
        statusRef.set('Recording stopped. Preparing audio...', 'info');

        try {
            const blob = await recorder.stopRecording();
            if (!blob || blob.size === 0) {
                statusRef.set('Mic audio is empty.', 'error');
                return;
            }

            statusRef.startDots('Sending audio for transcription', 'info');
            const text = await transcribeAudioBlob({
                blob,
                url: transcribeUrl,
                model: 'gpt-4o-mini-transcribe',
                language: 'en',
            });
            statusRef.stopDots();
            statusRef.set('Transcription received. Sending to AI...', 'success');

            const sent = await sendTextToChat(text, { source: 'voice' });
            if (sent) {
                statusRef.set('Voice message sent.', 'success');
                setTimeout(() => {
                    statusRef.clear();
                    status = null;
                }, 900);
            } else {
                statusRef.set('Could not send transcribed message.', 'error');
            }
        } catch (error) {
            const message = error?.message || 'Transcription request failed.';
            statusRef.set(message, 'error');
        } finally {
            handling = false;
        }
    };

    micButton.addEventListener('click', () => {
        if (recorder.isRecording()) {
            stopRecordingFlow();
            return;
        }
        startRecordingFlow();
    });
}
