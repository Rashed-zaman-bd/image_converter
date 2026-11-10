<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Image Tools')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"DM Sans"', 'system-ui', 'sans-serif'],
                        display: ['"Bricolage Grotesque"', '"DM Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        ink: '#141733',
                        brand: {
                            50: '#eef0ff',
                            100: '#e0e4ff',
                            500: '#5b5bf0',
                            600: '#4a4ae0',
                            700: '#3b3bc4',
                        },
                    },
                },
            },
        };
    </script>

    <style>
        body {
            background-color: #f6f7fb;
            background-image: radial-gradient(#d5d9e8 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .drop-area.dragover {
            border-color: #5b5bf0;
            background-color: #eef0ff;
        }

        .drop-area {
            transition: border-color .2s ease, background-color .2s ease;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .spinner {
            animation: spin .8s linear infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .drop-area,
            .spinner {
                transition: none;
                animation: none;
            }
        }
    </style>

    @stack('head')
</head>

<body class="min-h-screen font-sans text-ink antialiased">

    @include('partials.navbar')

    <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-10 lg:py-14">

        @yield('content')

        @include('partials.image-tools')

        <footer class="mt-10 text-center text-sm text-slate-500">
            Free online image tools.
        </footer>

    </main>

    @stack('scripts')

</body>
</html>