<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nabla&family=Science+Gothic:wght@400;600;700&display=swap"
        rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Nabla&family=Science+Gothic:wght@400;600;700&display=swap"
            rel="stylesheet">
    </noscript>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo 512 transp bg white color svg.svg') }}">
    <title>Git PULL Assistant | Auditor</title>
    @vite(['resources/css/auditor-ui.css', 'resources/js/app.js'])
</head>

<body>
    @php
        $vcsProviders = [
            ['name' => 'GitHub', 'state' => 'Connected'],
            ['name' => 'GitLab', 'state' => 'Available'],
            ['name' => 'Bitbucket', 'state' => 'Available'],
            ['name' => 'Azure DevOps', 'state' => 'Available'],
        ];
    @endphp

    <div class="app-shell">
        <section class="hero-view">
            @include('partials.sidebar')

            <main class="main-workspace">
                <section class="ai-panel">
                    <div class="ai-content">
                        @php
                            $chatModels = config('openai.chat_models', [config('openai.model', 'gpt-4o-mini')]);
                            $defaultChatModel = config('openai.model', 'gpt-4o-mini');
                        @endphp
                        <header class="panel-head ai-head">
                            <h3>
                                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo"
                                    class="brand-logo brand-logo--dark-on-light">
                                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo"
                                    class="brand-logo brand-logo--light-on-dark">
                                <span>PR ai</span>
                            </h3>
                            <div class="panel-actions">
                                <div class="import-hover" id="import-trigger-wrap">
                                    <button class="repo-upload-btn" id="import-trigger" type="button">Import</button>
                                    @include('partials.import-hover-menu')
                                </div>
                                <div class="hint-wrap hint-wrap--model">
                                    <select class="chat-model-select" id="chat-model-select" aria-label="Select model">
                                        @foreach ($chatModels as $chatModel)
                                            <option value="{{ $chatModel }}" @selected($chatModel === $defaultChatModel)>
                                                {{ $chatModel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="tiny-action-hint" role="tooltip">Select AI model</span>
                                </div>
                            </div>
                        </header>

                        <div id="ai-response-area" class="chat-demo-list">
                            <div class="chat-empty-state" id="chat-empty-state">
                                <h4 class="chat-empty-title">
                                    Hi,
                                    <span
                                        class="chat-empty-name">{{ auth()->user()->github_username ?? auth()->user()->name ?? 'there' }}</span>
                                </h4>
                                <p class="chat-empty-subtitle">Import a repo or diff, or paste code to start auditing.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="chat-container">
                        <div class="chat-input-wrap">
                            <textarea class="chat-input" id="user-prompt" rows="1" placeholder="Ask AI..."></textarea>
                            <button class="action-btn input-send-btn" id="send-btn" type="button" aria-label="Send"
                                data-chat-url="{{ route('ai.chat') }}"
                                data-chat-stream-url="{{ route('ai.chat.stream') }}"
                                data-transcribe-url="{{ route('ai.transcribe') }}">
                                <img src="{{ asset('images/send.png') }}" alt="Send" class="action-icon">
                            </button>
                        </div>
                        <div class="chat-tools-row">
                            <div class="voice-record-chip-wrap">
                                <div class="voice-record-chip" id="voice-record-chip">
                                    <button class="action-btn ghost" id="mic-btn" type="button" aria-label="Mic">
                                        <img src="{{ asset('images/mic.png') }}" alt="Mic" class="action-icon"
                                            data-mic-icon="{{ asset('images/mic.png') }}"
                                            data-send-icon="{{ asset('images/send.png') }}">
                                    </button>
                                    <span class="voice-record-timer" id="voice-record-timer">00:00</span>
                                </div>
                                <span class="tiny-action-hint" role="tooltip">Talk to the AI</span>
                            </div>
                            <div class="import-hover import-hover--plus" id="import-plus-wrap">
                                <button class="action-btn ghost import-plus-btn" id="import-plus-trigger" type="button"
                                    aria-label="Import options" aria-haspopup="true" aria-expanded="false">
                                    <svg class="plus-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.4"
                                            stroke-linecap="round" />
                                    </svg>
                                </button>
                                <span class="tiny-action-hint" role="tooltip">Upload or import file/repo</span>
                                @include('partials.import-hover-menu')
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </section>

        @include('partials.diff-viewer')
    </div>

    @include('partials.repo-import')
    @include('partials.diff-upload')
    @include('partials.import-monaco')
    @include('partials.import-paste')
    @include('partials.profile-modal')

    <div class="voice-fab" id="voice-fab">
        <div class="voice-record-chip voice-record-chip--fab" id="voice-record-chip-fab">
            <button class="action-btn ghost" id="mic-btn-fab" type="button" aria-label="Mic">
                <img src="{{ asset('images/mic.png') }}" alt="Mic" class="action-icon"
                    data-mic-icon="{{ asset('images/mic.png') }}" data-send-icon="{{ asset('images/send.png') }}">
            </button>
            <span class="voice-record-timer" id="voice-record-timer-fab">00:00</span>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js" defer></script>

</body>

</html>