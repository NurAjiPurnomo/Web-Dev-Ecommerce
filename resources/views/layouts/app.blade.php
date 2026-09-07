<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Toko Online - Belanja Online Produk Berkualitas & Terpercaya</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Toko Online - Platform e-commerce terpercaya untuk kebutuhan fashion, gadget, aksesoris, dan peralatan rumah tangga dengan penawaran terbaik dan garansi resmi.">
    <meta name="keywords" content="toko online, e-commerce, promo, belanja online, fashion, gadget">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN with Typography Plugin -->
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    
    <style>
        [x-cloak] { display: none !important; }
        html {
            scroll-behavior: smooth;
        }
        body {
            width: 100%;
            overflow-x: clip;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        ::selection {
            background-color: #1d4ed8;
            color: #ffffff;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    @php
        $isAuthPage = request()->routeIs(['login', 'register', 'password.*', 'otp.*', 'admin.login']);
    @endphp

    @if(!$isAuthPage)
        @php
            try {
                $topAnnouncement = \App\Models\Announcement::where('status', 'ditayangkan')
                    ->where(function($q) {
                        $q->where('type', 'banner')->orWhere('type', 'popup');
                    })
                    ->latest()
                    ->first();
            } catch (\Throwable $e) {
                $topAnnouncement = null;
            }
        @endphp

        @if($topAnnouncement)
            <!-- TOP ANNOUNCEMENT BROADCAST BANNER -->
            <div x-data="{ bannerOpen: true }" x-show="bannerOpen" class="bg-blue-900 border-b border-blue-950 text-white text-xs py-2.5 px-4 shadow-xs relative z-50">
                <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2 font-medium truncate">
                        <span class="bg-amber-400 text-slate-950 font-black text-[10px] uppercase px-2 py-0.5 rounded shrink-0">PROMO</span>
                        <span class="font-extrabold truncate">{{ $topAnnouncement->title }}</span>
                        @if($topAnnouncement->content)
                            <span class="hidden md:inline text-blue-100 font-normal truncate">— {{ $topAnnouncement->content }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('promo') }}" class="underline hover:text-amber-300 font-bold text-[11px] hidden sm:inline">Lihat Detail Promo →</a>
                        <button type="button" @click="bannerOpen = false" class="text-blue-200 hover:text-white font-bold p-0.5 cursor-pointer">✕</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Navbar Component -->
        @include('components.navbar')
    @endif

    <!-- Global Floating Toast Notification (3-Second Pop-up) -->
    @include('components.toast-notification')

    <!-- Main Content Yield -->
    <main class="flex-grow">
        @yield('content')
    </main>

    @if(!$isAuthPage)
        <!-- Footer Component -->
        @include('components.footer')
    @endif

    <!-- GLOBAL CONFIRM MODAL -->
    <div x-data="{ show: false, title: 'Konfirmasi', message: '', confirmText: 'Ya, Lanjutkan', action: null }"
         @open-confirm.window="show = true; title = $event.detail.title || 'Konfirmasi'; message = $event.detail.message; confirmText = $event.detail.confirmText || 'Ya, Lanjutkan'; action = $event.detail.action"
         x-show="show" 
         style="display: none;"
         class="fixed inset-0 z-[100] flex items-center justify-center px-4"
    >
        <div x-show="show" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="show = false"></div>
        <div x-show="show" x-transition.scale.origin.bottom class="relative bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-6 text-center space-y-4">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-extrabold text-slate-900" x-text="title"></h3>
                <p class="text-sm text-slate-500 leading-relaxed" x-text="message"></p>
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="show = false" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-colors cursor-pointer">Batal</button>
                    <button type="button" @click="show = false; action()" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl transition-colors cursor-pointer shadow-md shadow-red-200" x-text="confirmText"></button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
