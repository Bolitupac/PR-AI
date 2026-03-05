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
    const recordChip = document.getElementById('voice-record-chip');
    const recordTimer = document.getElementById('voice-record-timer');
    const micButtonFab = document.getElementById('mic-btn-fab');
    const recordChipFab = document.getElementById('voice-record-chip-fab');
    const recordTimerFab = document.getElementById('voice-record-timer-fab');
    const voiceFab = document.getElementById('voice-fab');

    const transcribeUrl = document.getElementById('send-btn')?.dataset.transcribeUrl || '/api/ai/transcribe';
    const responseArea = document.getElementById('ai-response-area');

    if (!micButton || !recordChip || !recordTimer || !responseArea) return;

    const controls = [
        {
            chip: recordChip,
            button: micButton,
            timer: recordTimer,
            icon: micButton.querySelector('img'),
        },
    ];

    if (micButtonFab && recordChipFab && recordTimerFab) {
        controls.push({
            chip: recordChipFab,
            button: micButtonFab,
            timer: recordTimerFab,
            icon: micButtonFab.querySelector('img'),
        });
    }

    const recorder = createVoiceRecorder();
    let status = null;
    let handling = false;
    let timerRef = null;
    let startedAt = 0;

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

    const updateIcons = (recording) => {
        controls.forEach((control) => {
            if (!control.icon) return;
            const micIcon = control.icon.dataset.micIcon || control.icon.src;
            const sendIcon = control.icon.dataset.sendIcon || control.icon.src;
            control.icon.src = recording ? sendIcon : micIcon;
            control.icon.alt = recording ? 'Send voice' : 'Mic';
        });
    };

    const updateTimers = (text) => {
        controls.forEach((control) => {
            control.timer.textContent = text;
        });
    };

    const startTimer = () => {
        if (timerRef) {
            clearInterval(timerRef);
            timerRef = null;
        }
        startedAt = Date.now();
        updateTimers('00:00');
        timerRef = setInterval(() => {
            const elapsed = Math.floor((Date.now() - startedAt) / 1000);
            updateTimers(formatElapsed(elapsed));
        }, 500);
    };

    const stopTimer = () => {
        if (timerRef) {
            clearInterval(timerRef);
            timerRef = null;
        }
        updateTimers('00:00');
    };

    const setRecordingUi = (recording) => {
        controls.forEach((control) => {
            control.chip.classList.toggle('is-recording', recording);
            control.button.classList.toggle('is-stop', recording);
            control.button.setAttribute('aria-label', recording ? 'Send voice recording' : 'Start voice recording');
        });
        updateIcons(recording);
        if (recording) startTimer();
        else stopTimer();
    };

    const handleToggle = async () => {
        if (recorder.isRecording()) {
            await stopRecordingFlow();
            return;
        }
        await startRecordingFlow();
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
            setRecordingUi(true);
            statusRef.startDots('Microphone ready. Recording', 'info');
        } catch (error) {
            statusRef.set(mapRecorderError(error), 'error');
            setRecordingUi(false);
        } finally {
            handling = false;
        }
    };

    const stopRecordingFlow = async () => {
        const statusRef = getStatus();
        if (handling) return;
        handling = true;

        setRecordingUi(false);
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
            statusRef.set(error?.message || 'Transcription request failed.', 'error');
        } finally {
            handling = false;
        }
    };

    const showFab = (visible) => {
        if (!voiceFab) return;
        voiceFab.classList.toggle('is-visible', visible);
    };

    if (voiceFab) {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                ([entry]) => showFab(!entry.isIntersecting),
                { threshold: 0.12 }
            );
            observer.observe(recordChip);
        } else {
            const syncFallback = () => {
                const rect = recordChip.getBoundingClientRect();
                const inView = rect.bottom > 0 && rect.top < window.innerHeight;
                showFab(!inView);
            };
            window.addEventListener('scroll', syncFallback, { passive: true });
            window.addEventListener('resize', syncFallback);
            syncFallback();
        }
    }

    controls.forEach((control) => {
        control.chip.setAttribute('role', 'button');
        control.chip.setAttribute('tabindex', '0');
        control.chip.addEventListener('click', () => {
            handleToggle();
        });
        control.chip.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                handleToggle();
            }
        });
        control.button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            handleToggle();
        });
    });

    setRecordingUi(false);
}

