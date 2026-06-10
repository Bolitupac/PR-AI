@php
    $metaTitle       = $metaTitle       ?? 'PR ai | AI Pull Request Reviews';
    $metaDescription = $metaDescription ?? 'PR ai helps teams import pull requests from GitHub & GitLab, inspect diffs, and generate AI-powered code reviews with VAPT security analysis aligned with OWASP Top 10.';
    $metaImage       = $metaImage       ?? asset('images/git-pull-ai-Logo tp bg 512.png');
    $metaUrl         = $metaUrl         ?? url()->current();
    $metaType        = $metaType        ?? 'website';
    $metaCanonical   = $metaCanonical   ?? $metaUrl;
    $metaRobots      = $metaRobots      ?? 'index, follow';
@endphp

{{-- Primary Meta --}}
<title>{{ $metaTitle }}</title>
<meta name="title" content="{{ $metaTitle }}">
<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="AI code review, pull request audit, VAPT, OWASP Top 10, AI security audit, code review tool, GitHub PR review, GitLab merge request, diff viewer, AI-powered code analysis, automated code review, software security, vulnerability assessment, penetration testing, developer tools">
<meta name="author" content="PR ai — by bolitupac">
<meta name="robots" content="{{ $metaRobots }}">
<meta name="theme-color" content="#4965ff">
<meta name="color-scheme" content="light dark">
<link rel="canonical" href="{{ $metaCanonical }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $metaType }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:alt" content="PR ai — AI Pull Request Reviews">
<meta property="og:image:width" content="512">
<meta property="og:image:height" content="512">
<meta property="og:site_name" content="PR ai">
<meta property="og:locale" content="en_US">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $metaUrl }}">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
<meta name="twitter:image:alt" content="PR ai — AI Pull Request Reviews">
<meta name="twitter:creator" content="&#64;bolitupac">
<meta name="twitter:site" content="&#64;bolitupac">

{{-- Schema.org JSON-LD --}}
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebApplication",
    "name": "PR ai",
    "url": "{{ url('/') }}",
    "description": "{{ $metaDescription }}",
    "applicationCategory": "DeveloperApplication",
    "operatingSystem": "Web",
    "author": {
        "@@type": "Person",
        "name": "Nanbol Dassak",
        "url": "https://bolitupac.github.io"
    },
    "offers": {
        "@@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    }
}
</script>
