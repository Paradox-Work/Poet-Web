<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <title inertia>{{ config('app.name', 'Poet-Web') }}</title>
        <meta
            name="description"
            content="Poet-Web — a Latvian-first platform for reading, publishing, discussing, and organizing poetry."
        >
        <meta property="og:site_name" content="Poet-Web">
        <meta property="og:type" content="website">
        <meta
            property="og:description"
            content="Read, write, discuss, and organize poetry in Poet-Web."
        >
        <meta name="twitter:card" content="summary">
        <style>
            /* Prevent the keyboard skip link flashing before Vite CSS loads. */
            .poet-skip-link:not(:focus) { visibility: hidden; }
            .poet-skip-link:focus { visibility: visible; }
            html, body { background-color: #f4ede4; color: #0f1b2d; }
            html.dark, html.dark body { background-color: #0f1b2d; color: #f4ede4; color-scheme: dark; }
        </style>

        <script>
            (() => {
                const storedTheme =
                    localStorage.getItem('theme');

                const prefersDark =
                    window.matchMedia(
                        '(prefers-color-scheme: dark)'
                    ).matches;

                const useDark =
                    storedTheme === 'dark' ||
                    (!storedTheme && prefersDark);

                document.documentElement
                    .classList
                    .toggle('dark', useDark);
                document.documentElement.style.colorScheme = useDark ? 'dark' : 'light';
            })();
        </script>

        <!-- Poet-Web typography -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link
            href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap"
            rel="stylesheet"
        >
        <link
            href="https://fonts.bunny.net/css?family=playfair-display:500,600,700,700i&display=swap"
            rel="stylesheet"
        >
        <meta
            name="theme-color"
            content="#0f1b2d"
        >

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="bg-[var(--poet-bg)] font-sans text-[var(--poet-text)] antialiased lg:h-full lg:overflow-hidden">
        <a
            href="#main-content"
            class="poet-skip-link"
        >
            Skip to main content
        </a>

        @inertia
    </body>
</html>
