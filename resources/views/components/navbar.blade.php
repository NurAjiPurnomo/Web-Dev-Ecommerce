<header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-2xs transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4 md:gap-8">
            
            <!-- Left: Brand Logo & Mobile Hamburger Toggle -->
            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Professional Hamburger Button (Visible on mobile/tablet < lg) -->
                <button 
                    type="button" 
                    @click.stop="mobileMenuOpen = !mobileMenuOpen" 
                    class="lg:hidden p-2 text-slate-700 hover:text-blue-700 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer"
                    title="Buka Menu Navigasi"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 rounded-xl bg-blue-700 text-white flex items-center justify-center shadow-sm group-hover:bg-blue-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base sm:text-lg font-bold text-slate-900 leading-none tracking-tight">
                            Toko<span class="text-blue-700">Online</span>
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium leading-tight">Official Store</span>
                    </div>
                </a>
            </div>

            <!-- Middle: E-Commerce Search Bar -->
            <div class="flex-1 max-w-2xl hidden md:block">
                <form action="{{ route('catalog') }}" method="GET" class="flex items-center">
                    <div class="relative w-full flex items-center">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Cari produk, brand, atau kategori..." 
                            class="w-full bg-slate-100 hover:bg-slate-100/80 focus:bg-white text-slate-800 text-xs sm:text-sm rounded-lg pl-10 pr-20 py-2 border border-slate-200 focus:border-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-700 transition-all"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <button 
                            type="submit" 
                            class="absolute inset-y-1 right-1 px-4 bg-blue-700 hover:bg-blue-800 text-white font-semibold text-xs rounded-md transition-colors flex items-center gap-1 cursor-pointer"
                        >
                            Cari Produk
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Navigation Links & Actions -->
            <div class="flex items-center gap-5 sm:gap-6">
                
                @php
                    $isHome = request()->routeIs('home') && !request()->has('promo');
                    $isCatalog = request()->routeIs('catalog');
                    $isPromo = request()->has('promo') || request()->routeIs('promo');
                @endphp

                <!-- Nav Links -->
                <nav class="hidden lg:flex items-center gap-6 text-xs sm:text-sm font-semibold">
                    <a href="{{ route('home') }}" class="{{ $isHome ? 'text-blue-700 font-bold border-b-2 border-blue-700' : 'text-slate-600 hover:text-blue-700 transition-colors' }} pb-0.5">
                        Beranda
                    </a>
                    <a href="{{ route('catalog') }}" class="{{ $isCatalog ? 'text-blue-700 font-bold border-b-2 border-blue-700' : 'text-slate-600 hover:text-blue-700 transition-colors' }} pb-0.5">
                        Katalog
                    </a>
                    <a href="{{ route('promo') }}" class="{{ $isPromo ? 'text-blue-700 font-bold border-b-2 border-blue-700' : 'text-slate-600 hover:text-blue-700 transition-colors' }} pb-0.5 flex items-center gap-1">
                        Promo
                        <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded">HOT</span>
                    </a>

                    
                    @if(isset($navbarPages))
                        @foreach($navbarPages as $np)
                            <a href="{{ route('page.show', $np->slug) }}" class="{{ request()->is('page/'.$np->slug) ? 'text-blue-700 font-bold border-b-2 border-blue-700' : 'text-slate-600 hover:text-blue-700 transition-colors' }} pb-0.5">
                                {{ $np->title }}
                            </a>
                        @endforeach
                    @endif
                </nav>

                <div class="h-5 w-px bg-slate-200 hidden lg:block"></div>

                <!-- Action Icons (Cart & User) -->
                @php
                    $cartSession = session()->get('cart', []);
                    $cartCount = array_sum(array_column($cartSession, 'qty'));
                @endphp
                <div class="flex items-center gap-2" x-data="{ count: {{ $cartCount }} }" @cart-count-updated.window="count = $event.detail.count">
                    
                    @if(auth()->check())
                        <a href="{{ route('wishlist') }}" class="relative p-2 text-slate-700 hover:text-red-500 hover:bg-slate-100 rounded-lg transition-colors" title="Wishlist">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </a>
                    @endif

                    <a href="{{ route('cart') }}" class="relative p-2 text-slate-700 hover:text-blue-700 hover:bg-slate-100 rounded-lg transition-colors" title="Keranjang Belanja">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <span 
                            x-show="count > 0" 
                            x-cloak 
                            x-text="count" 
                            class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 bg-blue-700 text-white font-bold text-[10px] rounded-full flex items-center justify-center border border-white"
                        >
                        </span>
                    </a>

                    <!-- Notification Bell Dropdown / Login Redirect for Guest -->
                    @php
                        $isLoggedIn = session()->has('user') || auth()->check();
                        $currentUserId = auth()->id() ?? (session('user.id') ?? null);
                        
                        if ($isLoggedIn && $currentUserId) {
                            try {
                                $activeAnnouncements = \App\Models\Announcement::where('status', 'ditayangkan')
                                    ->where(function($q) use ($currentUserId) {
                                        $q->where('user_id', $currentUserId)
                                          ->orWhere(function($subQ) {
                                              $subQ->whereNull('user_id')
                                                   ->where('type', '!=', 'notifikasi');
                                          });
                                    })
                                    ->latest()
                                    ->take(5)
                                    ->get();
                            } catch (\Throwable $e) {
                                $activeAnnouncements = collect([]);
                            }
                        } else {
                            $activeAnnouncements = collect([]);
                        }
                        $unreadNotifCount = $activeAnnouncements->count();
                    @endphp

                    @if($isLoggedIn)
                        <div x-data="{ notifOpen: false }" class="relative">
                            <button @click="notifOpen = !notifOpen" @click.outside="notifOpen = false" class="relative p-2 text-slate-700 hover:text-blue-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer" title="Notifikasi Toko">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                @if($unreadNotifCount > 0)
                                    <span class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 bg-red-600 text-white font-bold text-[10px] rounded-full flex items-center justify-center border border-white">
                                        {{ $unreadNotifCount }}
                                    </span>
                                @endif
                            </button>

                            <!-- Notification Dropdown Menu -->
                            <div x-show="notifOpen" x-transition class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50 text-xs space-y-1" style="display: none;">
                                <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                    <span class="font-extrabold text-slate-900 text-xs">Notifikasi Toko</span>
                                    <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">{{ $unreadNotifCount }} Baru</span>
                                </div>
                                <div class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                                    @forelse($activeAnnouncements as $ann)
                                        @php
                                            $notifLink = route('home');
                                            $searchStr = strtolower($ann->title . ' ' . $ann->content);
                                            if (str_contains($searchStr, 'promo') || str_contains($searchStr, 'diskon') || str_contains($searchStr, 'voucher') || str_contains($searchStr, 'vocer')) {
                                                $notifLink = route('promo');
                                            } elseif (str_contains($searchStr, 'pesanan') || str_contains($searchStr, 'order')) {
                                                $notifLink = route('orders');
                                            }
                                        @endphp
                                        <a href="{{ $notifLink }}" class="block px-4 py-2.5 hover:bg-slate-50 transition-colors">
                                            <h5 class="font-bold text-slate-900 leading-snug">{{ $ann->title }}</h5>
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">{{ $ann->content }}</p>
                                            <span class="text-[9px] text-slate-400 font-mono mt-1 block">{{ $ann->created_at->diffForHumans() }}</span>
                                        </a>
                                    @empty
                                        <div class="px-4 py-6 text-center text-slate-400 text-xs">Belum ada notifikasi baru.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Guest Notification Bell (Redirects to Login on Click) -->
                        <a href="{{ route('login') }}" class="relative p-2 text-slate-700 hover:text-blue-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer" title="Masuk untuk melihat Notifikasi Toko">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </a>
                    @endif

                    <!-- User Account / Auth Buttons -->
                    @if(session()->has('user') || auth()->check())
                        @php
                            $user = session('user') ?? auth()->user();
                            $userName = is_array($user) ? $user['name'] : $user->name;
                            $userEmail = is_array($user) ? $user['email'] : $user->email;
                            $userAvatar = is_array($user) ? ($user['avatar'] ?? null) : null;
                            $isAdmin = is_array($user) ? (!empty($user['is_admin'])) : (!empty($user->is_admin));
                        @endphp

                        @if($isAdmin)
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs rounded-lg shadow-2xs transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                Portal Admin
                            </a>
                        @endif

                        <!-- User Profile Dropdown (Hidden on Mobile < sm, visible on Desktop) -->
                        <div x-data="{ open: false }" class="relative hidden sm:block">
                            <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-1.5 focus:outline-none cursor-pointer">
                                @if($userAvatar)
                                    <img src="{{ $userAvatar }}" alt="{{ $userName }}" class="w-7 h-7 rounded-full object-cover shadow-2xs border border-slate-200">
                                @else
                                    <div class="w-7 h-7 rounded-full bg-blue-700 text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                                        {{ strtoupper(substr($userName, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="hidden sm:inline text-xs font-semibold text-slate-700 max-w-[100px] truncate">{{ $userName }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Profile Dropdown Menu -->
                            <div x-show="open" x-transition class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-50 text-xs space-y-0.5" style="display: none;">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="font-bold text-slate-900 truncate">{{ $userName }}</p>
                                    <p class="text-[11px] text-slate-500 truncate">{{ $userEmail }}</p>
                                    @if($isAdmin)
                                        <span class="mt-1 inline-block bg-blue-100 text-blue-800 text-[10px] font-extrabold px-2 py-0.5 rounded">ADMINISTRATOR</span>
                                    @endif
                                </div>

                                @if($isAdmin)
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-blue-700 hover:bg-blue-50 font-extrabold flex items-center justify-between">
                                        <span>⚡ Portal Admin</span>
                                        <span>&rarr;</span>
                                    </a>
                                    <div class="border-t border-slate-100 my-1"></div>
                                @endif

                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">Profil Saya</a>
                                <a href="{{ route('orders') }}" class="block px-4 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">Manajer Pesanan</a>
                                <a href="{{ route('vouchers.mine') }}" class="block px-4 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">Voucher Saya</a>
                                <a href="{{ route('cart') }}" class="block px-4 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">Keranjang Saya</a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 font-medium cursor-pointer">
                                        Keluar dari Akun
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest / Public State: Masuk & Daftar Buttons (Hidden on Mobile < sm) -->
                        <div class="hidden sm:flex items-center gap-2">
                            <a href="{{ route('login') }}" class="px-3.5 py-1.5 text-xs font-semibold text-blue-700 border border-blue-700 rounded-lg hover:bg-blue-50 transition-colors">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="px-3.5 py-1.5 text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 rounded-lg transition-colors shadow-2xs">
                                Daftar
                            </a>
                        </div>
                    @endif
                </div>

            </div>

        </div>

        <!-- Mobile Search Bar & Quick Nav Chips -->
        <div class="pb-2.5 space-y-2 lg:hidden">
            <form action="{{ route('catalog') }}" method="GET" class="relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari produk, brand, atau kategori..." 
                    class="w-full bg-slate-100 text-slate-800 text-xs rounded-full pl-9 pr-4 py-2 border border-slate-200 focus:border-blue-700 focus:outline-none"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>

            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar text-xs">
                <a href="{{ route('home') }}" class="{{ $isHome ? 'bg-blue-700 text-white font-extrabold' : 'bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200' }} px-3 py-1 rounded-full whitespace-nowrap transition-colors">
                    Beranda
                </a>
                <a href="{{ route('catalog') }}" class="{{ $isCatalog ? 'bg-blue-700 text-white font-extrabold' : 'bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200' }} px-3 py-1 rounded-full whitespace-nowrap transition-colors">
                    Katalog
                </a>
                <a href="{{ route('promo') }}" class="{{ $isPromo ? 'bg-blue-700 text-white font-extrabold' : 'bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200' }} px-3 py-1 rounded-full whitespace-nowrap transition-colors inline-flex items-center gap-1">
                    <span>Promo</span>
                    <span class="bg-amber-400 text-slate-950 text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase">HOT</span>
                </a>

            </div>
        </div>
    </div>

    <!-- WORLD-CLASS MOBILE MENU DRAWER OVERLAY -->
    <div 
        x-show="mobileMenuOpen" 
        x-cloak
        class="fixed inset-0 h-screen h-[100vh] z-[9999] lg:hidden flex overflow-hidden"
        style="display: none;"
        @keydown.escape.window="mobileMenuOpen = false"
    >
        <!-- Dark Backdrop Layer (Clicking Backdrop Closes Menu) -->
        <div 
            x-show="mobileMenuOpen" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileMenuOpen = false"
            class="fixed inset-0 h-screen bg-slate-950/70 backdrop-blur-xs"
        ></div>

        <!-- Drawer Content Panel (Fixed 100vh Full Screen Height, Solid White Opaque) -->
        <div 
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed top-0 bottom-0 left-0 h-screen h-[100vh] w-[85%] max-w-xs bg-white opacity-100 shadow-2xl flex flex-col justify-between z-[10000] text-slate-900 overflow-y-auto"
        >
            <div>
                <!-- Header Bar Inside Drawer -->
                <div class="p-4 bg-blue-700 text-white flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-white text-blue-700 flex items-center justify-center font-black text-xs shadow-2xs">
                            TO
                        </div>
                        <span class="font-extrabold text-white text-base tracking-tight">TokoOnline</span>
                    </div>
                    <button 
                        type="button" 
                        @click="mobileMenuOpen = false" 
                        class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center font-bold text-sm cursor-pointer transition-colors"
                    >
                        ✕
                    </button>
                </div>

                <!-- User Card Section -->
                @if(session()->has('user') || auth()->check())
                    @php
                        $u = session('user') ?? auth()->user();
                        $uName = is_array($u) ? $u['name'] : $u->name;
                        $uEmail = is_array($u) ? $u['email'] : $u->email;
                        $uAdmin = is_array($u) ? (!empty($u['is_admin'])) : (!empty($u->is_admin));
                    @endphp
                    <div class="p-4 bg-slate-50 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-blue-700 text-white flex items-center justify-center font-black text-sm shadow-2xs shrink-0">
                                {{ strtoupper(substr($uName, 0, 1)) }}
                            </div>
                            <div class="truncate">
                                <h4 class="font-extrabold text-slate-900 text-sm truncate leading-tight">{{ $uName }}</h4>
                                <p class="text-xs text-slate-500 truncate mt-0.5">{{ $uEmail }}</p>
                                @if($uAdmin)
                                    <span class="inline-block bg-blue-700 text-white text-[9px] font-black px-2 py-0.5 rounded mt-1">ADMINISTRATOR</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-blue-50/60 border-b border-blue-100 space-y-2">
                        <p class="text-xs text-slate-600 font-medium">Masuk ke akun Anda untuk akses fitur lengkap &amp; melacak pesanan.</p>
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('login') }}" class="py-2 text-center text-xs font-bold text-blue-700 bg-white border border-blue-700 rounded-xl hover:bg-blue-50">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="py-2 text-center text-xs font-extrabold text-white bg-blue-700 hover:bg-blue-800 rounded-xl shadow-2xs">
                                Daftar
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Navigation Links -->
                <div class="p-4 space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-2">Navigasi Utama</p>
                    
                    <a href="{{ route('home') }}" class="flex items-center justify-between p-3 rounded-xl font-bold text-xs text-slate-800 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('home') && !request()->has('promo') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <div class="flex items-center gap-3">
                            <span class="p-1.5 bg-blue-100 text-blue-700 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            </span>
                            <span>Beranda Utama</span>
                        </div>
                        <span class="text-slate-400">&rarr;</span>
                    </a>

                    <a href="{{ route('catalog') }}" class="flex items-center justify-between p-3 rounded-xl font-bold text-xs text-slate-800 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('catalog') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <div class="flex items-center gap-3">
                            <span class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            </span>
                            <span>Katalog Produk</span>
                        </div>
                        <span class="text-slate-400">&rarr;</span>
                    </a>

                    <a href="{{ route('promo') }}" class="flex items-center justify-between p-3 rounded-xl font-bold text-xs text-slate-800 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->has('promo') || request()->routeIs('promo') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <div class="flex items-center gap-3">
                            <span class="p-1.5 bg-amber-100 text-amber-700 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                            </span>
                            <span>Promo Spesial</span>
                        </div>
                        <span class="bg-amber-400 text-slate-950 text-[9px] font-black px-2 py-0.5 rounded-full">HOT</span>
                    </a>



                    @if(isset($navbarPages))
                        @foreach($navbarPages as $np)
                            <a href="{{ route('page.show', $np->slug) }}" class="flex items-center justify-between p-3 rounded-xl font-bold text-xs text-slate-800 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->is('page/'.$np->slug) ? 'bg-blue-50 text-blue-700' : '' }}">
                                <div class="flex items-center gap-3">
                                    <span class="p-1.5 bg-slate-100 text-slate-700 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </span>
                                    <span>{{ $np->title }}</span>
                                </div>
                                <span class="text-slate-400">&rarr;</span>
                            </a>
                        @endforeach
                    @endif

                    @if(session()->has('user') || auth()->check())
                        <div class="pt-3 mt-3 border-t border-slate-100 space-y-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-2">Akun Pelanggan</p>
                            
                            @if(!empty($uAdmin))
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between p-3 rounded-xl font-extrabold text-xs text-blue-700 bg-blue-50 border border-blue-100">
                                    <div class="flex items-center gap-3">
                                        <span class="p-1.5 bg-blue-700 text-white rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                        </span>
                                        <span>Portal Admin</span>
                                    </div>
                                    <span>&rarr;</span>
                                </a>
                            @endif

                            <a href="{{ route('orders') }}" class="flex items-center justify-between p-3 rounded-xl font-bold text-xs text-slate-800 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('orders') ? 'bg-blue-50 text-blue-700' : '' }}">
                                <div class="flex items-center gap-3">
                                    <span class="p-1.5 bg-purple-100 text-purple-700 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </span>
                                    <span>Manajer Pesanan</span>
                                </div>
                                <span class="text-slate-400">&rarr;</span>
                            </a>

                            <a href="{{ route('profile') }}" class="flex items-center justify-between p-3 rounded-xl font-bold text-xs text-slate-800 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('profile') ? 'bg-blue-50 text-blue-700' : '' }}">
                                <div class="flex items-center gap-3">
                                    <span class="p-1.5 bg-rose-100 text-rose-700 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </span>
                                    <span>Profil Saya</span>
                                </div>
                                <span class="text-slate-400">&rarr;</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bottom Action: Logout -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 space-y-3">
                @if(session()->has('user') || auth()->check())
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 text-center text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Keluar dari Akun</span>
                        </button>
                    </form>
                @endif
                <div class="text-center text-[10px] text-slate-400 font-medium">
                    TokoOnline Official Store &copy; 2026
                </div>
            </div>
        </div>
    </div>
</header>
