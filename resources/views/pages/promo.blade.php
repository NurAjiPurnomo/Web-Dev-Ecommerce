@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8">

    <!-- Breadcrumb -->
    <nav class="text-xs text-gray-500 flex items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
        <span>&gt;</span>
        <span class="text-gray-900 font-medium">Pusat Promo & Voucher</span>
    </nav>

    <!-- Hero Campaign Banner (Dynamic from Admin Panel /admin/banners) -->
    <div class="relative bg-blue-700 rounded-2xl overflow-hidden shadow-xs p-6 sm:p-10 text-white flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="max-w-xl space-y-3 relative z-10">
            <span class="text-blue-200 text-xs font-extrabold uppercase tracking-wider block">
                {{ $heroBanner->subtitle ?? 'PUSAT PROMO & VOUCHER TOKO' }}
            </span>
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
                {{ $heroBanner->title ?? 'Pesta Promo Gajian & Gratis Ongkir Rp0!' }}
                @if(isset($heroBanner) && $heroBanner->highlight_text)
                    <span class="text-yellow-300 block mt-1">{{ $heroBanner->highlight_text }}</span>
                @endif
            </h1>
            <p class="text-xs sm:text-sm text-blue-100 leading-relaxed">
                {{ $heroBanner->description ?? 'Klaim kode voucher spesial gajian sekarang dan nikmati diskon hingga 70% untuk semua kategori produk impianmu.' }}
            </p>
        </div>
        @if(isset($heroBanner) && $heroBanner->image)
            <div class="w-48 h-36 rounded-xl overflow-hidden shadow-md border border-white/20 shrink-0 hidden md:block">
                <img src="{{ $heroBanner->image }}" alt="{{ $heroBanner->title }}" class="w-full h-full object-cover">
            </div>
        @endif
    </div>

    <!-- PUSAT VOUCHER INTERAKTIF -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <div class="w-1.5 h-5 bg-blue-700 rounded-full"></div>
                    Pusat Voucher Toko
                </h2>
                <p class="text-xs text-slate-500">Klik klaim voucher untuk langsung digunakan saat checkout keranjang</p>
            </div>
        </div>

        <!-- Voucher Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
            @forelse($vouchers as $v)
                <div class="bg-white border border-blue-200 rounded-xl p-4 shadow-xs relative flex flex-col justify-between space-y-3">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="bg-blue-100 text-blue-700 text-[10px] font-extrabold px-2 py-0.5 rounded">
                                {{ $v['badge'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">
                                {{ $v['expiry'] }}
                            </span>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            {{ $v['title'] }}
                        </h3>
                        <p class="text-xs text-slate-600">
                            {{ $v['desc'] }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <div class="bg-slate-100 px-2.5 py-1 rounded border border-dashed border-slate-300 font-mono text-xs font-bold text-slate-700">
                            {{ $v['code'] }}
                        </div>
                        @if($v['id'])
                            @if($v['is_claimed'])
                                <button type="button" disabled class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all shadow-2xs bg-emerald-600 text-white cursor-not-allowed">
                                    Sudah Diklaim
                                </button>
                            @else
                                <form action="{{ route('vouchers.claim', $v['id']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all shadow-2xs bg-blue-700 hover:bg-blue-800 text-white cursor-pointer">
                                        Klaim Voucher
                                    </button>
                                </form>
                            @endif
                        @else
                            <button type="button" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all shadow-2xs bg-slate-300 text-slate-500 cursor-not-allowed">
                                Tidak Tersedia
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-slate-50 border border-slate-200 rounded-xl p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Belum Ada Promo Voucher</h3>
                    <p class="mt-1 text-xs text-slate-500">Nantikan kejutan voucher diskon dan gratis ongkir dari kami selanjutnya.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- KATALOG PRODUK SUPER PROMO (ENTERPRISE GRADE) -->
    <div class="space-y-4 pt-4">
        <!-- Section Header -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 space-y-4 shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        Katalog Penawaran Terhemat Hari Ini
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Koleksi produk original pilihan dengan potongan harga terbesar hingga 70%
                    </p>
                </div>
                <!-- Filter Category Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs font-semibold">
                    <a 
                        href="{{ route('promo', ['category' => 'all']) }}" 
                        class="{{ $selectedCategory === 'all' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap"
                    >
                        Semua Promo
                    </a>
                    <a 
                        href="{{ route('promo', ['category' => 'pakaian']) }}" 
                        class="{{ strcasecmp($selectedCategory, 'Pakaian') === 0 ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap"
                    >
                        Fashion
                    </a>
                    <a 
                        href="{{ route('promo', ['category' => 'gadget']) }}" 
                        class="{{ strcasecmp($selectedCategory, 'Gadget') === 0 ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap"
                    >
                        Gadget
                    </a>
                    <a 
                        href="{{ route('promo', ['category' => 'rumah tangga']) }}" 
                        class="{{ strcasecmp($selectedCategory, 'Rumah Tangga') === 0 ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap"
                    >
                        Rumah Tangga
                    </a>
                </div>
            </div>

            <!-- Product Grid (6 Columns) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                @forelse($promoProducts as $product)
                    <div>
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
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Belum ada promo</h3>
                        <p class="text-slate-500 text-sm mt-1">Nantikan penawaran menarik dari kami segera.</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            @if($promoProducts->hasPages())
                <div class="pt-6">
                    {{ $promoProducts->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>

    <!-- CARA PENGGUNAAN VOUCHER (3-STEP GUIDE) -->
    <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-5 sm:p-6 space-y-4">
        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Cara Menggunakan Voucher Diskon
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-700">
            <div class="bg-white border border-blue-100 rounded-lg p-3.5 flex items-start gap-3 shadow-2xs">
                <span class="w-6 h-6 rounded-full bg-blue-700 text-white font-bold flex items-center justify-center shrink-0 text-xs">1</span>
                <div>
                    <strong class="font-bold text-slate-900 block mb-0.5">Pilih & Klaim Voucher</strong>
                    <p class="text-slate-500 leading-relaxed">Klik tombol "Klaim Voucher" pada voucher promo yang ingin Anda gunakan.</p>
                </div>
            </div>

            <div class="bg-white border border-blue-100 rounded-lg p-3.5 flex items-start gap-3 shadow-2xs">
                <span class="w-6 h-6 rounded-full bg-blue-700 text-white font-bold flex items-center justify-center shrink-0 text-xs">2</span>
                <div>
                    <strong class="font-bold text-slate-900 block mb-0.5">Pilih Produk Impian</strong>
                    <p class="text-slate-500 leading-relaxed">Masukkan produk sesuai syarat promo ke keranjang belanja Anda.</p>
                </div>
            </div>

            <div class="bg-white border border-blue-100 rounded-lg p-3.5 flex items-start gap-3 shadow-2xs">
                <span class="w-6 h-6 rounded-full bg-blue-700 text-white font-bold flex items-center justify-center shrink-0 text-xs">3</span>
                <div>
                    <strong class="font-bold text-slate-900 block mb-0.5">Otomatis Terpotong</strong>
                    <p class="text-slate-500 leading-relaxed">Gunakan atau tempelkan kode voucher di halaman Keranjang saat checkout.</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
