<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sign in to PR ai with GitHub to access the auditing workspace.">
    <title>PR ai | Sign in</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo 512 transp bg white color svg.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geologica:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg-page: #f0ede8;
            --bg-page-deep: #ece8e2;
            --panel-white: rgba(255, 255, 255, 0.96);
            --panel-dark: #1f2634;
            --panel-dark-soft: #2a3141;
            --text: #12141c;
            --text-soft: #687081;
            --text-muted: #9aa2b3;
            --brand: #4965ff;
            --brand-deep: #304cff;
            --line: rgba(218, 224, 234, 0.92);
            --shadow: 0 36px 90px rgba(38, 40, 52, 0.08);
            --font-body: 'Geologica', ui-sans-serif, system-ui, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px;
            font-family: var(--font-body);
            color: var(--text);
            background:
                radial-gradient(960px 540px at 0% 0%, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0) 65%),
                radial-gradient(760px 420px at 100% 2%, rgba(73, 101, 255, 0.08) 0%, rgba(73, 101, 255, 0) 72%),
                linear-gradient(180deg, #f0ede8 0%, #ece8e2 100%);
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            inset: auto;
            width: 320px;
            height: 320px;
            background:
                linear-gradient(135deg, rgba(73, 101, 255, 0.06), rgba(73, 101, 255, 0));
            border-radius: 999px;
            filter: blur(60px);
            opacity: 0.8;
            pointer-events: none;
        }

        body::before {
            top: -40px;
            left: -80px;
        }

        body::after {
            right: -70px;
            bottom: -80px;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .auth-shell {
            width: min(100%, 530px);
        }

        .auth-card {
            display: grid;
            grid-template-columns: 1fr;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow);
        }

        .auth-visual {
            display: none;
            position: relative;
            overflow: hidden;
            padding: 40px 38px 34px;
            background:
                radial-gradient(260px 180px at 80% 22%, rgba(48, 76, 255, 0.18) 0%, rgba(48, 76, 255, 0) 72%),
                linear-gradient(180deg, #252b39 0%, #1e2430 100%);
            color: white;
        }

        .auth-visual::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.04), transparent 32%),
                linear-gradient(225deg, rgba(48, 76, 255, 0.1), transparent 26%);
            pointer-events: none;
        }

        .auth-visual-watermark {
            position: absolute;
            right: -44px;
            bottom: -10px;
            width: 340px;
            opacity: 0.08;
            filter: blur(2px);
        }

        .auth-visual-copy,
        .auth-visual-stage,
        .auth-panel {
            position: relative;
            z-index: 1;
        }

        .auth-visual-copy h1 {
            margin: 0;
            max-width: 9ch;
            font-size: clamp(2.1rem, 4vw, 3rem);
            line-height: 1.02;
            letter-spacing: -0.07em;
        }

        .auth-visual-copy p {
            margin: 16px 0 0;
            max-width: 26ch;
            color: rgba(235, 241, 252, 0.78);
            line-height: 1.7;
            font-size: 15px;
        }

        .auth-stage {
            position: relative;
            min-height: 430px;
            margin-top: 28px;
            border-radius: 18px;
            background:
                linear-gradient(180deg, rgba(48, 76, 255, 0.08), rgba(48, 76, 255, 0)),
                rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .auth-stage-card {
            position: absolute;
            border-radius: 18px;
            border: 1px solid rgba(103, 160, 255, 0.24);
            background: rgba(19, 27, 41, 0.74);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.2);
        }

        .auth-stage-card--main {
            left: 28px;
            right: 44px;
            bottom: 42px;
            height: 210px;
            padding: 18px;
        }

        .auth-stage-card--main::before,
        .auth-stage-card--main::after {
            content: '';
            position: absolute;
            border-radius: 14px;
            border: 2px solid #2aa0ff;
        }

        .auth-stage-card--main::before {
            inset: 22px 130px 56px 30px;
        }

        .auth-stage-card--main::after {
            inset: 50px 28px 34px 150px;
            border-color: #36c4ff;
        }

        .auth-stage-chip {
            position: absolute;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: rgba(239, 244, 255, 0.84);
            font-size: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .auth-stage-chip--one {
            top: 22px;
            left: 26px;
        }

        .auth-stage-chip--two {
            top: 74px;
            right: 40px;
        }

        .auth-stage-chip--three {
            bottom: 26px;
            left: 116px;
        }

        .auth-panel {
            padding: 34px 40px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(250, 251, 255, 0.98));
        }

        .auth-panel-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            letter-spacing: -0.05em;
        }

        .auth-brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            filter: brightness(0);
        }

        .auth-heading {
            margin: 0;
            font-size: clamp(3rem, 6vw, 4.4rem);
            line-height: 1;
            letter-spacing: -0.07em;
            font-weight: 800;
        }

        .auth-subtext {
            margin: 10px 0 0;
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.6;
        }

        .auth-panel h2 {
            margin: 26px 0 0;
            font-size: clamp(2rem, 4vw, 2.7rem);
            line-height: 1;
            letter-spacing: -0.07em;
        }

        .auth-panel-sub {
            margin: 12px 0 0;
            color: var(--text-soft);
            font-size: 15px;
            line-height: 1.7;
        }

        .auth-provider-label {
            margin: 28px 0 14px;
            color: var(--text-soft);
            font-size: 14px;
        }

        .auth-provider-list {
            display: grid;
            gap: 12px;
        }

        .auth-provider,
        .auth-provider-disabled {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-height: 54px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
        }

        .auth-provider {
            background: linear-gradient(135deg, #1f2738, #141a25);
            color: white;
            box-shadow: 0 16px 30px rgba(25, 31, 46, 0.18);
        }

        .auth-provider img {
            width: 18px;
            height: 18px;
            filter: brightness(0) invert(1);
        }

        .auth-provider-img--gitlab {
            width: 36px !important;
            height: 36px !important;
            filter: none !important;
        }

        .auth-provider-disabled {
            border: 1px solid var(--line);
            background: #f4f6fa;
            color: #98a0ae;
            opacity: 0.82;
            cursor: not-allowed;
        }

        .auth-provider-disabled span:last-child {
            opacity: 0.92;
        }

        .provider-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            font-size: 13px;
            font-weight: 800;
        }

        .provider-icon--azure svg,
        .provider-icon--gitlab svg,
        .provider-icon--bitbucket svg {
            width: 18px;
            height: 18px;
            display: block;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: var(--text-muted);
            font-size: 12px;
            text-transform: lowercase;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--line);
        }

        .auth-error {
            margin-top: 18px;
            padding: 14px 16px;
            border: 1px solid rgba(217, 69, 69, 0.16);
            border-radius: 14px;
            background: rgba(255, 240, 240, 0.92);
            color: #9c2f2f;
            font-size: 14px;
            line-height: 1.55;
        }

        .auth-tos-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 22px;
            font-size: 13px;
            color: var(--text-soft);
            cursor: pointer;
            user-select: none;
        }

        .auth-tos-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--brand);
            cursor: pointer;
            flex-shrink: 0;
        }

        .auth-tos-check a {
            color: var(--brand);
            text-decoration: underline;
            font-weight: 600;
        }

        .auth-tos-check a:hover {
            color: var(--brand-deep);
        }

        .auth-provider:disabled,
        .auth-provider--disabled {
            opacity: 0.45;
            cursor: not-allowed !important;
            pointer-events: none;
            filter: grayscale(0.6);
        }

        /* TOS Modal */
        .tos-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .tos-modal.is-open {
            display: flex;
        }

        .tos-modal-card {
            position: relative;
            width: min(640px, 95vw);
            max-height: 80vh;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 28px 64px rgba(0,0,0,0.25);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .tos-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid #e5e7eb;
            flex-shrink: 0;
        }

        .tos-modal-head h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #12141c;
        }

        .tos-modal-close {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            color: #5e6475;
            font-size: 18px;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: background 0.15s;
        }

        .tos-modal-close:hover {
            background: #f3f4f6;
        }

        .tos-modal-body {
            padding: 22px;
            overflow-y: auto;
            flex: 1;
            font-size: 13px;
            line-height: 1.75;
            color: #374151;
        }

        .tos-modal-body h4 {
            margin: 0 0 8px;
            font-size: 15px;
            font-weight: 700;
            color: #12141c;
        }

        .tos-modal-body p {
            margin: 0 0 14px;
        }

        .tos-modal-body ul {
            margin: 0 0 14px;
            padding-left: 20px;
        }

        .tos-modal-body li {
            margin-bottom: 6px;
        }

        .tos-modal-footer {
            padding: 14px 22px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: center;
            flex-shrink: 0;
        }

        .tos-modal-btn {
            height: 40px;
            padding: 0 20px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
            font-family: inherit;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .tos-modal-btn:hover { transform: translateY(-1px); }

        .tos-modal-btn--close {
            background: #f3f4f6;
            color: #5e6475;
            border: 1px solid #e5e7eb;
        }

        .auth-terms {
            margin: 18px 0 0;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.65;
        }

        .auth-privacy {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 12px;
            margin-top: 22px;
            padding: 16px;
            border-radius: 16px;
            background: linear-gradient(180deg, #f0f6ff 0%, #f7fbff 100%);
            border: 1px solid #dbe8ff;
        }

        .auth-privacy-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(48, 76, 255, 0.12);
            color: var(--brand);
            font-weight: 800;
        }

        .auth-privacy strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .auth-privacy p {
            margin: 0;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.6;
        }

        .auth-back {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
            margin-bottom: 20px;
            color: var(--text-soft);
            font-size: 14px;
            opacity: 0.8;
            transition: opacity 140ms ease;
        }

        .auth-back:hover {
            opacity: 1;
        }

        @media (max-width: 900px) {
            .auth-card {
                grid-template-columns: 1fr;
            }

            .auth-visual {
                min-height: 320px;
            }

            .auth-stage {
                min-height: 240px;
            }

            .auth-stage-card--main {
                left: 20px;
                right: 20px;
                bottom: 20px;
                height: 150px;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 14px;
            }

            .auth-panel,
            .auth-visual {
                padding: 24px 22px;
            }

            .auth-stage {
                display: none;
            }
        }
    </style>
</head>

<body>
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-visual">
                <img class="auth-visual-watermark" src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}"
                    alt="">

                <div class="auth-visual-copy">
                    <h1>PR ai helps you take the drag out of code review</h1>
                    <p>AI-assisted pull request auditing, DocGen workflows, and diff-aware collaboration for engineering teams.</p>
                </div>

                <div class="auth-stage">
                    <div class="auth-stage-card auth-stage-card--main"></div>
                    <span class="auth-stage-chip auth-stage-chip--one">Diff-aware</span>
                    <span class="auth-stage-chip auth-stage-chip--two">DocGen ready</span>
                    <span class="auth-stage-chip auth-stage-chip--three">GitHub connected</span>
                </div>
            </div>

            <div class="auth-panel">
                <div class="auth-panel-top-row">
                    <a href="{{ url('/') }}" class="auth-back">← Back to homepage</a>
                    <a href="{{ url('/') }}" class="auth-brand">
                        <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo">
                        <span>PR ai</span>
                    </a>
                </div>

                <h2 class="auth-heading">Sign in</h2>
                <p class="auth-subtext">Don't have an account? Just sign in and we'll automatically create one for you.</p>

                @if (session('auth_error'))
                    <div class="auth-error">{{ session('auth_error') }}</div>
                @endif

                <label class="auth-tos-check">
                    <input type="checkbox" id="tos-checkbox">
                    <span>I accept the <a href="#" id="tos-link">Terms of Service</a></span>
                </label>

                <div class="auth-provider-list" style="margin-top: 16px;" id="auth-provider-list">
                    <button class="auth-provider auth-provider--github" id="github-signin-btn" type="button" disabled>
                        <img src="{{ asset('images/github.png') }}" alt="">
                        <span>GitHub</span>
                    </button>

                    <button class="auth-provider auth-provider--gitlab" id="gitlab-signin-btn" type="button" disabled>
                        <img src="{{ asset('images/gitlab-logo-500-rgb.svg') }}" alt="GitLab" class="auth-provider-img--gitlab">
                        <span>GitLab</span>
                    </button>

                    <form action="{{ route('temp.login') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="auth-provider auth-provider--temp" style="width: 100%; border: none; cursor: pointer; background: linear-gradient(135deg, #304cff, #1e2e99);">
                            <span>TEMPORARY LOGIN</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    {{-- Terms of Service Modal --}}
    <div class="tos-modal" id="tos-modal" aria-hidden="true">
        <div class="tos-modal-card">
            <div class="tos-modal-head">
                <h3>Terms of Service</h3>
                <button class="tos-modal-close" id="tos-modal-close-btn" type="button" aria-label="Close">&times;</button>
            </div>
            <div class="tos-modal-body">
                <p><strong>Last updated:</strong> June 10, 2026</p>

                <h4>1. Acceptance of Terms</h4>
                <p>By accessing or using PR ai ("the Service"), you agree to be bound by these Terms of Service. If you do not agree, do not use the Service.</p>

                <h4>2. Description of Service</h4>
                <p>PR ai is an AI-powered code review and pull request assistant. It analyzes code diffs, generates security audits, and provides documentation generation capabilities. The Service integrates with third-party platforms including GitHub, GitLab, and AI providers (OpenAI, DeepSeek).</p>

                <h4>3. User Accounts</h4>
                <p>You may sign in using GitHub OAuth or GitLab OAuth. You are responsible for maintaining the security of your account and any activities that occur under it. PR ai does not store your GitHub or GitLab passwords — authentication is handled entirely through OAuth.</p>

                <h4>4. API Keys & Third-Party Services</h4>
                <p>You may provide your own API keys for OpenAI or DeepSeek. These keys are encrypted at rest. PR ai also provides a shared developer key for convenience. You are responsible for any costs incurred through your personal API keys. PR ai is not liable for charges from third-party API providers.</p>

                <h4>5. Code & Data Privacy</h4>
                <p>Code diffs you submit for analysis are processed by the selected AI provider (OpenAI or DeepSeek). By using the Service, you acknowledge that your code is transmitted to these providers for processing. PR ai does not permanently store your code diffs on our servers beyond what is necessary for chat history. We do not sell, share, or train on your code.</p>

                <h4>6. Acceptable Use</h4>
                <p>You agree not to use the Service to:</p>
                <ul>
                    <li>Upload malicious code, malware, or exploit payloads</li>
                    <li>Attempt to bypass rate limits or abuse API resources</li>
                    <li>Use the Service for any illegal activity</li>
                    <li>Reverse engineer or extract the underlying AI system prompts</li>
                    <li>Submit code that you do not have the right to share</li>
                </ul>

                <h4>7. Disclaimer of Warranties</h4>
                <p>The Service is provided "as is" without warranties of any kind. AI-generated code reviews are suggestions only and should not replace human review. PR ai makes no guarantees about the accuracy, completeness, or security of AI-generated output. Always review AI suggestions before applying them to production code.</p>

                <h4>8. Limitation of Liability</h4>
                <p>PR ai and its creators shall not be liable for any damages arising from the use of the Service, including but not limited to: missed security vulnerabilities in AI-generated audits, incorrect code suggestions, data loss from API failures, or costs from third-party API usage.</p>

                <h4>9. Changes to Terms</h4>
                <p>We reserve the right to modify these terms at any time. Continued use of the Service after changes constitutes acceptance of the new terms.</p>

                <h4>10. Contact</h4>
                <p>For questions about these Terms, contact the developer at <strong>bolitupac.github.io</strong>.</p>
            </div>
            <div class="tos-modal-footer">
                <button class="tos-modal-btn tos-modal-btn--close" id="tos-modal-decline-btn" type="button">Close</button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const tosCheckbox = document.getElementById('tos-checkbox');
            const githubBtn = document.getElementById('github-signin-btn');
            const gitlabBtn = document.getElementById('gitlab-signin-btn');
            const tosLink = document.getElementById('tos-link');
            const tosModal = document.getElementById('tos-modal');
            const tosCloseBtn = document.getElementById('tos-modal-close-btn');
            const tosDeclineBtn = document.getElementById('tos-modal-decline-btn');
            if (!tosCheckbox) return;

            const githubHref = '{{ route('github.redirect') }}';
            const gitlabHref = '{{ route('gitlab.redirect') }}';

            const updateButtons = () => {
                const checked = tosCheckbox.checked;
                if (githubBtn) {
                    githubBtn.disabled = !checked;
                    githubBtn.classList.toggle('auth-provider--disabled', !checked);
                }
                if (gitlabBtn) {
                    gitlabBtn.disabled = !checked;
                    gitlabBtn.classList.toggle('auth-provider--disabled', !checked);
                }
            };

            const openTosModal = () => {
                if (tosModal) {
                    tosModal.classList.add('is-open');
                    tosModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }
            };

            const closeTosModal = () => {
                if (tosModal) {
                    tosModal.classList.remove('is-open');
                    tosModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }
            };

            tosCheckbox.addEventListener('change', updateButtons);

            if (tosLink) {
                tosLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    openTosModal();
                });
            }

            if (tosCloseBtn) tosCloseBtn.addEventListener('click', closeTosModal);
            if (tosDeclineBtn) tosDeclineBtn.addEventListener('click', closeTosModal);

            if (tosModal) {
                tosModal.addEventListener('click', function(e) {
                    if (e.target === tosModal) closeTosModal();
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && tosModal && tosModal.classList.contains('is-open')) {
                    closeTosModal();
                }
            });

            // Sign-in buttons navigate on click when enabled
            if (githubBtn) {
                githubBtn.addEventListener('click', function() {
                    if (tosCheckbox.checked) {
                        window.location.href = githubHref;
                    }
                });
            }
            if (gitlabBtn) {
                gitlabBtn.addEventListener('click', function() {
                    if (tosCheckbox.checked) {
                        window.location.href = gitlabHref;
                    }
                });
            }
        })();
    </script>
</body>

</html>
