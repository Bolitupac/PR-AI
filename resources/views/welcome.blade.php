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

        $visibleCards = array_slice($featureCards, 0, 2);
        $hiddenCards = array_slice($featureCards, 2);

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
                <a href="#providers">AI Providers</a>
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

                <div class="feature-grid" id="feature-grid">
                    @foreach ($visibleCards as $card)
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

                    <div class="feature-hidden-wrapper" id="feature-hidden-wrapper">
                        @foreach ($hiddenCards as $card)
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
                </div>

                <div class="see-more-wrap">
                    <button class="button button--ghost see-more-btn" id="see-more-btn" aria-expanded="false">
                        <span class="see-more-text">See more</span>
                        <span class="see-more-icon">&#8595;</span>
                    </button>
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

            <section class="providers-section" id="providers" data-reveal>
                <div class="section-copy" data-reveal>
                    <span class="section-tag">AI Providers</span>
                    <h2>Powered by industry-leading AI models</h2>
                    <p>
                        PR ai gives you the freedom to choose between the best AI providers. Switch between OpenAI and
                        DeepSeek on the fly — each connected directly through their official APIs, so you always get
                        the real thing.
                    </p>
                </div>

                <div class="providers-grid">
                    <article class="provider-card" data-reveal>
                        <div class="provider-card__inner">
                            <div class="provider-card__logo-wrap">
                                <svg class="provider-card__logo" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3.2c2 0 3.8 1 4.9 2.6 2.2.3 4 2.2 4 4.5 0 1.1-.4 2.2-1.1 3l.1.6c.1 2.7-2 5-4.7 5.2-.9 1.1-2.2 1.8-3.7 1.8-1.5 0-2.8-.6-3.7-1.7-2.7-.3-4.8-2.6-4.7-5.4l.1-.5C2.4 12.4 2 11.3 2 10.2c0-2.3 1.7-4.2 4-4.5A5.8 5.8 0 0 1 12 3.2Z" fill="#74aa9c" stroke="#74aa9c" stroke-width="0.5"/>
                                    <path d="m7 8.8 5 2.8m0 0 5-2.8m-5 2.8v5.2m-3.8-1.8 3.8-2.2 3.8 2.2" fill="none" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3>OpenAI</h3>
                            <p class="provider-card__models">GPT-4o &amp; GPT-4o-mini</p>
                            <p class="provider-card__desc">Industry-standard models for deep code analysis, security audits, and structured documentation generation. Connect your own API key or use the shared developer key.</p>
                            <span class="provider-card__badge provider-card__badge--active">Active</span>
                        </div>
                    </article>

                    <article class="provider-card provider-card--deepseek" data-reveal>
                        <div class="provider-card__inner">
                            <div class="provider-card__logo-wrap provider-card__logo-wrap--deepseek">
                                <svg class="provider-card__logo" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" fill="#4D6BFE"/>
                                    <path d="M7.5 12h9M12 7.5v9" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/>
                                    <circle cx="12" cy="12" r="3" fill="#fff" opacity="0.3"/>
                                </svg>
                            </div>
                            <h3>DeepSeek</h3>
                            <p class="provider-card__models">DeepSeek-Chat &amp; DeepSeek-Reasoner</p>
                            <p class="provider-card__desc">High-performance reasoning models optimized for code understanding, logic verification, and cost-effective large-scale analysis. Now fully integrated into PR ai.</p>
                            <span class="provider-card__badge provider-card__badge--new">New</span>
                        </div>
                    </article>
                </div>
            </section>

            <section class="security-section" id="security" data-reveal style="padding: 6rem 5%; background: var(--surface); position: relative; overflow: hidden; border-top: 1px solid var(--border);">
                <div class="bg-orb bg-orb--three" aria-hidden="true" style="top: 20%; left: -10%; opacity: 0.1;"></div>
                <div class="section-copy" data-reveal style="max-width: 800px; margin: 0 auto 4rem auto; text-align: center;">
                    <span class="section-tag" style="background: rgba(255, 50, 50, 0.1); color: #ff5555;">VAPT &amp; OWASP Top 10</span>
                    <h2 style="font-size: clamp(2rem, 4vw, 3rem); line-height: 1.1; margin-bottom: 1.5rem; letter-spacing: -0.02em;">Code reviews that think like hackers</h2>
                    <p style="font-size: 1.125rem; color: var(--text-muted); line-height: 1.6;">
                        PR-AI doesn't just check for syntax errors or code style. It performs a Vulnerability Assessment and Penetration Testing (VAPT) analysis on every pull request, strictly aligned with the OWASP Top 10 standard.
                    </p>
                </div>

                <div class="security-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; max-width: 1200px; margin: 0 auto; position: relative; z-index: 2;">
                    <div class="security-card" data-reveal style="background: var(--surface-raised); border: 1px solid var(--border); padding: 2rem; border-radius: 16px; transition: transform 0.3s ease, border-color 0.3s ease;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 50, 50, 0.1); color: #ff5555; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem;">Injection Prevention</h3>
                        <p style="color: var(--text-muted); font-size: 0.9375rem; line-height: 1.5;">Automatically detects SQL, NoSQL, OS, and LDAP injection flaws before they reach your production environment.</p>
                    </div>

                    <div class="security-card" data-reveal style="background: var(--surface-raised); border: 1px solid var(--border); padding: 2rem; border-radius: 16px; transition: transform 0.3s ease, border-color 0.3s ease;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(0, 200, 255, 0.1); color: #00c8ff; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem;">Access Control</h3>
                        <p style="color: var(--text-muted); font-size: 0.9375rem; line-height: 1.5;">Spots broken authentication, missing role checks, and unauthorized data access routes during the review stage.</p>
                    </div>

                    <div class="security-card" data-reveal style="background: var(--surface-raised); border: 1px solid var(--border); padding: 2rem; border-radius: 16px; transition: transform 0.3s ease, border-color 0.3s ease;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(150, 100, 255, 0.1); color: #9664ff; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem;">Cryptographic Failures</h3>
                        <p style="color: var(--text-muted); font-size: 0.9375rem; line-height: 1.5;">Flags hardcoded secrets, weak encryption algorithms, and insecure data transmission protocols in your diffs.</p>
                    </div>
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
                            <div class="faq-answer">
                                <p>{{ $faq['answer'] }}</p>
                            </div>
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
