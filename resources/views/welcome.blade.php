<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pura Pura Ponsel</title>
    <link rel="icon" href="{{ asset('asset/images/favicon.ico') }}">
    @php($fontAwesomeCssPath = public_path('vendor/dashboard/fontawesome/css/all.min.css'))
    <link rel="stylesheet" href="{{ asset('vendor/dashboard/fontawesome/css/all.min.css') }}?v={{ file_exists($fontAwesomeCssPath) ? filemtime($fontAwesomeCssPath) : time() }}">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgb(248 250 252), rgb(255 255 255));
            color: rgb(15 23 42);
            font-family: ui-sans-serif, system-ui, sans-serif;
        }

        .welcome-card {
            width: min(92vw, 32rem);
            border: 1px solid rgb(226 232 240);
            border-radius: 24px;
            background: rgb(255 255 255);
            box-shadow: 0 20px 40px rgb(15 23 42 / 0.08);
            padding: 32px 28px;
            text-align: center;
        }

        .welcome-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            margin-bottom: 20px;
        }

        .welcome-kicker {
            margin: 0 0 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgb(100 116 139);
        }

        .welcome-title {
            margin: 0;
            font-size: 28px;
            line-height: 1.15;
        }

        .welcome-copy {
            margin: 14px 0 0;
            font-size: 14px;
            line-height: 1.6;
            color: rgb(71 85 105);
        }
    </style>
</head>
<body>
    <main class="welcome-card">
        <img class="welcome-logo" src="{{ asset('asset/images/logo.png') }}" alt="Logo Pura Pura Ponsel">
        <p class="welcome-kicker"><i class="fa-solid fa-chart-line"></i> Dashboard Marketing</p>
        <h1 class="welcome-title">Pura Pura Ponsel</h1>
        <p class="welcome-copy">Halaman awal memakai asset lokal yang sama dengan dashboard utama untuk ikon dan identitas brand.</p>
    </main>
</body>
</html>
