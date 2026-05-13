<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="PR ai helps teams import pull requests, inspect diffs, and generate AI-powered code reviews in a fast, collaborative workspace.">
    <title>PR ai | AI Pull Request Reviews</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo 512 transp bg white color svg.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geologica:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/homepage.css', 'resources/js/homepage.js'])
</head>

<body>
    @php
        $toolEntryUrl = auth()->check() ? route('auditor.index') : route('login');
        $featureCards = [
            [
                'title' => 'Import pull requests from GitHub',
                'description' => 'Pull live repositories, branches, pull requests, and recent activity into one clean workspace without opening ten browser tabs.',
                'image' => 'images/homepage/prai import pull requests from repos.png',
            ],
            [
                'title' => 'Review diffs inside the app',
                'description' => 'Use the built-in diff view to inspect changes, follow context, and keep the whole review flow inside PR ai.',
                'image' => 'images/homepage/viewgit diff with our inbuilt diff to html.png',
            ],
            [
                'title' => 'Chat directly with the audit',
                'description' => 'Ask the AI follow-up questions against the active review context and get answers grounded in the imported change.',
                'image' => 'images/homepage/PRAI audit page "chat with the ai good ui".png',
            ],
            [
                'title' => 'Read comments without leaving the review',
                'description' => 'Surface pull request discussion and line comments in-app so reviewers can keep momentum without bouncing back to GitHub.',
                'image' => 'images/homepage/viewcommentsinappwithoutnavigationg torepo.png',
            ],
            [
                'title' => 'Map architecture with Mermaid',
                'description' => 'Turn code changes into visual flow diagrams that help developers and stakeholders understand impact faster.',
                'image' => 'images/homepage/viewrepos structore with our inbuild flowchart using mermaid.png',
            ],
            [
                'title' => 'Work with your own OpenAI key',
                'description' => 'Teams can run on a shared developer key or switch users to their own personal OpenAI key when needed.',
                'image' => 'images/homepage/useyour own openai key.png',
            ],
        ];

        $faqs = [
            [
                'question' => 'What can I import into PR ai?',
                'answer' => 'You can import GitHub pull requests, branch comparisons, local commits, uploaded diff files, pasted diffs, and editor content into the same audit workflow.',
            ],
            [
                'question' => 'Do I need my own OpenAI key to use it?',
                'answer' => 'Not necessarily. PR ai supports a shared developer key setup, and it also supports personal OpenAI keys for users who want their own billing or model control.',
            ],
            [
                'question' => 'Does it only summarize code, or can it actually review it?',
                'answer' => 'It is designed as a review tool. It builds audits from changed lines, diff context, PR metadata, and comments, then returns structured findings, risk signals, and follow-up guidance.',
            ],
            [
                'question' => 'Can it help before approval and after merge?',
                'answer' => 'Yes. PR ai is useful for pre-merge pull request reviews, branch audits, commit inspections, and post-merge retrospective analysis.',
            ],
            [
                'question' => 'Will it support more actions inside the review flow?',
                'answer' => 'Yes. The roadmap includes AI-generated inline code comments, extensive technical reports, test plans, QA documents, and instruction-driven commit and push workflows.',
            ],
        ];
    @endphp

    <div class="homepage-shell" id="homepage-shell">
        <div class="bg-grid" aria-hidden="true"></div>
        <div class="bg-orb bg-orb--one" aria-hidden="true"></div>
        <div class="bg-orb bg-orb--two" aria-hidden="true"></div>

        <header class="site-header">
            <a href="/" class="brandmark">
                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo">
                <span>PR ai</span>
            </a>
            <nav class="top-nav" aria-label="Primary">
                <a href="#features">Features</a>
                <a href="#docgen">DocGen</a>
                <a href="#help">Help</a>
                <a href="{{ $toolEntryUrl }}" class="nav-cta">Try it</a>
            </nav>
        </header>

        <main>
            <section class="hero" data-reveal>
                <div class="hero-interactive" id="hero-interactive" aria-hidden="true"></div>

                <div class="hero-window">
                    <div class="hero-watermark" aria-hidden="true">
                        <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="">
                    </div>

                    <div class="hero-window__nav">
                        <span class="hero-window__brand">PR ai</span>
                        <a href="{{ $toolEntryUrl }}" class="hero-window__cta">Try it</a>
                    </div>

                    <div class="hero-copy hero-copy--full">
                        <div class="eyebrow-row">
                            <span class="eyebrow-pill">AI review workspace</span>
                            <span class="eyebrow-note">Built for pull requests, diffs, and team velocity</span>
                        </div>

                        <h1>Cut PR review time by 80% using AI</h1>

                        <p class="hero-lead">
                            Import pull requests, branch diffs, commits, or pasted code into one focused workspace. PR ai
                            turns changes into structured reviews, visual impact maps, follow-up answers, and a smoother
                            path from diff to decision.
                        </p>

                        <div class="hero-actions">
                            <a href="{{ $toolEntryUrl }}" class="button button--primary">Try demo</a>
                            <a href="#features" class="button button--ghost">See features</a>
                        </div>
                    </div>

                    <div class="hero-floating hero-floating--left">
                        <span class="hero-floating__label">Import flow</span>
                        <strong>GitHub, branches, PRs, commits</strong>
                    </div>

                    <div class="hero-floating hero-floating--left-lower">
                        <span class="hero-floating__label">Reviewer context</span>
                        <strong>Comments, diffs, and active audit history</strong>
                    </div>

                    <div class="hero-floating hero-floating--right">
                        <span class="hero-floating__label">Structured output</span>
                        <strong>Audits, diagrams, reports, follow-ups</strong>
                    </div>

                    <div class="hero-floating hero-floating--right-lower">
                        <span class="hero-floating__label">DocGen mode</span>
                        <strong>Generate shareable docs and exportable PDFs</strong>
                    </div>

                    <div class="hero-floating hero-floating--bottom">
                        <span class="hero-floating__label">Team workflow</span>
                        <strong>Diffs, comments, DocGen mode, help docs</strong>
                    </div>
                </div>
            </section>

            <section class="stats-strip" aria-label="Product summary" data-reveal>
                <article class="stat-card" data-reveal>
                    <strong>GitHub to audit in one flow</strong>
                    <span>Import repositories, pull requests, branches, and comments without leaving the app.</span>
                </article>
                <article class="stat-card" data-reveal>
                    <strong>Structured reviews, not generic chat</strong>
                    <span>Get summaries, impact maps, walkthroughs, Mermaid diagrams, and risk-oriented findings.</span>
                </article>
                <article class="stat-card" data-reveal>
                    <strong>Built for iteration</strong>
                    <span>Keep the diff context active, ask follow-up questions, and move from review to action faster.</span>
                </article>
            </section>

            <section class="feature-overview" id="features" data-reveal>
                <div class="section-copy" data-reveal>
                    <span class="section-tag">Futuristic features. About time.</span>
                    <h2>One focused place for AI-assisted pull request reviews</h2>
                    <p>
                        PR ai is designed to reduce review friction. Instead of juggling GitHub tabs, pasted diffs,
                        chat windows, and handwritten notes, teams can import code changes and work through the whole
                        review in one polished flow.
                    </p>
                </div>

                <div class="feature-grid">
                    @foreach ($featureCards as $card)
                        <article class="feature-card" data-reveal>
                            <div class="feature-media">
                                <img src="{{ asset($card['image']) }}" alt="{{ $card['title'] }}">
                            </div>
                            <div class="feature-copy">
                                <h3>{{ $card['title'] }}</h3>
                                <p>{{ $card['description'] }}</p>
                            </div>
                        </article>
                    @endforeach

                    <article class="feature-card feature-card--docgen" data-reveal>
                        <div class="feature-media">
                            <img src="{{ asset('images/homepage/viewgitrepos, pullrequests, commitsetc in out import page.png') }}"
                                alt="PR ai import and app workflow">
                        </div>
                        <div class="feature-copy">
                            <h3>Turn on DocGen mode in one click</h3>
                            <p>Add DocGen to your workflow from the app gallery, then switch from audit chat into document generation without losing context.</p>
                        </div>
                    </article>
                </div>
            </section>

            <section class="docgen-section" id="docgen" data-reveal>
                <div class="section-copy" data-reveal>
                    <span class="section-tag">DocGen mode</span>
                    <h2>Generate a document, review it, and download the PDF</h2>
                    <p>
                        DocGen mode lets users move beyond code review summaries and create shareable documentation.
                        It can prepare structured project documents, show the generated output inside the workspace,
                        and present a clear download action for the final PDF.
                    </p>
                </div>

                <div class="docgen-layout">
                    <article class="docgen-card docgen-card--info" data-reveal>
                        <span class="docgen-card__tag">What users get today</span>
                        <h3>Import code, audit faster, ask better questions</h3>
                        <ul class="details-list">
                            <li>GitHub repository, branch, pull request, and comment import</li>
                            <li>Commit-level auditing from local git history</li>
                            <li>Structured AI review responses with findings and visual impact maps</li>
                            <li>Follow-up chat grounded in the active audit context</li>
                            <li>Voice-to-AI workflow for quick prompting during review</li>
                        </ul>
                    </article>

                    <article class="docgen-card docgen-card--preview" data-reveal>
                        <div class="doc-panel">
                            <img src="{{ asset('images/homepage/PRAI audit page "chat with the ai good ui".png') }}"
                                alt="PR ai DocGen preview and download experience">
                        </div>
                    </article>
                </div>
            </section>

            <section class="help-section" id="help" data-reveal>
                <div class="section-copy" data-reveal>
                    <span class="section-tag">Help section</span>
                    <h2>Give users a clear place to learn the workflow</h2>
                    <p>
                        The help area should explain how to import code, switch audit modes, use DocGen mode, and find the settings that matter most. This keeps the tool easier to understand for first-time users.
                    </p>
                </div>

                <div class="help-layout">
                    <article class="help-card help-card--menu" data-reveal>
                        <img src="{{ asset('images/homepage/useyour own openai key.png') }}"
                            alt="PR ai settings and help related interface">
                    </article>

                    <article class="help-card help-card--content" data-reveal>
                        <span class="help-kicker">DocGen Mode</span>
                        <h3>Show users how to move from audit chat to downloadable documentation</h3>
                        <ol class="help-steps">
                            <li>Click the DocGen option from the app gallery or chat controls.</li>
                            <li>Enter a request for the document you want the AI to prepare.</li>
                            <li>Review the generated output inside the document preview card.</li>
                            <li>Use the download button to export the PDF when the document is ready.</li>
                        </ol>
                    </article>
                </div>
            </section>

            <section class="comparison-section" id="comparison" data-reveal>
                <div class="section-copy" data-reveal>
                    <span class="section-tag">Comparison</span>
                    <h2>Why teams pick PR ai over the usual alternatives</h2>
                    <p>
                        Most teams still review code with a mix of GitHub tabs, pasted diffs, generic AI chat, and
                        manual note-taking. PR ai pulls those steps into one review environment built specifically for
                        pull requests and code audits.
                    </p>
                </div>

                <div class="comparison-card" data-reveal>
                    <div class="comparison-head comparison-grid">
                        <div>Capability</div>
                        <div class="comparison-head__brand">PR ai</div>
                        <div>Generic AI chat</div>
                        <div>GitHub only</div>
                        <div>Manual review flow</div>
                    </div>

                    <div class="comparison-row comparison-grid">
                        <div class="comparison-label">GitHub PR and branch import</div>
                        <div class="comparison-yes comparison-brand">Yes</div>
                        <div class="comparison-no">No native workflow</div>
                        <div class="comparison-partial">Yes</div>
                        <div class="comparison-no">No</div>
                    </div>

                    <div class="comparison-row comparison-grid">
                        <div class="comparison-label">AI reviews built for diffs</div>
                        <div class="comparison-yes comparison-brand">Purpose-built</div>
                        <div class="comparison-partial">Possible with prompting</div>
                        <div class="comparison-no">No</div>
                        <div class="comparison-no">No</div>
                    </div>

                    <div class="comparison-row comparison-grid">
                        <div class="comparison-label">Follow-up chat with active audit context</div>
                        <div class="comparison-yes comparison-brand">Yes</div>
                        <div class="comparison-partial">Only if you paste context manually</div>
                        <div class="comparison-no">No</div>
                        <div class="comparison-no">No</div>
                    </div>

                    <div class="comparison-row comparison-grid">
                        <div class="comparison-label">Visual logic maps and impact summaries</div>
                        <div class="comparison-yes comparison-brand">Yes</div>
                        <div class="comparison-partial">Manual prompting required</div>
                        <div class="comparison-no">No</div>
                        <div class="comparison-no">No</div>
                    </div>

                    <div class="comparison-row comparison-grid">
                        <div class="comparison-label">Single workspace for comments, diffs, and review flow</div>
                        <div class="comparison-yes comparison-brand">Yes</div>
                        <div class="comparison-no">No</div>
                        <div class="comparison-partial">Partial</div>
                        <div class="comparison-no">No</div>
                    </div>
                </div>
            </section>

            <section class="faq-section" id="faq" data-reveal>
                <div class="section-copy section-copy--faq" data-reveal>
                    <span class="section-tag">FAQ</span>
                    <h2>Questions users will probably ask first</h2>
                </div>

                <div class="faq-list">
                    @foreach ($faqs as $faq)
                        <details class="faq-item" data-reveal @if ($loop->first) open @endif>
                            <summary>{{ $faq['question'] }}</summary>
                            <p>{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="site-footer__inner">
                <div class="site-footer__brand">
                    <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo">
                    <div>
                        <p class="site-footer__title">PR ai</p>
                        <p class="site-footer__meta">Made by bolitupac</p>
                    </div>
                </div>
                <div class="site-footer__info">
                    <p>&copy; {{ now()->year }} PR ai. All rights reserved.</p>
                    <p>AI-assisted pull request reviews, diff audits, and technical review workflows.</p>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
