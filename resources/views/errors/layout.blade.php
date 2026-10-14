<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Poet-Web</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #0f1b2d;
            --surface: #1e2936;
            --text: #f4ede4;
            --muted: #a9a39d;
            --accent: #b77a7e;
            --border: #344452;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        }
        main {
            width: min(680px, 100%);
            border: 1px solid var(--border);
            border-radius: 24px;
            background: var(--surface);
            padding: clamp(28px, 7vw, 56px);
            text-align: center;
            box-shadow: 0 24px 70px rgba(0,0,0,.25);
        }
        .code {
            color: #c9a96b;
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .24em;
            text-transform: uppercase;
        }
        h1 {
            margin: 12px 0 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(2rem, 7vw, 4rem);
            line-height: 1.05;
        }
        p {
            margin: 18px auto 0;
            max-width: 48ch;
            color: var(--muted);
            line-height: 1.7;
        }
        a {
            display: inline-flex;
            margin-top: 28px;
            border-radius: 999px;
            background: var(--accent);
            color: white;
            padding: 12px 20px;
            font-weight: 700;
            text-decoration: none;
        }
        a:focus-visible {
            outline: 3px solid white;
            outline-offset: 3px;
        }
    </style>
</head>
<body>
<main>
    <div class="code">@yield('code')</div>
    <h1>@yield('heading')</h1>
    <p>@yield('message')</p>
    <a href="/">@yield('action', 'Atgriezties sākumā')</a>
</main>
</body>
</html>
