<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Poet-Web') }}</title>

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
        @inertia
    </body>
</html>
