<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(request()->isSecure() || strtolower(request()->header('x-forwarded-proto', '')) === 'https' || str_contains(request()->header('host', ''), 'trycloudflare.com') || str_contains(request()->header('host', ''), 'ngrok'))
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @endif
    <title>@yield('title', 'E-Voting Kampus') - Sistem Pemira Terenkripsi</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        heading: ['"Outfit"', '"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        navy: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#090d16',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    @if(config('services.turnstile.enabled'))
        <!-- Cloudflare Turnstile Anti-Bot Security Widget -->
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.025em;
        }
        .font-mono, code, kbd {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>

    <!-- Client-side Anti-Clickjacking Frame Buster -->
    <script>
        if (top !== self) {
            top.location = self.location;
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col antialiased">

    @if(Auth::guard('web')->check())
        <!-- ========================================== -->
        <!-- DESKTOP ADMIN WORKSTATION SIDEBAR (LG+)    -->
        <!-- ========================================== -->
        <aside class="hidden lg:flex flex-col w-64 xl:w-72 bg-navy-950 border-r border-slate-800 fixed inset-y-0 left-0 z-50 shadow-2xl">
            <!-- Brand Header -->
            <div class="h-20 px-5 flex items-center border-b border-slate-800/80 bg-navy-900/60">
                <a href="{{ route('admin.dashboard') }}" class="block">
                    <x-app-logo size="sm" variant="full" theme="dark" title="PEMIRA POLITEKNIK NEGERI BENGKALIS" subtitle="Workstation KPR &amp; PanWas" />
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
                <!-- Group 1: Pusat Kendali -->
                <div>
                    <span class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-2">Pusat Kendali</span>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Dashboard Admin</span>
                        </a>

                        <a href="{{ route('admin.quick_count') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.quick_count') ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.quick_count') ? 'text-amber-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span>Grafik Quick Count</span>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        </a>
                        <a href="{{ route('admin.radar.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.radar.*') ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.radar.*') ? 'text-rose-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                                </svg>
                                <span>Radar Partisipasi</span>
                            </div>
                            <span class="text-[10px] bg-rose-500/20 text-rose-300 border border-rose-500/30 px-1.5 py-0.5 rounded font-bold">Mobilisasi</span>
                        </a>
                    </div>
                </div>

                <!-- Group 2: Master Data Pemira (10 Ormawa Politeknik Negeri Bengkalis) -->
                <div>
                    <span class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-2">Master Data Pemira</span>
                    <div class="space-y-1">
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <span>Kategori Ormawa</span>
                            </div>
                            <span class="text-[10px] bg-slate-800 text-slate-300 px-1.5 py-0.5 rounded font-mono font-bold">10</span>
                        </a>

                        <a href="{{ route('admin.candidates.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.candidates.*') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Paslon & Kandidat</span>
                        </a>

                        <a href="{{ route('admin.voters.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.voters.index') ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>DPT &amp; Akun Pemilih</span>
                            </div>
                            <span class="text-[10px] bg-slate-800 text-emerald-400 px-1.5 py-0.5 rounded font-mono font-bold">Sah DPT</span>
                        </a>

                        <a href="{{ route('admin.voters.dps') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.voters.dps') ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span>Saringan DPS Kampus</span>
                        </a>

                        <a href="{{ route('admin.fase.panel') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.fase.*') ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Fase Sistem Pemira</span>
                            </div>
                            <span class="text-[10px] bg-blue-500/20 text-blue-300 border border-blue-500/30 px-1.5 py-0.5 rounded font-mono font-bold">4 Fase</span>
                        </a>

                        <a href="{{ route('helpdesk.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('helpdesk.*') ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>Helpdesk Sengketa</span>
                            </div>
                            <span class="text-[10px] bg-amber-500/20 text-amber-300 border border-amber-500/30 px-1.5 py-0.5 rounded font-mono font-bold">KTM</span>
                        </a>
                    </div>
                </div>

                <!-- Group 3: Keamanan & Audit -->
                <div>
                    <span class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-2">Audit & Forensik</span>
                    <div class="space-y-1">
                        <a href="{{ route('admin.audit_logs') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.audit_logs') ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Log Audit Sistem</span>
                        </a>

                        <a href="{{ route('admin.reports.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.reports.*') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Berita Acara Tetap</span>
                            </div>
                            <span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-1.5 py-0.5 rounded font-bold">Cetak</span>
                        </a>

                        <a href="{{ route('verify_ballot') }}" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:bg-slate-800/70 hover:text-white transition">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Audit Kuitansi Suara</span>
                            </div>
                            <span class="text-[10px] text-slate-500">&nearr;</span>
                        </a>

                        <a href="{{ route('quick_count') }}" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:bg-slate-800/70 hover:text-white transition">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span>Quick Count Publik</span>
                            </div>
                            <span class="text-[10px] text-slate-500">&nearr;</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar User Profile Footer -->
            <div class="p-4 border-t border-slate-800 bg-navy-900/60">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1 mr-2">
                        <div class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-emerald-400 font-semibold truncate">Super Admin KPR (Komisi Pemilihan Raya)</div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar Admin" class="p-2 rounded-xl bg-rose-600/20 text-rose-300 hover:bg-rose-600 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
    @endif

    <!-- ========================================== -->
    <!-- TOP NAVIGATION BAR (RESPONSIVE)            -->
    <!-- ========================================== -->
    <header class="bg-navy-900 border-b border-slate-800 text-white sticky top-0 z-40 shadow-md {{ Auth::guard('web')->check() ? 'lg:pl-64 xl:pl-72' : '' }}">
        
        @if(Auth::guard('web')->check())
            <!-- DESKTOP TOP UTILITY BAR FOR ADMIN (LG+) -->
            <div class="hidden lg:flex items-center justify-between h-16 px-8">
                <!-- Breadcrumbs & Status -->
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Sistem Pemira Politeknik Negeri Bengkalis Siap</span>
                    </span>
                    <span class="text-slate-400 text-xs font-medium">Panitia KPR &amp; PanWas Workstation</span>
                </div>

                <!-- Right Utility Shortcuts -->
                <div class="flex items-center space-x-4">
                    <div class="text-xs text-slate-300 font-mono bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700">
                        {{ now()->timezone('Asia/Jakarta')->translatedFormat('d M Y - H:i') }} WIB
                    </div>
                    <a href="{{ route('admin.quick_count') }}" class="px-3 py-1.5 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold hover:bg-amber-500 hover:text-white transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Live Quick Count</span>
                    </a>
                    <a href="{{ route('quick_count') }}" target="_blank" class="text-xs font-medium text-slate-400 hover:text-white transition">
                        Buka Layar Publik &rarr;
                    </a>
                </div>
            </div>
        @endif

        <!-- STANDARD NAVBAR (FOR MOBILE & VOTERS / PUBLIC) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between {{ Auth::guard('web')->check() ? 'lg:hidden' : '' }}">
            <a href="{{ Auth::guard('voter')->check() ? route('voter.dashboard') : (Auth::guard('web')->check() ? route('admin.dashboard') : route('voter.login')) }}" class="flex items-center space-x-3">
                <x-app-logo size="sm" variant="full" theme="dark" title="PEMIRA POLBENG" subtitle="Sistem E-Voting Kampus" />
            </a>

            <!-- Desktop Nav Menu (For Voters / Public) -->
            <div class="hidden sm:flex items-center space-x-5">
                <a href="{{ route('voter.login', ['tab' => 'register']) }}" class="text-xs sm:text-sm font-semibold text-slate-200 hover:text-white transition">
                    Pendaftaran Pemilih
                </a>
                <a href="{{ route('nim.status.form') }}" class="text-xs sm:text-sm text-slate-300 hover:text-white transition">
                    Cek Status NIM
                </a>
                <a href="{{ route('verify_ballot') }}" class="text-xs sm:text-sm text-slate-300 hover:text-white transition">
                    Audit Kuitansi
                </a>
                <a href="{{ route('quick_count') }}" class="text-xs sm:text-sm text-slate-300 hover:text-white transition">
                    Quick Count
                </a>
                @if(!Auth::guard('voter')->check() && !Auth::guard('web')->check())
                    <button type="button" onclick="openHelpdeskModal('chat')" class="text-xs sm:text-sm font-semibold text-slate-200 hover:text-white transition flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Bantuan &amp; Chat</span>
                    </button>
                @endif

                @if(Auth::guard('voter')->check())
                    <div class="h-6 w-px bg-slate-700"></div>
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <div class="text-xs font-bold text-white">{{ Auth::guard('voter')->user()->name }}</div>
                            <div class="text-xs text-slate-400">NIM: {{ Auth::guard('voter')->user()->nim }}</div>
                        </div>
                        <form action="{{ route('voter.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600/20 text-rose-300 border border-rose-600/30 hover:bg-rose-600 hover:text-white text-xs font-semibold transition">
                                Keluar
                            </button>
                        </form>
                    </div>
                @elseif(Auth::guard('web')->check())
                    <div class="h-6 w-px bg-slate-700"></div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1 rounded-lg bg-teal-500/20 text-teal-300 border border-teal-500/30 text-xs font-bold hover:bg-teal-500 hover:text-white transition">
                            Dashboard
                        </a>
                        <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-600/20 text-rose-300 border border-rose-600/30 hover:bg-rose-600 hover:text-white text-xs font-semibold transition">
                                Keluar
                            </button>
                        </form>
                    </div>
                @else
                    {{-- Tombol Login Admin disembunyikan dari navbar publik pemilih --}}
                @endif
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="flex sm:hidden items-center">
                <button type="button" 
                        onclick="toggleMobileMenu()" 
                        class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none transition" 
                        aria-label="Toggle menu">
                    <svg id="hamburgerIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="closeMenuIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div id="mobileMenu" class="hidden sm:hidden border-t border-slate-800 bg-navy-950/95 backdrop-blur-md px-4 pt-3 pb-5 space-y-2">
            <a href="{{ route('voter.login', ['tab' => 'register']) }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 transition">
                Pendaftaran Pemilih
            </a>
            <a href="{{ route('nim.status.form') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 transition">
                Cek Status Kepemilikan NIM
            </a>
            <a href="{{ route('verify_ballot') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 transition">
                Audit Kuitansi Suara
            </a>
            <a href="{{ route('quick_count') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 transition">
                Hasil Quick Count
            </a>
            @if(!Auth::guard('voter')->check() && !Auth::guard('web')->check())
                <button type="button" onclick="toggleMobileMenu(); openHelpdeskModal('chat');" class="w-full text-left px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 transition flex items-center justify-between">
                    <span>Pusat Bantuan &amp; Live Chat</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                </button>
            @endif

            @if(Auth::guard('voter')->check())
                <div class="pt-3 border-t border-slate-800">
                    <div class="px-3 py-1 mb-2">
                        <div class="text-sm font-bold text-white">{{ Auth::guard('voter')->user()->name }}</div>
                        <div class="text-xs text-slate-400">NIM: {{ Auth::guard('voter')->user()->nim }}</div>
                    </div>
                    <form action="{{ route('voter.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-sm font-semibold text-rose-400 hover:bg-rose-950/30 transition">
                            Keluar dari Bilik Suara
                        </button>
                    </form>
                </div>
            @elseif(Auth::guard('web')->check())
                <div class="pt-3 border-t border-slate-800 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-bold text-teal-300 hover:bg-teal-950/30 transition">
                        ⚙️ Panel Admin KPR (Komisi Pemilihan Raya)
                    </a>
                    <a href="{{ route('admin.quick_count') }}" class="block px-3 py-2 rounded-xl text-sm font-bold text-amber-300 hover:bg-amber-950/30 transition">
                        📊 Grafik Quick Count Real-Time
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800 transition">
                        🏛️ Kelola Kategori Ormawa (10 Ormawa)
                    </a>
                    <a href="{{ route('admin.candidates.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800 transition">
                        👥 Kelola Paslon
                    </a>
                    <a href="{{ route('admin.voters.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800 transition">
                        📋 DPT &amp; Akun Pemilih
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-xl text-sm font-bold text-emerald-400 hover:bg-emerald-950/30 transition">
                        📑 Berita Acara &amp; Laporan Tetap (Cetak)
                    </a>
                    <a href="{{ route('admin.radar.index') }}" class="block px-3 py-2 rounded-xl text-sm font-bold text-rose-400 hover:bg-rose-950/30 transition">
                        🎯 Radar Partisipasi &amp; Mobilisasi Pemilih
                    </a>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-sm font-bold text-rose-400 hover:bg-rose-950/30 transition">
                            🚪 Keluar Admin
                        </button>
                    </form>
                </div>
            @else
                {{-- Tombol Login Admin disembunyikan dari menu mobile publik pemilih --}}
            @endif
        </div>
    </header>

    <!-- Flash Alerts -->
    <div class="{{ Auth::guard('web')->check() ? 'lg:pl-64 xl:pl-72 max-w-full px-4 sm:px-6 lg:px-10' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' }} mt-4 w-full">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center space-x-3 mb-4 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center space-x-3 mb-4 shadow-sm">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('status'))
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 flex items-center space-x-3 mb-4 shadow-sm">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium">{{ session('status') }}</span>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow w-full {{ Auth::guard('web')->check() ? 'lg:pl-64 xl:pl-72 px-4 sm:px-6 lg:px-10 py-8 max-w-full' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6' }}">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 text-slate-500 py-6 mt-12 text-center text-xs {{ Auth::guard('web')->check() ? 'lg:pl-64 xl:pl-72' : '' }}">
        <div class="max-w-7xl mx-auto px-4 space-y-2">
            <p class="font-semibold text-slate-700">Komisi Pemilihan Raya (KPR) &bull; Politeknik Negeri Bengkalis &copy; {{ date('Y') }}</p>
            <p class="text-slate-400">Dilindungi dengan Enkripsi SHA-256 &amp; Protokol Kotak Suara Anonim Terpisah (Secret Ballot Engine).</p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-slate-500 text-[11px]">
                <a href="{{ route('voter.login') }}" class="hover:text-slate-800 transition">Bilik Pemilih</a>
                <span class="text-slate-300">&bull;</span>
                <a href="{{ route('quick_count') }}" class="hover:text-slate-800 transition">Live Quick Count</a>
                <span class="text-slate-300">&bull;</span>
                <a href="{{ route('verify_ballot') }}" class="hover:text-slate-800 transition">Audit Kuitansi Suara</a>
                <span class="text-slate-300">&bull;</span>
                <button type="button" onclick="openCookiePreferences()" class="hover:text-slate-800 transition underline underline-offset-2 text-slate-500">
                    Pengaturan Cookie
                </button>
                @if(!Auth::guard('voter')->check() && !Auth::guard('web')->check())
                    <span class="text-slate-300">&bull;</span>
                    <button type="button" onclick="openHelpdeskModal('chat')" class="text-slate-600 hover:text-slate-900 font-semibold transition">
                        <span>Pusat Bantuan &amp; Chat</span>
                    </button>
                @endif
            </div>
        </div>
    </footer>

    @if(!Auth::guard('web')->check() && !Auth::guard('voter')->check())
        <!-- Floating Helpdesk & Modal Pusat Bantuan Pemilih Mahasiswa (HANYA DI HALAMAN LUAR / PUBLIK) -->
        <x-voter-helpdesk />
    @endif

    <!-- Komponen Privasi & Pengelolaan Cookie (UU PDP No. 27/2022) -->
    <x-cookie-consent />

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const hamburgerIcon = document.getElementById('hamburgerIcon');
            const closeIcon = document.getElementById('closeMenuIcon');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                hamburgerIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
                hamburgerIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
