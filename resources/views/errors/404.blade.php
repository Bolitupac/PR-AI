<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 – Page not found | PR ai</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo 512 transp bg white color svg.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geologica:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #ece8e2;
            --text: #12141c;
            --text-soft: #5e6475;
            --text-muted: #7c8398;
            --brand: #4965ff;
            --brand-deep: #304cff;
            --font-body: 'Geologica', ui-sans-serif, system-ui, sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-body);
            color: var(--text);
            background:
                radial-gradient(800px 400px at 0% 0%, rgba(255,255,255,0.85) 0%, rgba(255,255,255,0) 65%),
                radial-gradient(600px 350px at 100% 2%, rgba(73,101,255,0.08) 0%, rgba(73,101,255,0) 72%),
                linear-gradient(180deg, #f0ede8 0%, #efebe5 44%, #f6f3ef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .error-card {
            text-align: center;
            max-width: 520px;
            width: 100%;
        }

        .error-logo {
            width: 96px;
            height: 96px;
            margin: 0 auto 32px;
            display: block;
            filter: brightness(0);
            opacity: 0.85;
        }

        .error-code {
            font-size: clamp(5rem, 10vw, 8rem);
            font-weight: 800;
            line-height: 0.9;
            letter-spacing: -0.07em;
            color: var(--text);
            margin-bottom: 16px;
        }

        .error-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border: 1px solid rgba(73, 101, 255, 0.14);
            border-radius: 999px;
            background: rgba(238, 242, 255, 0.92);
            color: var(--brand-deep);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 700;
            letter-spacing: -0.04em;
            margin-bottom: 12px;
        }

        .error-desc {
            color: var(--text-soft);
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .error-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            padding: 0 22px;
            border-radius: 999px;
            font-weight: 700;
            font-family: var(--font-body);
            font-size: 15px;
            text-decoration: none;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn--primary {
            color: white;
            background: linear-gradient(135deg, var(--brand), var(--brand-deep));
            box-shadow: 0 20px 42px rgba(73, 101, 255, 0.24);
        }

        .btn--ghost {
            background: rgba(255,255,255,0.8);
            color: var(--text);
            border: 1px solid rgba(207,208,214,0.9);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <img class="error-logo" src="{{ asset('images/git-pull-ai-Logo tp bg 512.png') }}" alt="PR ai logo">
        <span class="error-tag">404</span>
        <h1 class="error-title">Page not found</h1>
        <p class="error-desc">
            The page you're looking for doesn't exist or has been moved.
            Let's get you back to reviewing pull requests.
        </p>
        <div class="error-actions">
            <a href="/" class="btn btn--primary">Go home</a>
            <a href="{{ route('auditor.index') }}" class="btn btn--ghost">Open auditor</a>
        </div>
    </div>
</body>
</html>
