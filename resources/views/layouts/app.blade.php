<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Toko Online - Belanja Online Produk Berkualitas & Terpercaya</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        <!-- STICKY TOP HEADER (Running Ticker + Navbar) -->
        <header class="sticky top-0 z-50">
            <!-- RECENT PURCHASES RUNNING TICKER BANNER -->
            @include('components.recent-purchases-ticker')

            <!-- Navbar Component -->
            @include('components.navbar')
        </header>
    @endif

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

    <!-- GLOBAL FLY TO TARGET ANIMATION SCRIPT (FLY TO CART / FLY TO WISHLIST) -->
    <script>
        window.flyToElement = function(startEl, targetType = 'cart', imgSrc = null) {
            const targetId = targetType === 'wishlist' ? 'nav-wishlist-icon' : 'nav-cart-icon';
            let targetEl = document.getElementById(targetId);
            
            // Fallbacks
            if (!targetEl) {
                targetEl = document.querySelector(targetType === 'wishlist' ? 'a[href*="wishlist"]' : 'a[href*="cart"]');
            }
            if (!startEl) {
                startEl = document.activeElement;
            }
            if (!targetEl || !startEl) return;

            const startRect = startEl.getBoundingClientRect();
            const targetRect = targetEl.getBoundingClientRect();

            // Create flyer element with explicit inline styles for 100% reliability
            const flyer = document.createElement('div');
            flyer.style.position = 'fixed';
            flyer.style.zIndex = '999999';
            flyer.style.pointerEvents = 'none';
            flyer.style.borderRadius = '50%';
            flyer.style.overflow = 'hidden';
            flyer.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.35), 0 8px 10px -6px rgba(0, 0, 0, 0.2)';
            flyer.style.border = targetType === 'wishlist' ? '3px solid #ef4444' : '3px solid #1d4ed8';
            flyer.style.backgroundColor = '#ffffff';

            const flyerSize = 56;
            const startX = startRect.left + (startRect.width / 2) - (flyerSize / 2);
            const startY = startRect.top + (startRect.height / 2) - (flyerSize / 2);

            flyer.style.left = startX + 'px';
            flyer.style.top = startY + 'px';
            flyer.style.width = flyerSize + 'px';
            flyer.style.height = flyerSize + 'px';
            flyer.style.transition = 'left 0.75s cubic-bezier(0.2, 0.8, 0.2, 1), top 0.75s cubic-bezier(0.1, 0.9, 0.2, 1), transform 0.75s ease-in-out, opacity 0.75s ease';
            flyer.style.transform = 'scale(1) rotate(0deg)';
            flyer.style.opacity = '1';

            if (imgSrc) {
                const img = document.createElement('img');
                img.src = imgSrc;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                flyer.appendChild(img);
            } else {
                const iconContainer = document.createElement('div');
                iconContainer.style.width = '100%';
                iconContainer.style.height = '100%';
                iconContainer.style.display = 'flex';
                iconContainer.style.alignItems = 'center';
                iconContainer.style.justifyContent = 'center';
                iconContainer.style.color = targetType === 'wishlist' ? '#ef4444' : '#1d4ed8';
                iconContainer.innerHTML = targetType === 'wishlist'
                    ? '<svg style="width:28px;height:28px;fill:currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'
                    : '<svg style="width:28px;height:28px;fill:none;stroke:currentColor;stroke-width:2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>';
                flyer.appendChild(iconContainer);
            }

            document.body.appendChild(flyer);

            const targetX = targetRect.left + (targetRect.width / 2) - 12;
            const targetY = targetRect.top + (targetRect.height / 2) - 12;

            setTimeout(() => {
                flyer.style.left = targetX + 'px';
                flyer.style.top = targetY + 'px';
                flyer.style.width = '24px';
                flyer.style.height = '24px';
                flyer.style.opacity = '0.3';
                flyer.style.transform = 'scale(0.3) rotate(360deg)';
            }, 30);

            setTimeout(() => {
                flyer.remove();
                targetEl.classList.add('scale-150', 'transition-transform', 'duration-300');
                setTimeout(() => {
                    targetEl.classList.remove('scale-150');
                }, 300);
            }, 770);
        };
    </script>

    @stack('scripts')
</body>
</html>
