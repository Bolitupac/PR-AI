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
            --bg: #f2efe9;
            --panel: rgba(255, 255, 255, 0.86);
            --line: rgba(218, 221, 228, 0.95);
            --text: #12141c;
            --text-soft: #61687a;
            --brand: #304cff;
            --brand-deep: #2338c2;
            --shadow: 0 28px 70px rgba(31, 36, 48, 0.08);
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
            padding: 24px;
            font-family: var(--font-body);
            color: var(--text);
            background:
                radial-gradient(520px 280px at 15% 10%, rgba(48, 76, 255, 0.08) 0%, rgba(48, 76, 255, 0) 70%),
                radial-gradient(480px 260px at 85% 0%, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0) 75%),
                linear-gradient(180deg, #f3f0eb 0%, #efebe5 100%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .auth-shell {
            width: min(100%, 460px);
        }

        .auth-card {
            position: relative;
            overflow: hidden;
            padding: 34px;
            border: 1px solid var(--line);
            border-radius: 32px;
            background: var(--panel);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }

        .auth-card::before {
            content: '';
            position: absolute;
            inset: auto -60px -80px auto;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: rgba(48, 76, 255, 0.08);
            filter: blur(16px);
        }

        .brand {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            letter-spacing: -0.05em;
        }

        .brand img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            filter: brightness(0);
        }

        .eyebrow {
            margin: 28px 0 12px;
            color: var(--brand);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
        }

        h1 {
            margin: 0;
            font-size: clamp(2.2rem, 6vw, 3rem);
            line-height: 0.95;
            letter-spacing: -0.07em;
        }

        p {
            margin: 16px 0 0;
            color: var(--text-soft);
            line-height: 1.7;
            font-size: 15px;
        }

        .auth-error {
            margin-top: 20px;
            padding: 14px 16px;
            border: 1px solid rgba(217, 69, 69, 0.16);
            border-radius: 18px;
            background: rgba(255, 240, 240, 0.92);
            color: #9c2f2f;
            font-size: 14px;
            line-height: 1.55;
        }

        .github-button {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            min-height: 56px;
            margin-top: 28px;
            padding: 0 22px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--brand), var(--brand-deep));
            color: white;
            font-weight: 700;
            letter-spacing: -0.02em;
            box-shadow: 0 18px 36px rgba(48, 76, 255, 0.22);
        }

        .github-button img {
            width: 20px;
            height: 20px;
            filter: brightness(0) invert(1);
        }

        .auth-footnote {
            margin-top: 18px;
            font-size: 13px;
        }

        .auth-back {
            display: inline-block;
            margin-top: 22px;
            color: var(--text-soft);
            font-size: 14px;
        }
    </style>
</head>

<body>
    <main class="auth-shell">
        <section class="auth-card">
            <a href="{{ url('/') }}" class="brand">
                <img src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo">
                <span>PR ai</span>
            </a>

            <p class="eyebrow">GitHub access</p>
            <h1>Sign in to use the tool</h1>
            <p>
                PR ai now requires an account before anyone can access the auditing workspace. For now, users sign in
                with GitHub, and a PR ai account is created automatically on first login.
            </p>

            @if (session('auth_error'))
                <div class="auth-error">{{ session('auth_error') }}</div>
            @endif

            <a href="{{ route('github.redirect') }}" class="github-button">
                <img src="{{ asset('images/github.png') }}" alt="">
                <span>Continue with GitHub</span>
            </a>

            <p class="auth-footnote">
                By continuing, users can connect their GitHub identity and access the PR ai workspace.
            </p>

            <a href="{{ url('/') }}" class="auth-back">Back to homepage</a>
        </section>
    </main>
</body>

</html>
