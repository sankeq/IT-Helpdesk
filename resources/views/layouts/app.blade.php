<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — IKI IT Helpdesk</title>

    <!-- Google Fonts: Plus Jakarta Sans & Fira Code -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN Fallback + Design System Tokens -->
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
                            950: '#072746', // Maritime Deep Navy
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
            --color-cta: #10b981;
            --color-background: #f8fafc;
            --color-text: #0f172a;

            --space-xs: 4px;
            --space-sm: 8px;
            --space-md: 16px;
            --space-lg: 24px;
            --space-xl: 32px;
            --space-2xl: 48px;

            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-background);
            color: var(--color-text);
        }

        /* Status Badges */
        .status-badge-open { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .status-badge-assigned { background-color: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .status-badge-in_progress { background-color: #fefce8; color: #a16207; border: 1px solid #fef08a; }
        .status-badge-pending { background-color: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .status-badge-resolved { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .status-badge-closed { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .status-badge-cancelled { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Priority Badges */
        .priority-badge-P1, .priority-badge-critical { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; font-weight: 700; }
        .priority-badge-P2, .priority-badge-high { background-color: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
        .priority-badge-P3, .priority-badge-medium { background-color: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; }
        .priority-badge-P4, .priority-badge-low { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }

        /* Accessibility & Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 selection:bg-brand-500 selection:text-white antialiased">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebarBackdrop"
         class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs hidden lg:hidden transition-opacity duration-300 motion-reduce:transition-none"
         aria-hidden="true"></div>

    <!-- Left Sidebar Navigation -->
    <aside id="mainSidebar"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-brand-950 via-brand-900 to-navy-950 text-white flex flex-col border-r border-brand-800/80 shadow-2xl transition-transform duration-300 ease-in-out motion-reduce:transition-none -translate-x-full lg:translate-x-0"
           aria-label="Navigasi Utama">

        <!-- Sidebar Header: Logo & Branding -->
        <div class="h-16 px-5 flex items-center justify-between border-b border-white/10 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group rounded-xl p-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400">
                <div class="w-9 h-9 rounded-xl bg-blue-500/20 border border-blue-400/40 flex items-center justify-center text-blue-300 shadow-inner group-hover:bg-blue-500/30 transition-all shrink-0">
                    <!-- Maritime Anchor Icon -->
                    <svg class="w-5 h-5 text-blue-300 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20m0 0a8 8 0 01-8-8H2m10 8a8 8 0 008-8h-2M12 2a3 3 0 100 6 3 3 0 000-6z"/>
                    </svg>
                </div>
                <div class="leading-tight">
                    <div class="text-sm font-extrabold tracking-tight text-white group-hover:text-blue-200 transition-colors whitespace-nowrap">
                        IKI IT Helpdesk
                    </div>
                    <div class="text-[10px] text-blue-300/80 font-medium tracking-wider uppercase whitespace-nowrap">
                        PT IKI (Persero)
                    </div>
                </div>
            </a>

            <!-- Mobile Close Button -->
            <button type="button"
                    id="sidebarCloseBtn"
                    class="lg:hidden p-2 rounded-xl text-blue-200 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 cursor-pointer"
                    aria-label="Tutup Menu Navigasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="sr-only">Tutup Navigasi Samping</span>
            </button>
        </div>

        <!-- Primary Action CTA Button: Buat Tiket -->
        <div class="p-4 shrink-0">
            <a href="{{ route('tickets.create') }}"
               class="flex items-center justify-center w-full px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-emerald-600/30 transition-all duration-200 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 group">
                <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Tiket Baru</span>
            </a>
        </div>

        <!-- Sidebar Scrollable Navigation Menus -->
        <nav class="flex-1 px-3 py-2 space-y-6 overflow-y-auto">

            <!-- Group 1: Menu Utama -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-bold text-blue-300/70 uppercase tracking-widest">
                    Menu Utama
                </div>
                <div class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                       class="relative flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 {{ request()->routeIs('dashboard') ? 'bg-white/15 text-white shadow-xs border border-white/15 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-brand-400 before:rounded-r' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-3 shrink-0 {{ request()->routeIs('dashboard') ? 'text-blue-300' : 'text-blue-300/70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Daftar Tiket -->
                    <a href="{{ route('tickets.index') }}"
                       class="relative flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 {{ request()->routeIs('tickets.index*') && !request()->routeIs('tickets.create') ? 'bg-white/15 text-white shadow-xs border border-white/15 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-brand-400 before:rounded-r' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-3 shrink-0 {{ request()->routeIs('tickets.index*') && !request()->routeIs('tickets.create') ? 'text-blue-300' : 'text-blue-300/70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                        <span>Daftar Tiket</span>
                    </a>

                    <!-- Basis Pengetahuan -->
                    <a href="{{ route('knowledge-base.index') }}"
                       class="relative flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 {{ request()->routeIs('knowledge-base.*') ? 'bg-white/15 text-white shadow-xs border border-white/15 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-brand-400 before:rounded-r' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-3 shrink-0 {{ request()->routeIs('knowledge-base.*') ? 'text-blue-300' : 'text-blue-300/70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>Basis Pengetahuan</span>
                    </a>
                </div>
            </div>

            <!-- Group 2: Manajemen & Tata Kelola (Conditional Permissions) -->
            @if(auth()->check() && (auth()->user()->hasPermission('assets.view') || auth()->user()->hasPermission('assets.manage') || auth()->user()->hasPermission('approvals.decide') || auth()->user()->hasPermission('tickets.view_all') || auth()->user()->hasPermission('reports.view')))
            <div>
                <div class="px-3 mb-2 text-[10px] font-bold text-blue-300/70 uppercase tracking-widest">
                    Manajemen &amp; Operasi
                </div>
                <div class="space-y-1">
                    <!-- Aset TI -->
                    @if(auth()->user()->hasPermission('assets.view') || auth()->user()->hasPermission('assets.manage'))
                        <a href="{{ route('assets.index') }}"
                           class="relative flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 {{ request()->routeIs('assets.*') ? 'bg-white/15 text-white shadow-xs border border-white/15 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-brand-400 before:rounded-r' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 mr-3 shrink-0 {{ request()->routeIs('assets.*') ? 'text-blue-300' : 'text-blue-300/70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>Aset TI</span>
                        </a>
                    @endif

                    <!-- Persetujuan -->
                    @if(auth()->user()->hasPermission('approvals.decide') || auth()->user()->hasPermission('tickets.view_all'))
                        <a href="{{ route('approvals.index') }}"
                           class="relative flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 {{ request()->routeIs('approvals.*') ? 'bg-white/15 text-white shadow-xs border border-white/15 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-brand-400 before:rounded-r' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-3 shrink-0 {{ request()->routeIs('approvals.*') ? 'text-blue-300' : 'text-blue-300/70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Persetujuan</span>
                            </div>
                        </a>
                    @endif

                    <!-- Laporan & Analitik -->
                    @if(auth()->user()->hasPermission('reports.view'))
                        <a href="{{ route('reports.index') }}"
                           class="relative flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 {{ request()->routeIs('reports.*') ? 'bg-white/15 text-white shadow-xs border border-white/15 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-brand-400 before:rounded-r' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 mr-3 shrink-0 {{ request()->routeIs('reports.*') ? 'text-blue-300' : 'text-blue-300/70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Laporan &amp; Analitik</span>
                        </a>
                    @endif
                </div>
            </div>
            @endif

        </nav>

        <!-- Sidebar Bottom Area: User Info & Operational Indicator -->
        <div class="p-3 border-t border-white/10 shrink-0 bg-brand-950/60 space-y-2">

            <!-- System Operational Status Indicator -->
            <div class="px-2.5 py-1.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between text-[10px]">
                <div class="flex items-center space-x-1.5">
                    <span class="relative flex h-2 w-2" aria-hidden="true">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-blue-200/90 font-medium">Sistem Helpdesk IKI</span>
                </div>
                <span class="font-mono text-emerald-400 font-bold">Online</span>
            </div>

            @auth
            <!-- User Profile Card in Sidebar -->
            <div class="flex items-center justify-between p-2 rounded-xl bg-white/5 border border-white/10">
                <div class="flex items-center space-x-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-700 to-blue-600 border border-blue-300/40 text-white font-bold flex items-center justify-center text-xs shadow-xs font-mono shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-white truncate">
                            {{ auth()->user()->name }}
                        </div>
                        <div class="text-[10px] text-blue-300/80 truncate">
                            {{ auth()->user()->roles->first()?->label ?? 'User' }}
                            @if(auth()->user()->department)
                                • {{ auth()->user()->department->code ?? auth()->user()->department->name }}
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit"
                            title="Keluar dari sistem"
                            class="p-2 text-blue-200 hover:text-rose-400 hover:bg-white/10 rounded-lg transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400"
                            aria-label="Keluar dari akun">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="sr-only">Keluar</span>
                    </button>
                </form>
            </div>
            @endauth

        </div>

    </aside>

    <!-- Main Application Shell (Desktop has lg:pl-64) -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen lg:pl-64">

        <!-- Top Header Bar -->
        <header class="sticky top-0 z-30 bg-white/85 backdrop-blur-md border-b border-slate-200/90 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-2xs">

            <!-- Left: Mobile Menu Toggle & Context Breadcrumb -->
            <div class="flex items-center space-x-3">
                <!-- Hamburger Button for Mobile -->
                <button type="button"
                        id="mobileSidebarToggle"
                        class="lg:hidden p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 shrink-0"
                        aria-label="Buka Menu Navigasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span class="sr-only">Buka Menu</span>
                </button>

                <!-- Page Context / Operational Tag -->
                <div class="flex items-center space-x-2 text-xs">
                    <div class="hidden sm:flex items-center space-x-2 text-slate-500">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-brand-50 text-brand-700 font-bold text-[10px] uppercase tracking-wider">
                            PT IKI (PERSERO)
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="text-slate-600 font-medium">Pusat Layanan TI &amp; Galangan Kapal</span>
                    </div>
                </div>
            </div>

            <!-- Right: Realtime Clock, Notifications, Profile -->
            <div class="flex items-center space-x-2 sm:space-x-3">

                <!-- Realtime Makassar Clock (WITA) -->
                <div class="hidden md:flex items-center space-x-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200/80 text-[11px] text-slate-600 font-mono">
                    <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="makassarClock">WITA</span>
                </div>

                @auth
                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                @endphp

                <!-- Notification Bell -->
                <a href="{{ route('notifications.index') }}"
                   title="Notifikasi & Peringatan SLA"
                   class="relative p-2 text-slate-600 hover:text-brand-700 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 shrink-0"
                   aria-label="Notifikasi dan Peringatan SLA">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($unreadCount > 0)
                        <span class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-extrabold text-white">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                    <span class="sr-only">Notifikasi ({{ $unreadCount }} belum dibaca)</span>
                </a>

                <!-- User Mini Pill on Topbar -->
                <div class="flex items-center space-x-2 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-700 to-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs font-mono shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden xl:block text-left text-xs leading-tight">
                        <div class="font-bold text-slate-800">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-slate-400 font-medium">{{ auth()->user()->roles->first()?->label ?? 'User' }}</div>
                    </div>
                </div>
                @endauth

            </div>

        </header>

        <!-- Flash Alerts Container -->
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs" role="alert">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 cursor-pointer p-1 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" aria-label="Tutup pemberitahuan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="sr-only">Tutup</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-2xs" role="alert">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="text-xs sm:text-sm font-semibold">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 cursor-pointer p-1 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500" aria-label="Tutup peringatan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="sr-only">Tutup</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-2xs" role="alert">
                    <div class="flex items-center space-x-2 font-bold text-xs mb-1">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Terdapat beberapa kesalahan validasi input:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 ml-5 text-rose-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-[1440px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500 mt-auto">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    © {{ date('Y') }} <span class="font-bold text-slate-700">PT Industri Kapal Indonesia (Persero)</span> — Divisi Teknologi Informasi.
                </div>
                <div class="flex items-center space-x-4 text-slate-400">
                    <span class="font-mono text-[11px]">v1.0 (Laravel 13)</span>
                    <span>•</span>
                    <span class="inline-flex items-center text-emerald-600 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse" aria-hidden="true"></span>
                        Sistem Operasional Galangan
                    </span>
                </div>
            </div>
        </footer>

    </div>

    <!-- Interactive Sidebar Drawer & Clock Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('mainSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const openBtn = document.getElementById('mobileSidebarToggle');
            const closeBtn = document.getElementById('sidebarCloseBtn');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
            }

            function closeSidebar() {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
            }

            if (openBtn) openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
                    closeSidebar();
                }
            });

            // Live Makassar WITA Clock Ticker
            function updateClock() {
                const clockEl = document.getElementById('makassarClock');
                if (!clockEl) return;
                try {
                    const now = new Date();
                    const options = {
                        timeZone: 'Asia/Makassar',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    };
                    const timeStr = new Intl.DateTimeFormat('id-ID', options).format(now);
                    clockEl.textContent = timeStr + ' WITA';
                } catch (e) {
                    // Fallback
                }
            }

            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
    @stack('scripts')
</body>
</html>
