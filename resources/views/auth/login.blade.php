<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.meta', [
        'metaTitle' => 'PR ai | Sign in — AI Code Review & PR Audit Tool',
        'metaDescription' => 'Sign in with GitHub or GitLab to access PR ai — the AI-powered pull request auditing workspace with VAPT security analysis and OWASP Top 10 coverage.',
        'metaType' => 'website',
    ])
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
                    <span>I accept the <a href="#" id="tos-link">Terms of Service</a> and <a href="#" id="privacy-link">Privacy Policy</a></span>
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

                <h4>1. Introduction and Acceptance of Terms</h4>
                <p>Welcome to PR ai ("the Service", "we", "our", or "us"). PR ai is an AI-powered code review, pull request auditing, and documentation generation platform designed for software engineering teams. By accessing, browsing, registering for, or using the Service in any way, you ("the User", "you", or "your") acknowledge that you have read, understood, and agree to be legally bound by these Terms of Service ("Terms"). If you do not agree to these Terms in their entirety, you must immediately discontinue use of the Service and refrain from accessing it.</p>
                <p>These Terms constitute a binding legal agreement between you and the operator of PR ai. Your use of the Service is expressly conditioned upon your compliance with these Terms. We reserve the right to update, modify, or replace any part of these Terms at our sole discretion. It is your responsibility to check this page periodically for changes. Your continued use of or access to the Service following the posting of any changes constitutes acceptance of those changes.</p>

                <h4>2. Description of the Service</h4>
                <p>PR ai provides an AI-assisted workspace for software developers to import pull requests, branch diffs, commits, and code snippets from connected version control systems including GitHub, GitLab, Bitbucket, and Azure DevOps. The Service uses third-party artificial intelligence providers (including but not limited to OpenAI and DeepSeek) to analyze submitted code and generate security audits, vulnerability assessments, code reviews, documentation, diagrams, and related outputs.</p>
                <p>The Service also includes document generation capabilities ("DocGen"), voice-to-text transcription via OpenAI Whisper, persistent chat conversation storage, diff visualization, and inline code commenting. PR ai is provided as a software-as-a-service (SaaS) platform accessible through a standard web browser. Support for Bitbucket and Azure DevOps integrations is currently under development and marked as "Coming Soon" where applicable.</p>

                <h4>3. Eligibility and User Accounts</h4>
                <p>You must be at least 13 years of age to use the Service. If you are under the age of majority in your jurisdiction, you must have the consent of a parent or legal guardian. By using the Service, you represent and warrant that you meet these eligibility requirements.</p>
                <p>You may create an account by authenticating through GitHub OAuth or GitLab OAuth. When you authenticate through these third-party services, PR ai receives your public profile information (username, avatar, and email address if made public) as permitted by the respective platform's OAuth scopes. PR ai does not receive or store your GitHub, GitLab, or any other third-party account passwords at any time — authentication is delegated entirely to the respective OAuth provider.</p>
                <p>You are solely responsible for maintaining the confidentiality and security of your connected accounts and for all activities that occur under your PR ai session. You agree to notify us immediately of any unauthorized access to or use of your account. PR ai shall not be liable for any loss or damage arising from your failure to comply with this section.</p>

                <h4>4. Third-Party API Keys and Services</h4>
                <p>PR ai supports two modes of AI provider authentication: (a) a shared developer API key provided by PR ai ("Developer Mode"), and (b) your own personal API key ("Personal Mode") for OpenAI and/or DeepSeek. When you choose to use Personal Mode, your API key is transmitted to our server, encrypted at rest using AES-256-CBC encryption, and used solely for the purpose of forwarding your code analysis requests to the selected AI provider. Your personal API key is never shared with other users, sold, or used for any purpose other than processing your specific requests.</p>
                <p>When using your personal API key, you are directly responsible for all costs, charges, and fees incurred through your AI provider account. These costs are billed by the AI provider (OpenAI, DeepSeek, or others) according to their respective pricing terms, which are independent of and unrelated to PR ai. PR ai does not control, and is not responsible for, the pricing, rate limits, service availability, or terms of any third-party AI provider. You are strongly advised to review the pricing and terms of your chosen AI provider before using Personal Mode.</p>
                <p>PR ai reserves the right to impose usage limits, rate limits, or quotas on the shared developer API key at any time without prior notice, in order to maintain service quality and prevent abuse.</p>

                <h4>5. User-Generated Content and Code Submission</h4>
                <p>By submitting code diffs, pull request data, commit information, code snippets, or any other content ("User Content") to the Service, you represent and warrant that: (a) you own or have the necessary licenses, rights, consents, and permissions to submit such User Content; (b) the User Content does not infringe, misappropriate, or violate any third party's intellectual property rights, privacy rights, or any other legal rights; and (c) the User Content complies with all applicable laws and regulations.</p>
                <p>You retain all ownership rights in and to your User Content. PR ai does not claim ownership over any User Content you submit. By submitting User Content, you grant PR ai a limited, non-exclusive, worldwide, royalty-free license to process, transmit, display, and store your User Content solely as necessary to provide the Service to you. This license terminates when you delete your User Content or your account, except to the extent that copies have been retained in routine backup archives for a reasonable period not exceeding thirty (30) days.</p>

                <h4>6. Acceptable Use Policy</h4>
                <p>You agree that you will not, and will not permit or encourage any third party to, use the Service in any manner that violates these Terms or any applicable law. Specifically, you agree not to:</p>
                <ul>
                    <li>Submit, transmit, or process any code or content that is unlawful, harmful, threatening, abusive, harassing, defamatory, obscene, or otherwise objectionable;</li>
                    <li>Submit code containing malware, ransomware, viruses, worms, Trojan horses, exploit payloads, or any other malicious or destructive code;</li>
                    <li>Use the Service to develop, test, or refine any code or system intended to compromise the security of any computer system, network, or application without explicit written authorization from the system owner;</li>
                    <li>Attempt to probe, scan, or test the vulnerability of the Service itself or to breach any security or authentication measures;</li>
                    <li>Attempt to reverse engineer, decompile, disassemble, or extract the underlying source code, system prompts, model configurations, or proprietary logic of the Service;</li>
                    <li>Use any automated means (bots, scrapers, crawlers, scripts) to access or interact with the Service in a manner that sends more requests to our servers than a human can reasonably produce in the same period;</li>
                    <li>Use the Service for any activity that constitutes or facilitates illegal activity under any applicable law;</li>
                    <li>Impersonate any person or entity, or falsely state or otherwise misrepresent your affiliation with a person or entity;</li>
                    <li>Interfere with or disrupt the Service, its servers, or networks connected to the Service;</li>
                    <li>Submit code that you know, or reasonably should know, you do not have the legal right to share, including proprietary code covered by non-disclosure agreements or other confidentiality obligations.</li>
                </ul>
                <p>PR ai reserves the right to investigate and take appropriate legal action against anyone who violates this Acceptable Use Policy, including reporting violators to law enforcement authorities. We further reserve the right to suspend or terminate your access to the Service without prior notice for any violation of these Terms.</p>

                <h4>7. Intellectual Property Rights</h4>
                <p>The Service itself, including its original content, features, functionality, user interface design, logos, trademarks, system prompts, and underlying code (excluding User Content), is and shall remain the exclusive property of PR ai and its licensors. These Terms do not grant you any right, title, or interest in or to the Service except the limited right to use the Service in accordance with these Terms.</p>
                <p>The PR ai name, logo, and all related names, logos, product and service names, designs, and slogans are trademarks of PR ai or its affiliates. You may not use such marks without our prior written permission. All other names, logos, product and service names, designs, and slogans appearing on the Service are the trademarks of their respective owners.</p>

                <h4>8. Disclaimer of Warranties</h4>
                <p>THE SERVICE IS PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS, WITHOUT ANY WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED. TO THE FULLEST EXTENT PERMITTED BY APPLICABLE LAW, PR AI EXPRESSLY DISCLAIMS ALL WARRANTIES, WHETHER EXPRESS, IMPLIED, STATUTORY, OR OTHERWISE, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, QUIET ENJOYMENT, ACCURACY, AND NON-INFRINGEMENT.</p>
                <p>WITHOUT LIMITING THE FOREGOING, PR AI MAKES NO WARRANTY OR REPRESENTATION THAT: (A) THE SERVICE WILL MEET YOUR SPECIFIC REQUIREMENTS; (B) THE SERVICE WILL BE UNINTERRUPTED, TIMELY, SECURE, OR ERROR-FREE; (C) THE RESULTS OBTAINED FROM USE OF THE SERVICE, INCLUDING ALL AI-GENERATED CODE REVIEWS, AUDITS, AND RECOMMENDATIONS, WILL BE ACCURATE, COMPLETE, OR RELIABLE; (D) ANY ERRORS OR DEFECTS IN THE SERVICE WILL BE CORRECTED; OR (E) THE SERVICE OR THE SERVERS THAT MAKE IT AVAILABLE ARE FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS.</p>
                <p>AI-GENERATED CODE REVIEWS, SECURITY AUDITS, AND SUGGESTIONS ARE PROVIDED FOR INFORMATIONAL AND ASSISTIVE PURPOSES ONLY. THEY ARE NOT A SUBSTITUTE FOR PROFESSIONAL HUMAN CODE REVIEW, SECURITY TESTING, OR ENGINEERING JUDGMENT. YOU SHOULD ALWAYS INDEPENDENTLY REVIEW AND VERIFY ANY AI-GENERATED OUTPUT BEFORE RELYING ON IT, ESPECIALLY IN PRODUCTION ENVIRONMENTS WHERE SECURITY AND CORRECTNESS ARE CRITICAL.</p>

                <h4>9. Limitation of Liability</h4>
                <p>TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO EVENT SHALL PR AI, ITS OPERATORS, DEVELOPERS, AFFILIATES, AGENTS, DIRECTORS, EMPLOYEES, SUPPLIERS, OR LICENSORS BE LIABLE FOR ANY INDIRECT, PUNITIVE, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR EXEMPLARY DAMAGES, INCLUDING WITHOUT LIMITATION DAMAGES FOR LOSS OF PROFITS, GOODWILL, USE, DATA, OR OTHER INTANGIBLE LOSSES, ARISING OUT OF OR RELATING TO THE USE OF, OR INABILITY TO USE, THE SERVICE.</p>
                <p>WITHOUT LIMITING THE GENERALITY OF THE FOREGOING, PR AI SHALL NOT BE LIABLE FOR ANY DAMAGES RESULTING FROM: (A) MISSED SECURITY VULNERABILITIES OR FALSE POSITIVES IN AI-GENERATED AUDIT REPORTS; (B) INCORRECT, MISLEADING, OR INCOMPLETE CODE SUGGESTIONS OR ANALYSIS; (C) DATA LOSS OR CORRUPTION RESULTING FROM API FAILURES, NETWORK INTERRUPTIONS, OR SERVICE OUTAGES; (D) COSTS OR CHARGES INCURRED THROUGH THIRD-PARTY API PROVIDERS; (E) ANY UNAUTHORIZED ACCESS TO OR USE OF OUR SERVERS AND/OR ANY PERSONAL INFORMATION STORED THEREIN; (F) ANY BUGS, VIRUSES, OR OTHER HARMFUL CODE THAT MAY BE TRANSMITTED TO OR THROUGH THE SERVICE BY ANY THIRD PARTY.</p>
                <p>IN ANY EVENT, THE TOTAL AGGREGATE LIABILITY OF PR AI FOR ANY CLAIMS ARISING OUT OF OR RELATING TO THESE TERMS OR THE SERVICE SHALL NOT EXCEED THE GREATER OF: (A) THE AMOUNTS PAID BY YOU TO PR AI IN THE TWELVE (12) MONTHS PRECEDING THE CLAIM; OR (B) ONE HUNDRED UNITED STATES DOLLARS (USD $100.00). THE FOREGOING LIMITATIONS SHALL APPLY EVEN IF PR AI HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES AND REGARDLESS OF THE THEORY OF LIABILITY (WHETHER CONTRACT, TORT, STRICT LIABILITY, OR OTHERWISE).</p>

                <h4>10. Indemnification</h4>
                <p>You agree to defend, indemnify, and hold harmless PR ai, its operators, developers, affiliates, licensors, and service providers, and its and their respective officers, directors, employees, contractors, agents, licensors, suppliers, successors, and assigns from and against any claims, liabilities, damages, judgments, awards, losses, costs, expenses, or fees (including reasonable attorneys' fees) arising out of or relating to: (a) your violation of these Terms; (b) your User Content; (c) your use of the Service, including but not limited to any use of the Service's content, services, and products other than as expressly authorized in these Terms; or (d) your violation of any applicable law, rule, or regulation, or the rights of any third party.</p>

                <h4>11. Third-Party Links and Integrations</h4>
                <p>The Service may contain links to or integrations with third-party websites, platforms, and services, including but not limited to GitHub, GitLab, OpenAI, DeepSeek, and their respective APIs. PR ai does not control, endorse, sponsor, recommend, or accept responsibility for the content, privacy policies, or practices of any third-party websites or services. You acknowledge and agree that PR ai shall not be responsible or liable for any damage or loss caused or alleged to be caused by or in connection with your use of or reliance on any such third-party content, goods, or services available on or through any such websites or services.</p>

                <h4>12. Termination</h4>
                <p>We may terminate or suspend your access to the Service immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach these Terms. Upon termination, your right to use the Service will immediately cease. All provisions of these Terms which by their nature should survive termination shall survive termination, including, without limitation, ownership provisions, warranty disclaimers, indemnity, and limitations of liability.</p>
                <p>You may discontinue using the Service at any time. If you wish to delete your account and associated data, you may contact us using the information provided in the Contact section below.</p>

                <h4>13. Governing Law and Dispute Resolution</h4>
                <p>These Terms shall be governed and construed in accordance with the laws applicable in the jurisdiction where the Service operator is based, without regard to its conflict of law provisions. Any dispute arising out of or relating to these Terms or the Service shall be resolved through good-faith negotiation between the parties. If the dispute cannot be resolved through negotiation within thirty (30) days, either party may pursue resolution through the courts of competent jurisdiction.</p>
                <p>You agree that regardless of any statute or law to the contrary, any claim or cause of action arising out of or related to the use of the Service or these Terms must be filed within one (1) year after such claim or cause of action arose or be forever barred.</p>

                <h4>14. Severability and Waiver</h4>
                <p>If any provision of these Terms is held to be invalid, illegal, or unenforceable by a court of competent jurisdiction, such provision shall be modified to reflect the parties' original intention as closely as possible in accordance with applicable law, and the remaining provisions of these Terms shall remain in full force and effect. No waiver of any term of these Terms shall be deemed a further or continuing waiver of such term or any other term, and PR ai's failure to assert any right or provision under these Terms shall not constitute a waiver of such right or provision.</p>

                <h4>15. Entire Agreement</h4>
                <p>These Terms, together with our Privacy Policy, constitute the entire agreement between you and PR ai regarding the Service and supersede all prior and contemporaneous understandings, agreements, representations, and warranties, both written and oral, regarding the Service.</p>

                <h4>16. Contact Information</h4>
                <p>If you have any questions, concerns, or feedback about these Terms of Service, or if you need to contact us for any reason related to your use of the Service, please reach out to:</p>
                <p><strong>Email:</strong> nanboldassak2@gmail.com</p>
                <p>We strive to respond to all inquiries within 2-3 business days.</p>
            </div>
            <div class="tos-modal-footer">
                <button class="tos-modal-btn tos-modal-btn--close" id="tos-modal-decline-btn" type="button">Close</button>
            </div>
        </div>
    </div>

    {{-- Privacy Policy Modal --}}
    <div class="tos-modal" id="privacy-modal" aria-hidden="true">
        <div class="tos-modal-card">
            <div class="tos-modal-head">
                <h3>Privacy Policy</h3>
                <button class="tos-modal-close" id="privacy-modal-close-btn" type="button" aria-label="Close">&times;</button>
            </div>
            <div class="tos-modal-body">
                <p><strong>Last updated:</strong> June 10, 2026</p>

                <h4>1. Introduction</h4>
                <p>This Privacy Policy explains how PR ai ("we", "our", or "us") collects, uses, processes, stores, and discloses information about you when you access or use our Service. We are committed to protecting your privacy and handling your data transparently and responsibly. By using the Service, you consent to the collection and use of information in accordance with this Privacy Policy. If you do not agree with any part of this policy, you must discontinue use of the Service.</p>

                <h4>2. Information We Collect</h4>
                <p><strong>2.1 Account Information.</strong> When you sign in to PR ai through GitHub OAuth or GitLab OAuth, we receive your public profile information as authorized by the OAuth flow. This typically includes your username, display name, avatar URL, and public email address if you have one configured. We do not receive or store your GitHub or GitLab account passwords.</p>
                <p><strong>2.2 User-Generated Content.</strong> We collect and store the content you submit to the Service, including but not limited to: code diffs, pull request data, commit information, chat messages and prompts, AI-generated responses, conversation history, and audit contexts. This information is stored in our database (hosted on Supabase PostgreSQL) to provide persistent chat history and context across sessions.</p>
                <p><strong>2.3 API Key Information.</strong> If you choose to provide your own OpenAI or DeepSeek API key for Personal Mode, that key is encrypted at rest using AES-256-CBC encryption and stored in our database. Your API key is only used to authenticate your specific requests to the respective AI provider and is never shared with other users.</p>
                <p><strong>2.4 Usage Data.</strong> We may automatically collect certain information about your device and usage of the Service, including your IP address, browser type, operating system, referring URLs, pages visited, and the dates and times of your visits. This data is used for operational purposes, security monitoring, and service improvement.</p>
                <p><strong>2.5 Audio Data.</strong> If you use the voice input feature, your audio recording is transmitted to OpenAI's Whisper API for transcription. PR ai does not permanently store your audio recordings — they are processed in transit and discarded after transcription. The resulting text transcript is treated as User-Generated Content.</p>

                <h4>3. How We Use Your Information</h4>
                <p>We use the information we collect for the following purposes:</p>
                <ul>
                    <li><strong>To Provide the Service:</strong> Processing your code diffs, generating AI-powered audits and reviews, maintaining chat history, restoring conversation context, and enabling all features of the platform.</li>
                    <li><strong>To Communicate With You:</strong> Responding to your inquiries, providing customer support, and sending service-related notifications.</li>
                    <li><strong>To Improve the Service:</strong> Analyzing usage patterns, identifying bugs and performance issues, and informing feature development priorities. We do not use your code or conversations to train AI models.</li>
                    <li><strong>To Maintain Security:</strong> Detecting, preventing, and addressing technical issues, fraud, abuse, and violations of our Terms of Service.</li>
                    <li><strong>To Comply With Legal Obligations:</strong> Responding to lawful requests from public authorities, enforcing our Terms, and protecting our rights and the rights of others.</li>
                </ul>

                <h4>4. How Your Data is Shared</h4>
                <p><strong>4.1 AI Providers (OpenAI, DeepSeek).</strong> When you submit code for analysis, the content of your request (including code diffs, prompts, and conversation context) is transmitted to the AI provider you have selected (OpenAI or DeepSeek) for processing. This is essential for the Service to function. Each AI provider handles data according to their own privacy policies and data processing terms. You should review the respective privacy policies of OpenAI (https://openai.com/policies/privacy-policy) and DeepSeek before using the Service.</p>
                <p><strong>4.2 Voice Transcription (OpenAI Whisper).</strong> If you use voice input, audio data is sent to OpenAI's Whisper API for transcription. OpenAI's data usage policies for API services apply.</p>
                <p><strong>4.3 Infrastructure Providers.</strong> We use Supabase for database hosting. Your data is stored on Supabase infrastructure. Supabase's privacy and security policies apply to the hosting environment.</p>
                <p><strong>4.4 No Sale of Data.</strong> We do not sell, rent, trade, or otherwise disclose your personal information or User Content to third parties for monetary or other valuable consideration. We do not share your data with advertisers, data brokers, or analytics companies. We do not use your code, conversations, or any User Content to train, fine-tune, or improve AI models — neither our own nor those of third parties.</p>
                <p><strong>4.5 Legal Disclosures.</strong> We may disclose your information if required to do so by law or in response to valid legal process (such as a court order, subpoena, or search warrant), or if we believe in good faith that disclosure is necessary to protect our rights, protect your safety or the safety of others, investigate fraud, or respond to a government request.</p>
                <p><strong>4.6 Business Transfers.</strong> In the event of a merger, acquisition, reorganization, sale of assets, or bankruptcy, your information may be transferred as part of that transaction. We will provide notice before your information is transferred and becomes subject to a different privacy policy.</p>

                <h4>5. Data Storage and Security</h4>
                <p>Your data is stored on Supabase (PostgreSQL) with encryption at rest and in transit (TLS/SSL). API keys stored in Personal Mode are encrypted at the application layer using AES-256-CBC encryption before being written to the database. We implement commercially reasonable administrative, technical, and physical security measures designed to protect your information from unauthorized access, disclosure, alteration, and destruction.</p>
                <p>However, no method of electronic storage or transmission over the Internet is 100% secure. While we strive to protect your personal information, we cannot guarantee its absolute security. You acknowledge and accept this risk when using the Service.</p>

                <h4>6. Data Retention</h4>
                <p><strong>6.1 Chat Conversations.</strong> Your conversation histories (including messages and audit contexts) are retained as long as your account remains active. You may delete individual conversations at any time through the Service interface. Deleted conversations are removed from our active database but may persist in routine encrypted backups for up to thirty (30) days.</p>
                <p><strong>6.2 Account Data.</strong> Your account information (username, avatar URL, provider tokens) is retained until you request deletion of your account. To request account deletion, contact us at the email provided below.</p>
                <p><strong>6.3 API Keys.</strong> Personal API keys are deleted from our database immediately upon your request to remove them through the Service interface.</p>
                <p><strong>6.4 Usage Logs.</strong> Server logs and usage data may be retained for a reasonable period (typically 30-90 days) for operational, security, and debugging purposes.</p>

                <h4>7. Your Rights and Choices</h4>
                <p>Depending on your jurisdiction, you may have certain rights regarding your personal information, which may include:</p>
                <ul>
                    <li><strong>Right to Access:</strong> You may request a copy of the personal data we hold about you.</li>
                    <li><strong>Right to Rectification:</strong> You may request that we correct any inaccurate or incomplete data.</li>
                    <li><strong>Right to Erasure:</strong> You may request that we delete your personal data, subject to certain exceptions.</li>
                    <li><strong>Right to Restrict Processing:</strong> You may request that we limit how we process your data in certain circumstances.</li>
                    <li><strong>Right to Data Portability:</strong> You may request a copy of your data in a structured, machine-readable format.</li>
                    <li><strong>Right to Object:</strong> You may object to our processing of your personal data in certain circumstances.</li>
                    <li><strong>Right to Withdraw Consent:</strong> Where processing is based on your consent, you may withdraw that consent at any time.</li>
                </ul>
                <p>To exercise any of these rights, contact us at the email address provided in the Contact section below. We will respond to your request within thirty (30) days. We may need to verify your identity before processing your request.</p>

                <h4>8. Cookies and Tracking</h4>
                <p>PR ai uses essential session cookies and local storage to maintain your authenticated session, remember your theme preferences (light/dark mode), sidebar state, and other user interface preferences. These cookies are strictly necessary for the Service to function and do not track you across websites. We do not use third-party analytics cookies, advertising cookies, or tracking pixels. We do not engage in cross-site tracking or behavioral advertising.</p>

                <h4>9. Children's Privacy</h4>
                <p>The Service is not directed to individuals under the age of 13. We do not knowingly collect personal information from children under 13. If we become aware that a child under 13 has provided us with personal information, we will take steps to delete such information from our servers. If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately.</p>

                <h4>10. International Data Transfers</h4>
                <p>Your information may be transferred to, stored, and processed in countries other than the country in which you reside, including the United States where our database provider (Supabase) and AI providers may have operations. These countries may have data protection laws that are different from the laws of your country. By using the Service, you consent to the transfer of your information to these countries for processing in accordance with this Privacy Policy.</p>
                <p>Where required by applicable law, we implement appropriate safeguards (such as standard contractual clauses) to ensure that your personal data receives an adequate level of protection when transferred internationally.</p>

                <h4>11. Data Breach Notification</h4>
                <p>In the event of a data breach that compromises your personal information, we will notify you and relevant authorities as required by applicable law. Notification will be made via the email address associated with your account or through a prominent notice on the Service. We will take all reasonable steps to mitigate the effects of any breach and prevent future occurrences.</p>

                <h4>12. Changes to This Privacy Policy</h4>
                <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the "Last updated" date. In the case of significant changes that materially affect your rights or our obligations, we will make reasonable efforts to provide additional notice, such as through the Service interface. Your continued use of the Service after the effective date of any changes constitutes your acceptance of the revised Privacy Policy. We encourage you to review this Privacy Policy periodically.</p>

                <h4>13. Contact Us</h4>
                <p>If you have any questions, concerns, or requests regarding this Privacy Policy, your personal data, or our data practices, please contact us:</p>
                <p><strong>Email:</strong> nanboldassak2@gmail.com</p>
                <p>We aim to acknowledge all inquiries within 2-3 business days and resolve any concerns as quickly as possible.</p>
            </div>
            <div class="tos-modal-footer">
                <button class="tos-modal-btn tos-modal-btn--close" id="privacy-modal-decline-btn" type="button">Close</button>
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

            // TOS modal
            if (tosLink) {
                tosLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    openTosModal();
                });
            }

            // Privacy Policy modal
            const privacyLink = document.getElementById('privacy-link');
            const privacyModal = document.getElementById('privacy-modal');
            const privacyCloseBtn = document.getElementById('privacy-modal-close-btn');
            const privacyDeclineBtn = document.getElementById('privacy-modal-decline-btn');

            const openPrivacyModal = () => {
                if (privacyModal) {
                    privacyModal.classList.add('is-open');
                    privacyModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }
            };

            const closePrivacyModal = () => {
                if (privacyModal) {
                    privacyModal.classList.remove('is-open');
                    privacyModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }
            };

            if (privacyLink) {
                privacyLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    openPrivacyModal();
                });
            }

            if (privacyCloseBtn) privacyCloseBtn.addEventListener('click', closePrivacyModal);
            if (privacyDeclineBtn) privacyDeclineBtn.addEventListener('click', closePrivacyModal);

            if (privacyModal) {
                privacyModal.addEventListener('click', function(e) {
                    if (e.target === privacyModal) closePrivacyModal();
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && privacyModal && privacyModal.classList.contains('is-open')) {
                    closePrivacyModal();
                }
            });

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
