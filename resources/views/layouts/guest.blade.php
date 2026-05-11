<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>{{ $title ?? 'Connexion' }} — Lipa Agent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .login-bg {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            padding: 0;
        }
        @media (min-width: 600px) {
            .login-bg {
                background: oklch(0.94 0.008 255);
                padding: 24px 16px;
            }
        }
        .login-card {
            width: 100%;
            background: var(--surface);
        }
        @media (min-width: 600px) {
            .login-card {
                max-width: 420px;
                border-radius: 16px;
                box-shadow: 0 8px 40px rgba(0,0,0,0.12);
                border: 1px solid var(--border-color);
            }
        }
    </style>
</head>
<body class="antialiased">
    <div class="login-bg">
        <div class="login-card">
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
</body>
</html>
