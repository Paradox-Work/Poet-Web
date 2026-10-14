<!DOCTYPE html>
<html lang="{{ request()->getPreferredLanguage(['lv', 'en']) ?? 'lv' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} · Poet-Web</title>
    <meta name="robots" content="noindex">
    <style>
        :root {
            color-scheme: dark;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            background: #0f1b2d;
            color: #f7f2e8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at top, rgba(181,140,255,.12), transparent 35%),
                #0f1b2d;
        }
        main {
            width: min(620px, 100%);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 24px;
            background: rgba(18, 26, 49, .92);
            padding: 32px;
            box-shadow: 0 20px 70px rgba(0,0,0,.28);
        }
        .kicker {
            font-size: 12px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #f5c96a;
        }
        h1 {
            margin: 8px 0 10px;
            font-family: Georgia, serif;
            font-size: clamp(34px, 7vw, 64px);
            line-height: 1;
        }
        p {
            color: #b8c1d1;
            line-height: 1.7;
        }
        a {
            display: inline-flex;
            margin-top: 18px;
            padding: 10px 16px;
            border-radius: 999px;
            background: #b58cff;
            color: #111827;
            font-weight: 700;
            text-decoration: none;
        }
        .code {
            font-size: 13px;
            color: #7f8ca2;
        }
    </style>
</head>
<body>
    @php
        $lv = (request()->getPreferredLanguage(['lv', 'en']) ?? 'lv') === 'lv';
    @endphp

    <main>
        <div class="kicker">Poet-Web · {{ $code }}</div>
        <h1>{{ $lv ? $titleLv : $titleEn }}</h1>
        <p>{{ $lv ? $messageLv : $messageEn }}</p>
        <div class="code">{{ $lv ? 'Kļūdas kods' : 'Error code' }}: {{ $code }}</div>

        <a href="{{ route('dashboard') }}">
            {{ $lv ? 'Atgriezties sākumā' : 'Back to Home' }}
        </a>
    </main>
</body>
</html>
