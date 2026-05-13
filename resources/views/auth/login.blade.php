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
            --bg-blue: #1291f6;
            --bg-blue-deep: #0e7fe0;
            --panel-white: rgba(255, 255, 255, 0.96);
            --panel-dark: #1f2634;
            --panel-dark-soft: #2a3141;
            --text: #12141c;
            --text-soft: #687081;
            --text-muted: #9aa2b3;
            --brand: #304cff;
            --brand-deep: #2338c2;
            --line: rgba(218, 224, 234, 0.92);
            --shadow: 0 36px 90px rgba(14, 31, 71, 0.18);
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
                radial-gradient(420px 240px at 10% 8%, rgba(255, 255, 255, 0.14) 0%, rgba(255, 255, 255, 0) 72%),
                radial-gradient(460px 240px at 92% 96%, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 76%),
                linear-gradient(180deg, var(--bg-blue) 0%, var(--bg-blue-deep) 100%);
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
                linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0));
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            opacity: 0.4;
            pointer-events: none;
        }

        body::before {
            top: -40px;
            left: -80px;
            transform: rotate(18deg);
        }

        body::after {
            right: -70px;
            bottom: -80px;
            transform: rotate(-12deg);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .auth-shell {
            width: min(100%, 1060px);
        }

        .auth-card {
            display: grid;
            grid-template-columns: minmax(320px, 1.05fr) minmax(380px, 1.1fr);
            overflow: hidden;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow);
        }

        .auth-visual {
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

        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            letter-spacing: -0.05em;
        }

        .auth-brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
            filter: brightness(0);
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

        .provider-icon--azure {
            color: #2f8df8;
        }

        .provider-icon--google {
            color: #d74b3f;
        }

        .provider-icon--gitlab {
            color: #fc6d26;
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
            display: inline-block;
            margin-top: 18px;
            color: var(--text-soft);
            font-size: 14px;
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
                <a href="{{ url('/') }}" class="auth-brand">
                    <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo">
                    <span>PR ai</span>
                </a>

                <h2>Sign in to your account</h2>
                <p class="auth-panel-sub">
                    Don&apos;t have an account? Sign in with GitHub and PR ai will create one automatically for you.
                </p>

                @if (session('auth_error'))
                    <div class="auth-error">{{ session('auth_error') }}</div>
                @endif

                <p class="auth-provider-label">Use one of the following systems:</p>

                <div class="auth-provider-list">
                    <a href="{{ route('github.redirect') }}" class="auth-provider">
                        <img src="{{ asset('images/github.png') }}" alt="">
                        <span>GitHub</span>
                    </a>

                    <div class="auth-provider-disabled" aria-disabled="true">
                        <span class="provider-icon provider-icon--azure">A</span>
                        <span>Azure DevOps</span>
                    </div>
                </div>

                <div class="auth-divider">or</div>

                <div class="auth-provider-list">
                    <div class="auth-provider-disabled" aria-disabled="true">
                        <span class="provider-icon provider-icon--google">G</span>
                        <span>Google</span>
                    </div>

                    <div class="auth-provider-disabled" aria-disabled="true">
                        <span class="provider-icon provider-icon--gitlab">G</span>
                        <span>GitLab</span>
                    </div>
                </div>

                <p class="auth-terms">
                    Signing in means you are accepting the PR ai access flow for GitHub-based authentication. More providers will be enabled later.
                </p>

                <div class="auth-privacy">
                    <div class="auth-privacy-icon">P</div>
                    <div>
                        <strong>Your access is important</strong>
                        <p>GitHub is the only live sign-in provider right now. Azure, Google, and GitLab are shown here as upcoming options and are intentionally disabled.</p>
                    </div>
                </div>

                <a href="{{ url('/') }}" class="auth-back">Back to homepage</a>
            </div>
        </section>
    </main>
</body>

</html>
