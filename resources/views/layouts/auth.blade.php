<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') — IKI IT Helpdesk</title>

    <!-- Google Fonts: Plus Jakarta Sans & Fira Code -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['"Fira Code"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc7fb',
                            400: '#38a9f6',
                            500: '#0e8ce4',
                            600: '#026fc3',
                            700: '#03589e',
                            800: '#074b82',
                            900: '#0c3f6d',
                            950: '#072746',
                        },
                        navy: {
                            800: '#14253d',
                            900: '#0d1b2d',
                            950: '#08121f',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --color-primary: #072746;
            --color-secondary: #026fc3;
            --color-cta: #f97316;
            --color-background: #08121f;
            --color-text: #0f172a;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body class="min-h-screen relative overflow-x-hidden bg-gradient-to-br from-navy-950 via-slate-950 to-brand-950 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 text-slate-800 antialiased selection:bg-brand-500 selection:text-white">
    <!-- Ambient glowing backgrounds & maritime grid overlay -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.05)_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none opacity-40"></div>

    <div class="relative z-10 w-full">
        @yield('content')
    </div>
</body>
</html>
