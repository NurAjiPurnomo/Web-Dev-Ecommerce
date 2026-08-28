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

    @stack('scripts')
</body>
</html>
