<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AI Automation')</title>
    <style>
        :root { color-scheme: light; font-family: "Segoe UI", sans-serif; color: #172126; background: #f3f6f5; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        a { color: #087f70; }
        .dn-fallback-nav { padding: 16px max(20px, calc((100vw - 1180px) / 2)); background: #123f3a; color: white; }
        .dn-fallback-nav a { color: white; text-decoration: none; font-weight: 700; }
        .dn-fallback-main { max-width: 1180px; padding: 24px 20px; margin: auto; }
        .dn-admin .card { background: white; border: 1px solid #dce5e2; border-radius: 8px; padding: 18px; margin-bottom: 16px; }
        .dn-admin .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
        .dn-admin input, .dn-admin textarea, .dn-admin select { width: 100%; padding: 10px; border: 1px solid #c8d3d0; border-radius: 5px; font: inherit; }
        .dn-admin textarea { min-height: 100px; }
        .dn-admin label { display: block; margin: 10px 0 5px; font-weight: 600; }
        .dn-admin button, .dn-admin .button { display: inline-block; padding: 9px 14px; border: 0; border-radius: 5px; background: #087f70; color: white; cursor: pointer; text-decoration: none; }
        .dn-admin .muted { color: #64736f; }
        .dn-admin .nav-tabs { display: flex; gap: 18px; padding: 12px 0; border-bottom: 1px solid #dce5e2; }
        .dn-admin .record { border-top: 1px solid #e4ebe9; padding: 16px 0; }
        .dn-admin table { width: 100%; border-collapse: collapse; }
        .dn-admin th, .dn-admin td { padding: 10px; border-bottom: 1px solid #e4ebe9; text-align: left; }
        @media (max-width: 640px) { .dn-admin table { display: block; overflow-x: auto; } }
    </style>
</head>
<body>
    <header class="dn-fallback-nav"><a href="{{ route('devniox-ai.admin.settings.index') }}">AI Automation</a></header>
    <main class="dn-fallback-main">@yield('content')</main>
</body>
</html>