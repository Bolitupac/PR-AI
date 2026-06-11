<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.meta', [
        'metaTitle' => 'PR ai | Auditor — AI Code Review Workspace',
        'metaDescription' => 'Import pull requests, branch diffs, and commits into the AI-powered Auditor. Get structured VAPT security audits, OWASP Top 10 analysis, Mermaid diagrams, and context-aware code reviews.',
        'metaType' => 'website',
        'metaRobots' => 'noindex, nofollow',
    ])
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
    <style>
        .doc-gen-glass {
            padding: 4px 10px; 
            display: flex; 
            align-items: center; 
            gap: 4px;
            border: 1px solid rgba(250, 204, 21, 0.3); 
            border-radius: 9999px; 
            background: rgba(250, 204, 21, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            color: #eab308;
            transition: all 0.2s ease;
        }
        [data-theme="dark"] .doc-gen-glass {
            border-color: rgba(250, 204, 21, 0.35); 
            background: rgba(250, 204, 21, 0.15);
            color: #fde047;
        }
        [data-theme="light"] .doc-gen-glass {
            border-color: rgba(234, 179, 8, 0.3); 
            background: rgba(253, 224, 71, 0.6);
            backdrop-filter: blur(16px);
            color: #854d0e;
        }
        .doc-gen-text {
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .doc-gen-icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            margin-left: -2px;
        }
        .doc-gen-icon {
            display: block;
            width: 15px;
            height: 15px;
            opacity: 0.9;
        }
        .doc-gen-close-btn {
            display: none;
            width: 22px; 
            height: 22px; 
            min-width: 22px; 
            padding: 0; 
            align-items: center; 
            justify-content: center; 
            border-radius: 50%; 
            background: transparent; 
            border: none; 
            cursor: pointer;
            transition: background 0.2s;
            color: inherit;
        }
        .doc-gen-close-btn:hover {
            background: rgba(128, 128, 128, 0.2);
        }
        .doc-gen-close-svg {
            width: 14px; 
            height: 14px; 
        }

        .doc-gen-glass:hover .doc-gen-icon { display: none; }
        .doc-gen-glass:hover .doc-gen-close-btn { display: flex; }

        .chat-input-wrap .doc-gen-chip-wrap {
            position: absolute;
            right: 48px;
            top: 50%;
            transform: translateY(-50%);
            margin: 0 !important;
            z-index: 10;
        }
        
        .doc-gen-chip-wrap.is-hidden {
            display: none !important;
        }

        .chat-container:not(.is-active) .doc-gen-glass { padding: 4px; gap: 0; border-color: transparent; background: transparent; box-shadow: none; border-radius: 50%; justify-content: center; }
        .chat-container:not(.is-active) .doc-gen-text { display: none; }

        @media (max-width: 768px) {
            .doc-gen-text { display: none; }
            .doc-gen-glass { padding: 4px; gap: 0; justify-content: center; border-radius: 50%; }
        }
    </style>
</head>

<body>
    @include('partials.mobile-hamburger')
    <div class="app-shell">
        <section class="hero-view">
            @include('partials.sidebar')

            <main class="main-workspace">
                <section class="ai-panel">
                    <div class="ai-content">
                        @php
                            $chatModels = config('openai.chat_models', [config('openai.model', 'gpt-4o-mini')]);
                            $defaultChatModel = config('openai.model', 'gpt-4o-mini');
                            $deepseekModels = config('deepseek.chat_models', [config('deepseek.model', 'deepseek-chat')]);
                            $defaultDeepSeekModel = config('deepseek.model', 'deepseek-chat');
                            $providerModels = [
                                'openai' => ['models' => $chatModels, 'default' => $defaultChatModel],
                                'deepseek' => ['models' => $deepseekModels, 'default' => $defaultDeepSeekModel],
                            ];
                        @endphp
                        <header class="panel-head ai-head">
                            <h3>
                                <a href="/" title="Go back to landing page" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:inherit;">
                                    <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo"
                                        class="brand-logo brand-logo--dark-on-light">
                                    <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo"
                                        class="brand-logo brand-logo--light-on-dark">
                                    <span>PR ai</span>
                                </a>
                            </h3>
                            <div class="panel-actions">
                                <div class="import-hover" id="import-trigger-wrap">
                                    <button class="repo-upload-btn" id="import-trigger" type="button">Import</button>
                                    @include('partials.import-hover-menu')
                                </div>
                                <div class="hint-wrap hint-wrap--model">
                                    <div class="import-hover" id="provider-select-wrap">
                                        <button class="chat-provider-trigger" id="provider-trigger" type="button">OpenAI</button>
                                        <div class="import-hover-menu">
                                            <button class="import-hover-item provider-choice-item" type="button" data-value="openai">
                                                <span class="import-hover-label">OpenAI</span>
                                            </button>
                                            <button class="import-hover-item provider-choice-item" type="button" data-value="deepseek">
                                                <span class="import-hover-label">DeepSeek</span>
                                            </button>
                                        </div>
                                    </div>
                                    <select class="chat-model-select" id="chat-provider-select" aria-label="Select AI provider" style="display: none;">
                                        <option value="openai">OpenAI</option>
                                        <option value="deepseek">DeepSeek</option>
                                    </select>
                                    <span class="tiny-action-hint" role="tooltip">Select AI provider</span>
                                </div>
                                <div class="hint-wrap hint-wrap--model">
                                    <div class="import-hover" id="model-select-wrap">
                                        <button class="chat-model-trigger" id="model-trigger" type="button">{{ $defaultChatModel }}</button>
                                        <div class="import-hover-menu" id="model-choices-container">
                                            @foreach ($chatModels as $chatModel)
                                                <button class="import-hover-item model-choice-item" type="button" data-value="{{ $chatModel }}">
                                                    <span class="import-hover-label">{{ $chatModel }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <select class="chat-model-select" id="chat-model-select" aria-label="Select model" style="display: none;">
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
                        <script>
                            window.__providerModels = @json($providerModels);
                        </script>

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
                        
                        <button id="diff-ready-scroll-btn" style="position: absolute; top: 80px; right: 24px; z-index: 50; background: #ef4444; color: white; border: none; border-radius: 99px; padding: 6px 14px; font-weight: 600; font-size: 13px; cursor: pointer; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); display: none; transition: opacity 0.2s;" aria-label="Scroll to bottom">Diff Ready ↓</button>
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
                            <div id="doc-gen-chip-wrap" class="doc-gen-chip-wrap is-hidden" style="margin-left: 8px;">
                                <div class="doc-gen-glass">
                                    <div class="doc-gen-icon-container">
                                        <svg class="doc-gen-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2-2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        <button id="doc-gen-close-btn" class="doc-gen-close-btn" type="button" aria-label="Close">
                                            <svg class="doc-gen-close-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                                <path d="M18 6L6 18M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <span class="doc-gen-text">DocGen</span>
                                </div>
                            </div>
                            <div class="credits-indicator-wrap" id="credits-indicator-wrap">
                                <button class="action-btn ghost credits-dots-btn" id="credits-dots-btn" type="button"
                                    aria-label="AI calls remaining" title="AI calls remaining"
                                    data-credits-url="{{ route('profile.ai-key.credits') }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                                        <circle cx="12" cy="5" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="12" cy="19" r="2"/>
                                    </svg>
                                </button>
                                <div class="credits-popover" id="credits-popover" aria-hidden="true">
                                    <span class="credits-popover-text" id="credits-popover-text">—</span>
                                </div>
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
    @include('partials.settings-modal')
    @include('partials.apps-modal')
    @include('partials.mobile-redirect')

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
