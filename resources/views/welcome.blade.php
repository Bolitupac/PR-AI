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
        <div class="bg-orb bg-orb--three" aria-hidden="true"></div>

        <header class="site-header">
            <a href="/" class="brandmark">
                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo">
                <span>PR ai</span>
            </a>
            <nav class="top-nav" aria-label="Primary">
                <a href="#features">Features</a>
                <a href="#faq">FAQ</a>
                <a href="/auditor" class="nav-cta">Try it</a>
            </nav>
        </header>

        <main>
            <section class="hero">
                <div class="hero-interactive" id="hero-interactive" aria-hidden="true"></div>
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
                        <a href="/auditor" class="button button--primary">Try demo</a>
                        <a href="#features" class="button button--ghost">See features</a>
                    </div>

                    <div class="trust-panel">
                        <p class="trust-title">Built around the tools developers already use</p>
                        <div class="trust-logos">
                            <div class="trust-logo-card">
                                <img src="{{ asset('images/github.png') }}" alt="GitHub">
                                <span>GitHub import</span>
                            </div>
                            <div class="trust-logo-card trust-logo-card--brand">
                                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai">
                                <span>PR ai workspace</span>
                            </div>
                            <div class="trust-chip">Diffs</div>
                            <div class="trust-chip">AI audits</div>
                            <div class="trust-chip">Inline context</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="stats-strip" aria-label="Product summary">
                <article class="stat-card">
                    <strong>GitHub to audit in one flow</strong>
                    <span>Import repositories, pull requests, branches, and comments without leaving the app.</span>
                </article>
                <article class="stat-card">
                    <strong>Structured reviews, not generic chat</strong>
                    <span>Get summaries, impact maps, walkthroughs, Mermaid diagrams, and risk-oriented findings.</span>
                </article>
                <article class="stat-card">
                    <strong>Built for iteration</strong>
                    <span>Keep the diff context active, ask follow-up questions, and move from review to action faster.</span>
                </article>
            </section>

            <section class="feature-overview" id="features">
                <div class="section-copy">
                    <span class="section-tag">Product overview</span>
                    <h2>One focused place for AI-assisted pull request reviews</h2>
                    <p>
                        PR ai is designed to reduce review friction. Instead of juggling GitHub tabs, pasted diffs,
                        chat windows, and handwritten notes, teams can import code changes and work through the whole
                        review in one polished flow.
                    </p>
                </div>

                <div class="feature-grid">
                    @foreach ($featureCards as $card)
                        <article class="feature-card">
                            <div class="feature-media">
                                <img src="{{ asset($card['image']) }}" alt="{{ $card['title'] }}">
                            </div>
                            <div class="feature-copy">
                                <h3>{{ $card['title'] }}</h3>
                                <p>{{ $card['description'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="details-section">
                <div class="details-card details-card--primary">
                    <span class="section-tag">What users get today</span>
                    <h2>Import code, audit faster, ask better questions</h2>
                    <ul class="details-list">
                        <li>GitHub repository, branch, pull request, and comment import</li>
                        <li>Commit-level auditing from local git history</li>
                        <li>Structured AI review responses with findings and visual impact maps</li>
                        <li>Follow-up chat grounded in the active audit context</li>
                        <li>Voice-to-AI workflow for quick prompting during review</li>
                    </ul>
                </div>

                <div class="details-card details-card--accent">
                    <span class="section-tag">Coming soon</span>
                    <h2>More capability is on the way</h2>
                    <ul class="details-list">
                        <li>The AI will be able to comment directly on specific lines of code</li>
                        <li>Users will be able to generate extensive technical reports, test plans, and QA documents</li>
                        <li>Users will be able to instruct the AI to prepare commit-and-push workflows from natural language</li>
                        <li>Ongoing bug fixes, refinement, and product furnishing will keep improving the experience</li>
                    </ul>
                </div>
            </section>

            <section class="cta-band">
                <div class="cta-copy">
                    <span class="section-tag">Start reviewing</span>
                    <h2>Move from diff to decision with less drag</h2>
                    <p>
                        Open the auditor, import a pull request or diff, and let PR ai turn raw changes into something
                        your team can review much faster.
                    </p>
                </div>
                <div class="cta-actions">
                    <a href="/auditor" class="button button--primary">Try it</a>
                    <a href="#faq" class="button button--ghost">Read FAQ</a>
                </div>
            </section>

            <section class="comparison-section" id="comparison">
                <div class="section-copy">
                    <span class="section-tag">Comparison</span>
                    <h2>Why teams pick PR ai over the usual alternatives</h2>
                    <p>
                        Most teams still review code with a mix of GitHub tabs, pasted diffs, generic AI chat, and
                        manual note-taking. PR ai pulls those steps into one review environment built specifically for
                        pull requests and code audits.
                    </p>
                </div>

                <div class="comparison-card">
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

            <section class="faq-section" id="faq">
                <div class="section-copy section-copy--faq">
                    <span class="section-tag">FAQ</span>
                    <h2>Questions users will probably ask first</h2>
                </div>

                <div class="faq-list">
                    @foreach ($faqs as $faq)
                        <details class="faq-item" @if ($loop->first) open @endif>
                            <summary>{{ $faq['question'] }}</summary>
                            <p>{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
            <section class="feature-overview" id="documentation" style="margin-top: 120px; padding-bottom: 60px;">
                <div class="section-copy">
                    <span class="section-tag">Documentation & Capabilities</span>
                    <h2>Everything you need to know</h2>
                    <p>
                        Explore the full range of PR-AI's capabilities, from automated code audits to interactive architectural diagramming.
                    </p>
                </div>

                <div class="feature-grid" style="grid-template-columns: 1fr; max-width: 860px; margin: 40px auto 0; text-align: left;">
                    <article class="feature-card" style="padding: 32px; align-items: flex-start; text-align: left; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                        <h3 style="margin-bottom: 12px; font-size: 20px;">1. Automated Auditing & Scopes</h3>
                        <p style="margin-bottom: 16px;">The AI conducts automated analysis to spot issues, instantly catching bugs, security flaws, and performance bottlenecks. <em>Note: The tool is currently in its early stage, so please expect some bugs! More updates are coming soon.</em></p>
                        <p style="margin-bottom: 0;"><strong>Audits:</strong> Seamlessly audit your code history. Branch audits compare the selected branch with the main branch. <strong>Note: For now, you can only audit Pull Requests and Commits directly.</strong></p>
                    </article>

                    <article class="feature-card" style="padding: 32px; align-items: flex-start; text-align: left; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                        <h3 style="margin-bottom: 12px; font-size: 20px;">2. Doc Gen & Voice Interactions</h3>
                        <p style="margin-bottom: 16px;">Automatically generate comprehensive <code>README.md</code> files, setup instructions, or technical design docs based on your repository. The AI will even suggest prompts for you to get started.</p>
                        <p style="margin-bottom: 0;">You can also chat with the AI directly about a specific piece of code, or use your <strong>voice to talk</strong> to the AI for a hands-free experience.</p>
                    </article>

                    <article class="feature-card" style="padding: 32px; align-items: flex-start; text-align: left; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                        <h3 style="margin-bottom: 12px; font-size: 20px;">3. Diffs, Diagrams & Context</h3>
                        <p style="margin-bottom: 16px;">The UI displays Git diffs at the bottom of the audit page when a repository is loaded, a diff file is uploaded, or when a diff is posted. <strong>Important:</strong> A user's git diff code <em>must</em> be pasted in the provided box on the Import page so it can be rendered correctly in diff2html, else it will not be rendered.</p>
                        <p style="margin-bottom: 16px;">PR-AI can also visually display architectural diagrams, workflows, and logic trees using <strong>Mermaid</strong>.</p>
                        <p style="margin-bottom: 0;">You can upload external context documents by clicking the top right icon in the audit page, or by using the plus button in the chatbox.</p>
                    </article>

                    <article class="feature-card" style="padding: 32px; align-items: flex-start; text-align: left; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                        <h3 style="margin-bottom: 12px; font-size: 20px;">4. Configuration & API Keys</h3>
                        <p style="margin-bottom: 0;">In the settings, you can choose to use the default developer API key, or you can choose to supply your own personal OpenAI key for extended limits and privacy.</p>
                    </article>

                    <article class="feature-card" style="padding: 32px; align-items: flex-start; text-align: left; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                        <h3 style="margin-bottom: 12px; font-size: 20px;">5. Buttons & Settings Reference</h3>
                        <p style="margin-bottom: 16px;"><strong>Imports Page:</strong> Use <em>Connect</em> to link VCS providers. Use <em>Load Diff Box</em> or <em>Upload Diff</em> to import custom patches, and click <em>Audit</em> next to pulled PRs to instantly analyze them.</p>
                        <p style="margin-bottom: 16px;"><strong>Auditor Controls:</strong> Toggle the sidebar, toggle themes (Sun/Moon), upload context files (+), dictate prompts with the <em>Voice/Mic</em> button, or switch to <em>Doc Gen Mode</em> to generate architecture docs.</p>
                        <p style="margin-bottom: 0;"><strong>Settings Modal:</strong> Adjust your AI's personality, verbosity, and tone. Toggle <em>Demo Mode</em> to safely test features, or provide a Custom System Prompt to strictly align the AI with your team's unique coding guidelines.</p>
                    </article>
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
