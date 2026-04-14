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
    <title>Git PULL Assistant | Reports</title>
    @vite(['resources/css/reports-ui.css', 'resources/js/app.js'])
</head>

<body>
    <div class="app-shell sidebar-expanded reports-shell" id="reports-page">
        @include('partials.sidebar')

        <main class="reports-workspace">
            <section class="reports-topbar">
                <div>
                    <h1 class="reports-title">Report Generator</h1>
                    <p class="reports-subtitle">Demo mode. Pick a repo, choose sections, arrange the order, and chat with AI while the draft grows.</p>
                </div>

                <label class="reports-repo-picker">
                    <span>Repository</span>
                    <select id="reports-repo-select" aria-label="Select repository" data-repos-url="{{ route('github.repos') }}">
                        <option selected disabled>Loading repositories...</option>
                    </select>
                </label>
            </section>

            <section class="reports-grid">
                <aside class="reports-panel reports-sections-panel">
                    <div class="reports-panel-head">
                        <div>
                            <h2>Sections to Include</h2>
                            <p>Tick the parts you want, drag to reorder them, and add more sections in demo mode.</p>
                        </div>
                    </div>

                    <div class="reports-section-list auto-hide-scrollbar" id="reports-section-list">
                        <article class="reports-section-item" draggable="true">
                            <span class="reports-section-number">1</span>
                            <label class="reports-section-toggle">
                                <input type="checkbox" checked>
                                <span>Executive Summary</span>
                            </label>
                            <button type="button" class="reports-drag-handle" aria-label="Drag section">⋮⋮</button>
                        </article>

                        <article class="reports-section-item" draggable="true">
                            <span class="reports-section-number">2</span>
                            <label class="reports-section-toggle">
                                <input type="checkbox" checked>
                                <span>System Architecture</span>
                            </label>
                            <button type="button" class="reports-drag-handle" aria-label="Drag section">⋮⋮</button>
                        </article>

                        <article class="reports-section-item" draggable="true">
                            <span class="reports-section-number">3</span>
                            <label class="reports-section-toggle">
                                <input type="checkbox" checked>
                                <span>Database Design</span>
                            </label>
                            <button type="button" class="reports-drag-handle" aria-label="Drag section">⋮⋮</button>
                        </article>

                        <article class="reports-section-item" draggable="true">
                            <span class="reports-section-number">4</span>
                            <label class="reports-section-toggle">
                                <input type="checkbox" checked>
                                <span>APIs and Integrations</span>
                            </label>
                            <button type="button" class="reports-drag-handle" aria-label="Drag section">⋮⋮</button>
                        </article>

                        <article class="reports-section-item" draggable="true">
                            <span class="reports-section-number">5</span>
                            <label class="reports-section-toggle">
                                <input type="checkbox">
                                <span>Testing Strategy</span>
                            </label>
                            <button type="button" class="reports-drag-handle" aria-label="Drag section">⋮⋮</button>
                        </article>

                        <article class="reports-section-item" draggable="true">
                            <span class="reports-section-number">6</span>
                            <label class="reports-section-toggle">
                                <input type="checkbox" checked>
                                <span>Risks and Recommendations</span>
                            </label>
                            <button type="button" class="reports-drag-handle" aria-label="Drag section">⋮⋮</button>
                        </article>
                    </div>

                    <div class="reports-section-footer">
                        <button type="button" class="reports-add-btn" id="reports-add-section-btn">Add Section</button>
                    </div>
                </aside>

                <section class="reports-center-column">
                    <article class="reports-panel reports-draft-panel">
                        <div class="reports-panel-head">
                            <div>
                                <h2>Document Draft</h2>
                                <p>The report body grows here. You can tweak the content directly in demo mode.</p>
                            </div>
                            <span class="reports-status-badge">Draft v0.3</span>
                        </div>

                        <div class="reports-draft-body auto-hide-scrollbar">
                            <section class="reports-draft-block">
                                <div class="reports-draft-heading">1. Executive Summary</div>
                                <div class="reports-draft-copy" contenteditable="true" spellcheck="false">
                                    This report summarizes the current repository structure, delivery risk, system design,
                                    and integration footprint. The draft is intended to become a polished engineering
                                    document after a few AI-assisted iterations.
                                </div>
                            </section>

                            <section class="reports-draft-block">
                                <div class="reports-draft-heading">2. System Architecture</div>
                                <div class="reports-draft-copy" contenteditable="true" spellcheck="false">
                                    The platform combines a Laravel backend, a Vite frontend, GitHub import workflows,
                                    and OpenAI-supported analysis features. The report generator should eventually turn
                                    this context into a downloadable technical document for engineers and stakeholders.
                                </div>
                            </section>

                            <section class="reports-draft-block">
                                <div class="reports-draft-heading">3. Database Design</div>
                                <div class="reports-draft-copy" contenteditable="true" spellcheck="false">
                                    Current persistence focuses on authentication, GitHub connectivity, and AI settings.
                                    A future report workflow may add draft version history, export logs, and cloud sync
                                    metadata for traceability.
                                </div>
                            </section>
                        </div>
                    </article>

                    <article class="reports-panel reports-chat-panel">
                        <div class="reports-chat-thread auto-hide-scrollbar">
                            <div class="reports-chat-msg user">Generate a technical report for this repo focused on architecture and APIs.</div>
                            <div class="reports-chat-msg ai">I can do that. Pick the sections you want on the left, then I’ll help shape the draft section by section.</div>
                            <div class="reports-chat-msg ai">You can also ask for an executive tone, compliance formatting, or a stronger database narrative.</div>
                        </div>

                        <div class="chat-container" style="border-top: none;">
                            <div class="chat-input-wrap">
                                <textarea class="chat-input" id="reports-chat-input" rows="1" placeholder="Ask AI..."></textarea>
                                <button class="action-btn input-send-btn" id="reports-send-btn" type="button" aria-label="Send">
                                    <img src="{{ asset('images/send.png') }}" alt="Send" class="action-icon">
                                </button>
                            </div>
                            <div class="chat-tools-row">
                                <div class="import-hover import-hover--plus">
                                    <button class="action-btn ghost import-plus-btn" type="button" aria-label="Import options">
                                        <svg class="plus-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>

                <aside class="reports-panel reports-actions-panel">
                    <div class="reports-panel-head">
                        <div>
                            <h2>Actions</h2>
                            <p>Quick export and report workflow actions for the current draft.</p>
                        </div>
                    </div>

                    <div class="reports-actions-list">
                        <button type="button" class="reports-action-card is-primary">
                            <span class="reports-action-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M7 3.8h7.4L19 8.4v10.8A1.8 1.8 0 0 1 17.2 21H7a1.8 1.8 0 0 1-1.8-1.8V5.6A1.8 1.8 0 0 1 7 3.8Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                    <path d="M14.4 3.8v4.6H19M8.5 15.3h7M8.5 18h5.2M8.5 12.6h7.8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="reports-action-copy">
                                <strong>Download PDF</strong>
                                <span>Export the current draft as a polished PDF.</span>
                            </span>
                        </button>

                        <button type="button" class="reports-action-card">
                            <span class="reports-action-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="4.2" y="3.8" width="15.6" height="16.4" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M8 9.2 9.4 15l1.8-4.2L13 15l1.6-5.8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="reports-action-copy">
                                <strong>Download Word Document</strong>
                                <span>Generate a `.docx` version for editing outside the app.</span>
                            </span>
                        </button>

                        <button type="button" class="reports-action-card">
                            <span class="reports-action-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M4.2 6.2A1.8 1.8 0 0 1 6 4.4h12a1.8 1.8 0 0 1 1.8 1.8v11.6a1.8 1.8 0 0 1-1.8 1.8H6a1.8 1.8 0 0 1-1.8-1.8V6.2Z" fill="none" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="m7.8 14.8 2.2-5 2.1 5 2.1-5 2.1 5M7 16.8h10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="reports-action-copy">
                                <strong>Download Markdown</strong>
                                <span>Save a markdown copy for docs or GitHub wiki sync.</span>
                            </span>
                        </button>

                        <button type="button" class="reports-action-card">
                            <span class="reports-action-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M2.8 12s3.6-5.8 9.2-5.8 9.2 5.8 9.2 5.8-3.6 5.8-9.2 5.8S2.8 12 2.8 12Z" fill="none" stroke="currentColor" stroke-width="1.6"/>
                                    <circle cx="12" cy="12" r="2.6" fill="none" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </span>
                            <span class="reports-action-copy">
                                <strong>Preview Report</strong>
                                <span>Open a large preview of the generated document layout.</span>
                            </span>
                        </button>

                        <button type="button" class="reports-action-card">
                            <span class="reports-action-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M7 8V4.8h10V8M7 15.8H5.4A1.6 1.6 0 0 1 3.8 14.2V9.8a1.6 1.6 0 0 1 1.6-1.6h13.2a1.6 1.6 0 0 1 1.6 1.6v4.4a1.6 1.6 0 0 1-1.6 1.6H17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                    <rect x="7" y="13.2" width="10" height="6" rx="1.4" fill="none" stroke="currentColor" stroke-width="1.6"/>
                                </svg>
                            </span>
                            <span class="reports-action-copy">
                                <strong>Print</strong>
                                <span>Prepare the report for immediate printing or PDF printer export.</span>
                            </span>
                        </button>

                        <button type="button" class="reports-action-card">
                            <span class="reports-action-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M7.2 18.2a3.2 3.2 0 1 1 .5-6.3 4.8 4.8 0 0 1 9.1-1.4A3.5 3.5 0 1 1 18 18.2H7.2Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 10.5v6.4m0 0-2.2-2.2M12 16.9l2.2-2.2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="reports-action-copy">
                                <strong>Sync to Cloud</strong>
                                <span>Push this report to a GitHub wiki or docs destination later.</span>
                            </span>
                        </button>
                    </div>
                </aside>
            </section>
        </main>
    </div>

    @include('partials.settings-modal')
</body>

</html>
