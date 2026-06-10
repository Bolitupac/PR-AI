import { runTutorial, welcomeStep, step } from '../../shared/tutorial';

const TERMS_URL = '/terms';

/**
 * Auditor Page Tutorial Steps
 */
export function startAuditorTutorial() {
    const steps = [
        welcomeStep({
            title: 'Welcome to PR-AI 👋',
            subtitle: 'Your AI-powered pull request auditing assistant',
            text: 'This short walkthrough will introduce the core features of the Auditor workspace. You\'ll learn how to chat with the AI, import code, and navigate the review environment.',
            termsUrl: TERMS_URL,
        }),

        // Step 1: Chat Box
        step(
            'Chat Box',
            '.chat-input-wrap',
            'This is the chat box. You can enter prompts, ask coding questions, request code reviews, analyze pull requests, and interact with PR-AI. Type your message and press Enter or click the send button.',
            { placement: 'top' }
        ),

        // Step 2: Voice Input (Mic Button)
        step(
            'Voice Input',
            '#voice-record-chip',
            'Use the microphone button to speak directly with PR-AI. Voice input is automatically transcribed into text for faster interactions. Click to start recording, click again or pause to send.',
            { placement: 'top' }
        ),

        // Step 3: Import Button (+)
        step(
            'Import Button',
            '#import-plus-trigger',
            'The import button (➕) lets you upload files and import code. You can upload diff/patch files, paste code directly in a Monaco editor, or import from repository providers like GitHub and GitLab.',
            { placement: 'top' }
        ),

        // Step 4: Send Button
        step(
            'Send Button',
            '#send-btn',
            'Once your prompt is ready, click the send button to begin your conversation with PR-AI. The AI will process your request and stream the response in real time.',
            { placement: 'left' }
        ),

        // Step 5: Response Area
        step(
            'Response Area',
            '#ai-response-area',
            'This area displays PR-AI\'s responses, recommendations, audit findings, security scores, OWASP Top 10 coverage, Mermaid diagrams, and interactive follow-up suggestions.',
            { placement: 'top' }
        ),

        // Step 6: Navigation Sidebar
        step(
            'Navigation Sidebar',
            '#app-sidebar',
            'Use the sidebar to navigate between the Auditor workspace, Imports page, and Apps gallery. Access settings, toggle dark/light theme, view your profile, and browse saved chat history.',
            { placement: 'right' }
        ),

        // Step 7: AI Provider & Model Selectors
        step(
            'AI Provider & Model',
            '#provider-select-wrap',
            'Switch between AI providers (OpenAI and DeepSeek) and choose your preferred model (GPT-4o, GPT-4o-mini, DeepSeek-Chat). Your personal API key can be configured in Settings → API Keys.',
            { placement: 'bottom' }
        ),

        // Step 8: Import Page Transition
        step(
            'Continue to Import Page',
            '#app-sidebar',
            'Now that you\'ve seen the Auditor workspace, it\'s time to learn how to import repositories and files. Click "Start Import Tutorial" to continue to the Imports page walkthrough.',
            {
                placement: 'right',
                nextLabel: 'Start Import Tutorial →',
            }
        ),
    ];

    return runTutorial(steps, {
        page: 'auditor',
        onComplete: (completed) => {
            if (completed && steps.length > 0) {
                // If the last step was reached, redirect to imports with tutorial flag
                const lastStep = steps[steps.length - 1];
                // Check if user wants to continue to imports tutorial
            }
        },
    });
}
