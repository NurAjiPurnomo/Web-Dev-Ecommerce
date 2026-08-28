@extends('layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-12">

    <!-- HERO SLIDER SECTION (Clean, Compact, Solid Blue Theme - 5 Slides Auto Slideshow) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div id="hero-slider" class="relative rounded-2xl overflow-hidden shadow-xs bg-blue-700 text-white min-h-[250px] sm:min-h-[280px] group">
            
            <!-- DYNAMIC SLIDES FROM DATABASE -->
            @forelse($banners as $index => $banner)
                <div class="hero-slide {{ $index === 0 ? '' : 'hidden opacity-0' }} transition-all duration-500 flex items-center min-h-[250px] sm:min-h-[280px] p-6 sm:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center w-full">
                        <div class="lg:col-span-7 space-y-2.5 sm:space-y-3 text-center lg:text-left">
                            <span class="text-blue-200 text-xs font-bold uppercase tracking-wider">
                                {{ $banner->subtitle ?: 'PROMO SPESIAL' }}
                            </span>
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-snug">
                                {{ $banner->title }} <br class="hidden sm:inline">
                                @if($banner->highlight_text)
                                    <span class="text-yellow-300">{{ $banner->highlight_text }}</span>
                                @endif
                            </h1>
                            <p class="text-blue-100 text-xs sm:text-sm max-w-lg mx-auto lg:mx-0 font-normal line-clamp-2">
                                {{ $banner->description }}
                            </p>
                            <div class="pt-1">
                                <a 
                                    href="{{ $banner->button_url ?: route('catalog') }}" 
                                    class="inline-flex items-center gap-2 bg-white hover:bg-blue-50 text-blue-800 font-bold text-xs sm:text-sm px-5 py-2.5 rounded-full shadow-xs transition-colors"
                                >
                                    {{ $banner->button_text ?: 'Belanja Sekarang' }}
                                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <div class="lg:col-span-5 hidden lg:flex justify-center">
                            <div class="relative w-52 h-52 sm:w-56 sm:h-56 rounded-xl overflow-hidden shadow-sm border border-white/20">
                                <img 
                                    src="{{ $banner->image }}" 
                                    alt="{{ $banner->title }}" 
                                    class="w-full h-full object-cover"
                                >
                                <div class="absolute inset-x-0 bottom-0 bg-slate-900/80 p-2.5 text-white">
                                    <span class="bg-blue-700 text-white font-bold text-[10px] px-2 py-0.5 rounded">{{ $banner->category_tag ?: 'Promo Toko' }}</span>
                                    <h3 class="font-semibold text-xs mt-0.5 truncate">{{ $banner->title }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="hero-slide flex items-center min-h-[250px] sm:min-h-[280px] p-6 sm:p-8">
                    <div class="text-center w-full text-white space-y-2">
                        <h1 class="text-2xl font-extrabold">SELAMAT DATANG DI TOKO ONLINE</h1>
                        <p class="text-xs text-blue-100">Koleksi produk berkualitas dengan penawaran terbaik setiap hari.</p>
                        <a href="{{ route('catalog') }}" class="inline-block bg-white text-blue-800 font-bold text-xs px-5 py-2 rounded-full mt-2">Lihat Katalog</a>
                    </div>
                </div>
            @endforelse

            <!-- Slider Arrow Buttons -->
            <button 
                id="prev-slide" 
                type="button" 
                class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/30 hover:bg-black/50 text-white flex items-center justify-center transition-colors focus:outline-none z-20 cursor-pointer"
                aria-label="Previous Slide"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            <button 
                id="next-slide" 
                type="button" 
                class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/30 hover:bg-black/50 text-white flex items-center justify-center transition-colors focus:outline-none z-20 cursor-pointer"
                aria-label="Next Slide"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <!-- Slider Dots Indicator (Dynamic) -->
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-20">
                @foreach($banners as $index => $banner)
                    <button type="button" class="slide-dot {{ $index === 0 ? 'w-6 bg-white' : 'w-1.5 bg-white/40' }} h-1.5 rounded-full hover:bg-white/70 transition-all duration-300 cursor-pointer" aria-label="Go to Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>

        </div>
    </section>

    <!-- TRUST FEATURES STRIP -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                
                <div class="flex items-center gap-3 pt-2 md:pt-0">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Gratis Ongkir Rp0</h4>
                        <p class="text-[11px] text-slate-500">Pengiriman cepat se-Indonesia</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 md:pt-0 md:pl-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">100% Original</h4>
                        <p class="text-[11px] text-slate-500">Garansi uang kembali 100%</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 md:pt-0 md:pl-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Proses Kilat</h4>
                        <p class="text-[11px] text-slate-500">Pengiriman di hari yang sama</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 md:pt-0 md:pl-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Layanan CS 24/7</h4>
                        <p class="text-[11px] text-slate-500">Siap bantu kapan saja</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- KATALOG SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-slate-200 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                    Kategori Pilihan
                </h2>
                @if(isset($selectedCategory) && $selectedCategory)
                    <a href="{{ route('home') }}" class="text-xs font-bold text-blue-700 hover:text-blue-800 transition-colors">
                        Reset Filter (Lihat Semua)
                    </a>
                @endif
            </div>

            <!-- Active Category Notification Banner -->
            @if(isset($selectedCategory) && $selectedCategory)
                <div class="bg-blue-50/80 border border-blue-200 rounded-lg p-3 flex items-center justify-between text-xs sm:text-sm text-blue-900">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>Menampilkan produk untuk kategori: <strong class="text-blue-700 font-extrabold">{{ $selectedCategory }}</strong></span>
                    </div>
                    <a href="{{ route('home') }}" class="text-xs bg-blue-700 hover:bg-blue-800 text-white font-bold px-3 py-1.5 rounded-md transition-colors">
                        Tampilkan Semua
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-5 gap-3 sm:gap-6 justify-items-center">
                @foreach($categories as $category)
                    @php $isActive = isset($selectedCategory) && strcasecmp($selectedCategory, $category['name']) === 0; @endphp
                    <a href="{{ route('catalog', ['category' => $category['name']]) }}" class="flex flex-col items-center group cursor-pointer w-full">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl border transition-all duration-300 transform group-hover:-translate-y-1 group-hover:scale-105 flex items-center justify-center mb-2 {{ $isActive ? 'bg-blue-700 text-white border-blue-700 ring-4 ring-blue-700/20 shadow-md' : ($category['bgLight'] ?? 'bg-blue-50 text-blue-700 border-slate-200 group-hover:bg-blue-700 group-hover:text-white') }}">
                            @if(isset($category['icon']))
                                <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $category['icon'] }}"/>
                                </svg>
                            @else
                                <span class="text-xl sm:text-2xl transition-transform duration-300 group-hover:scale-110">{{ $category['emoji'] ?? '📦' }}</span>
                            @endif
                        </div>
                        <span class="text-xs font-semibold transition-colors text-center truncate w-full {{ $isActive ? 'text-blue-700 font-extrabold' : 'text-slate-700 group-hover:text-blue-700 font-bold' }}">
                            {{ $category['name'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FLASH SALE SECTION -->
    <section id="flash-sale" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            
            <!-- Header Bar -->
            <div class="bg-blue-700 px-4 sm:px-5 py-2.5 flex flex-col sm:flex-row items-center justify-between gap-2.5">
                <div class="flex items-center gap-2">
                    <span class="p-1 bg-white/10 rounded text-white">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.57l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.57l7-10a1 1 0 011.12-.384z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-white uppercase tracking-tight">
                        Flash Sale
                    </h2>
                </div>

                <!-- Countdown Timer -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-blue-100 uppercase tracking-wider mr-1">Berakhir Dalam:</span>
                    <div class="flex items-center gap-1 text-blue-700 font-bold text-xs">
                        <span id="hours" class="bg-white px-2 py-0.5 rounded min-w-[28px] text-center font-extrabold">05</span>
                        <span class="text-white font-bold">:</span>
                        <span id="minutes" class="bg-white px-2 py-0.5 rounded min-w-[28px] text-center font-extrabold">42</span>
                        <span class="text-white font-bold">:</span>
                        <span id="seconds" class="bg-white px-2 py-0.5 rounded min-w-[28px] text-center font-extrabold">19</span>
                    </div>
                </div>
            </div>

            <!-- Grid of 5 Product Cards -->
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($flashSaleProducts as $product)
                        <x-product-card 
                            :id="$product['id']"
                            :title="$product['title']"
                            :price="$product['price']"
                            :originalPrice="$product['originalPrice']"
                            :discount="$product['discount']"
                            :image="$product['image']"
                            :rating="$product['rating']"
                            :sold="$product['sold']"
                            :variants="$product['variants'] ?? []"
                        />
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    <!-- PRODUK TERLARIS (BEST SELLERS) SECTION -->
    @if(isset($bestSellerProducts) && count($bestSellerProducts) > 0)
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-xl overflow-hidden border border-slate-200 shadow-xs">
            
            <!-- Header Bar Blue Solid -->
            <div class="bg-blue-700 px-4 sm:px-6 py-3.5 flex items-center justify-between text-white">
                <div class="space-y-0.5">
                    <h2 class="text-base sm:text-xl font-extrabold uppercase tracking-tight">
                        Produk Terlaris Minggu Ini
                    </h2>
                    <p class="text-xs text-blue-100 hidden sm:block">
                        Produk favorit pilihan 10.000+ pembeli dengan rating tertinggi minggu ini
                    </p>
                </div>
                <a href="{{ route('catalog') }}" class="text-xs font-extrabold text-blue-100 hover:text-white flex items-center gap-1 transition-colors shrink-0">
                    Lihat Semua Katalog
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <!-- Best Sellers Product Cards Grid (5 Columns) -->
            <div class="bg-white p-4 sm:p-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($bestSellerProducts as $product)
                        <x-product-card 
                            :id="$product['id']"
                            :title="$product['title']"
                            :price="$product['price']"
                            :originalPrice="$product['originalPrice']"
                            :discount="$product['discount']"
                            :image="$product['image']"
                            :rating="$product['rating']"
                            :sold="$product['sold']"
                            :rankBadge="$product['rankBadge'] ?? null"
                            :rankColor="$product['rankColor'] ?? 'bg-blue-700 text-white'"
                            :variants="$product['variants'] ?? []"
                        />
                    @endforeach
                </div>
            </div>

        </div>
    </section>
    @endif

    <!-- MID-PAGE SPECIAL CAMPAIGN BANNER -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-blue-700 rounded-xl p-6 sm:p-8 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-xs">
            <div class="space-y-1.5 text-center md:text-left">
                <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                    Gratis Ongkir Rp0 & Extra Cashback s.d. 100RB
                </h3>
                <p class="text-xs sm:text-sm text-blue-100 max-w-xl">
                    Klaim voucher promo khusus hari ini dan nikmati pengiriman ekspres tanpa batas minimal belanja.
                </p>
            </div>
            <a href="{{ route('promo') }}" class="bg-white hover:bg-blue-50 text-blue-800 font-extrabold text-xs sm:text-sm px-6 py-3 rounded-lg transition-colors shrink-0 shadow-2xs">
                Klaim Voucher Sekarang &gt;
            </a>
        </div>
    </section>

    <!-- RECOMMENDATIONS SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-5 bg-blue-700 rounded-full"></div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                    Rekomendasi Untukmu
                </h2>
            </div>
            <a href="{{ route('catalog') }}" class="text-xs font-bold text-blue-700 hover:text-blue-800 flex items-center gap-1 transition-colors">
                Lihat Semua
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- 5-Column Grid of Products -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($recommendedProducts as $product)
                <x-product-card 
                    :id="$product['id']"
                    :title="$product['title']"
                    :price="$product['price']"
                    :originalPrice="$product['originalPrice']"
                    :discount="$product['discount']"
                    :image="$product['image']"
                    :rating="$product['rating']"
                    :sold="$product['sold']"
                    :variants="$product['variants'] ?? []"
                />
            @endforeach
        </div>
    </section>

</div>

<!-- Countdown & Hero Slider JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Countdown Timer ---
        let totalSeconds = 5 * 3600 + 42 * 60 + 19;

        const hoursEl = document.getElementById('hours');
        const minutesEl = document.getElementById('minutes');
        const secondsEl = document.getElementById('seconds');

        function updateTimer() {
            if (totalSeconds <= 0) {
                totalSeconds = 24 * 3600;
            }
            
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
            if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');

            totalSeconds--;
        }

        setInterval(updateTimer, 1000);

        // --- 2. Hero Auto-Playing Slider ---
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.slide-dot');
        const prevBtn = document.getElementById('prev-slide');
        const nextBtn = document.getElementById('next-slide');
        const sliderContainer = document.getElementById('hero-slider');
        
        let currentSlide = 0;
        let slideInterval;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.remove('hidden', 'opacity-0');
                    slide.classList.add('opacity-100');
                } else {
                    slide.classList.add('hidden', 'opacity-0');
                    slide.classList.remove('opacity-100');
                }
            });

            dots.forEach((dot, i) => {
                if (i === index) {
                    dot.classList.remove('w-1.5', 'bg-white/40');
                    dot.classList.add('w-6', 'bg-white');
                } else {
                    dot.classList.remove('w-6', 'bg-white');
                    dot.classList.add('w-1.5', 'bg-white/40');
                }
            });

            currentSlide = index;
        }

        function nextSlide() {
            let nextIndex = (currentSlide + 1) % slides.length;
            showSlide(nextIndex);
        }

        function prevSlide() {
            let prevIndex = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(prevIndex);
        }

        function startAutoPlay() {
            slideInterval = setInterval(nextSlide, 4500);
        }

        function stopAutoPlay() {
            clearInterval(slideInterval);
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                stopAutoPlay();
                nextSlide();
                startAutoPlay();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                stopAutoPlay();
                prevSlide();
                startAutoPlay();
            });
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                stopAutoPlay();
                showSlide(index);
                startAutoPlay();
            });
        });

        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopAutoPlay);
            sliderContainer.addEventListener('mouseleave', startAutoPlay);
        }

        startAutoPlay();
    });
</script>
@endsection
