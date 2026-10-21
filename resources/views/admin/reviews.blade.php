@extends('admin.layout')

@section('title', 'Ulasan Produk')

@section('content')
<div class="space-y-6" x-data="{ previewImage: null }">

    <!-- Header Section (Konsisten dengan Dashboard, Pesanan & Pelanggan) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Ulasan Produk</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pantau kepuasan pembeli, statistik penilaian produk, dan riwayat ulasan transaksi toko.</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <div class="text-xs font-medium text-slate-700 bg-white border border-slate-200 px-3.5 py-2 rounded-lg shadow-xs flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Total: <strong class="text-slate-900 font-semibold">{{ number_format($stats['total_reviews']) }}</strong> Ulasan Masuk</span>
            </div>
        </div>
    </div>

    <!-- Alert Sukses -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between gap-3 text-xs sm:text-sm animate-in fade-in duration-200">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs p-1 cursor-pointer">✕</button>
        </div>
    @endif

    <!-- 4 Metric Cards: Ringkasan Rating & Ulasan (Persis Desain Dashboard & Pesanan) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Total Ulasan -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Total Ulasan</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($stats['total_reviews']) }}</div>
            <p class="text-xs text-blue-600 font-medium mt-0.5">Ulasan riil dari pembeli</p>
        </div>

        <!-- 2. Rata-Rata Rating Toko -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Rata-Rata Rating Toko</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                    <svg class="w-4 h-4 fill-amber-500" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5 mt-2">
                <div class="text-xl sm:text-2xl font-semibold text-slate-900">
                    {{ $stats['total_reviews'] > 0 ? number_format($stats['average_rating'], 1) : '-' }}
                </div>
                <span class="text-xs text-amber-500 font-semibold">★ / 5.0</span>
            </div>
            <p class="text-xs text-amber-600 font-medium mt-0.5">
                {{ $stats['total_reviews'] > 0 ? 'Indeks kepuasan pelanggan' : 'Belum ada penilaian' }}
            </p>
        </div>

        <!-- 3. Ulasan Positif (Bintang 4-5) -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Ulasan Puas (Bintang 4-5)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h47M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">
                {{ number_format(($stats['five_star'] ?? 0) + ($stats['four_star'] ?? 0)) }}
            </div>
            <p class="text-xs text-emerald-600 font-medium mt-0.5">Rating bintang 4 dan 5</p>
        </div>

        <!-- 4. Perlu Diperhatikan (Bintang 1-2) -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Perlu Diperhatikan (1-2★)</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">
                {{ number_format($stats['low_rating'] ?? 0) }}
            </div>
            <p class="text-xs text-rose-600 font-medium mt-0.5">Evaluasi & tingkatkan layanan</p>
        </div>
    </div>

    <!-- Panduan Operasional Ulasan Produk untuk Admin Toko -->
    <div class="p-3.5 bg-blue-50/70 border border-blue-200/80 rounded-xl text-xs text-blue-900 space-y-1.5">
        <div class="font-semibold flex items-center gap-1.5 text-blue-900">
            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Panduan Manajemen Penilaian & Ulasan untuk Admin Toko:</span>
        </div>
        <p class="text-slate-600 leading-relaxed text-[11px]">
            1. <strong>Asal Ulasan:</strong> Ulasan produk dikirimkan secara langsung oleh pembeli setelah pesanan berstatus <em>Selesai</em> melalui akun pembeli.<br>
            2. <strong>Penanganan Keluhan (1-2★):</strong> Gunakan tab <strong>"1-2 Bintang (Keluhan)"</strong> untuk memprioritaskan masukan pembeli yang kurang puas agar tim Anda dapat menghubungi pembeli via WhatsApp untuk memberikan solusi.<br>
            3. <strong>Privasi & Keamanan:</strong> Demi privasi, nama pembeli dapat disamarkan otomatis (misal: <em>N***o</em>). Jika terdapat ulasan yang mengandung spam atau kata tidak pantas, admin dapat menekan tombol <strong>"Hapus"</strong>.
        </p>
    </div>

    <!-- Main Container: Filter, Search & Reviews Table (Persis Halaman Pesanan & Pelanggan) -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">

        <!-- Rating Tabs Bar (Shopee / Tokopedia Seller Centre Pattern) -->
        <div class="border-b border-slate-200 px-4 sm:px-6 bg-white overflow-x-auto no-scrollbar">
            <nav class="flex items-center gap-6 sm:gap-7 -mb-px text-xs sm:text-sm font-medium">
                @php
                    $currentRating = request('rating', 'all');
                    $ratingTabs = [
                        'all'        => ['label' => 'Semua Rating', 'count' => $stats['total_reviews']],
                        '5'          => ['label' => '5 Bintang (Sangat Puas)', 'count' => $stats['five_star']],
                        '4'          => ['label' => '4 Bintang (Puas)', 'count' => $stats['four_star']],
                        '3'          => ['label' => '3 Bintang (Cukup)', 'count' => $stats['three_star']],
                        'low'        => ['label' => '1-2 Bintang (Keluhan)', 'count' => $stats['low_rating'], 'alert' => true],
                        'with_media' => ['label' => 'Dengan Foto', 'count' => $stats['with_media']],
                    ];
                @endphp

                @foreach($ratingTabs as $tabKey => $tabData)
                    @php
                        $isActive = ($currentRating === $tabKey);
                        $queryParams = request()->query();
                        $queryParams['rating'] = $tabKey;
                        $queryParams['page'] = 1;
                        $tabUrl = route('admin.reviews', $queryParams);
                    @endphp
                    <a 
                        href="{{ $tabUrl }}" 
                        class="py-3.5 border-b-2 whitespace-nowrap transition-colors flex items-center gap-1.5 {{ $isActive ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}"
                    >
                        <span>{{ $tabData['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $isActive ? 'bg-blue-100 text-blue-700 font-semibold' : 'bg-slate-100 text-slate-600' }}">
                            {{ $tabData['count'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Toolbar Pencarian & Filter Kategori Dinamis -->
        <div class="p-4 sm:p-5 bg-slate-50/50 border-b border-slate-200 space-y-3">
            <form action="{{ route('admin.reviews') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                @if(request('rating') && request('rating') !== 'all')
                    <input type="hidden" name="rating" value="{{ request('rating') }}">
                @endif
                @if(request('category') && request('category') !== 'all')
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 max-w-xl">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Cari komentar, nama pembeli, produk, atau invoice..." 
                            class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-8 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        @if(request('search'))
                            <a href="{{ route('admin.reviews', ['rating' => request('rating'), 'category' => request('category')]) }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold" title="Reset pencarian">✕</a>
                        @endif
                    </div>

                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg transition-colors cursor-pointer shadow-xs shrink-0"
                    >
                        Cari
                    </button>

                    @if(request('search') || (request('category') && request('category') !== 'all') || (request('rating') && request('rating') !== 'all'))
                        <a 
                            href="{{ route('admin.reviews') }}" 
                            class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium text-xs rounded-lg transition-colors text-center shrink-0"
                            title="Reset filter"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Filter Kategori Produk Dinamis -->
            @if(count($categories ?? []) > 0)
                <div class="pt-2.5 border-t border-slate-200/80 flex items-center gap-1.5 overflow-x-auto text-xs no-scrollbar">
                    <span class="text-slate-400 text-[11px] font-medium uppercase tracking-wider shrink-0 mr-1">Kategori:</span>
                    <a 
                        href="{{ route('admin.reviews', array_merge(request()->query(), ['category' => 'all', 'page' => 1])) }}" 
                        class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors shrink-0 {{ (!request('category') || request('category') === 'all') ? 'bg-blue-600 text-white font-semibold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}"
                    >
                        Semua
                    </a>
                    @foreach($categories as $cat)
                        <a 
                            href="{{ route('admin.reviews', array_merge(request()->query(), ['category' => $cat, 'page' => 1])) }}" 
                            class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors shrink-0 {{ request('category') === $cat ? 'bg-blue-600 text-white font-semibold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}"
                        >
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tabel Ulasan Produk (Shopee / Tokopedia Seller Centre Standard) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Pembeli</th>
                        <th class="px-4 py-3">Produk & Varian</th>
                        <th class="px-3 py-3 text-center">Rating</th>
                        <th class="px-4 py-3">Komentar & Testimoni</th>
                        <th class="px-3 py-3 whitespace-nowrap">Waktu Ulasan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800 font-normal">
                    @forelse($reviews as $r)
                        @php
                            $userInitial = strtoupper(substr($r->display_name ?? 'P', 0, 1));
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Pembeli -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-semibold flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                        {{ $userInitial }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="font-medium text-slate-900 truncate text-xs">{{ $r->display_name }}</h4>
                                            @if($r->is_anonymous)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                                    Anonim
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-slate-400 font-mono">Invoice: #{{ $r->order_id ?: '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Produk Yang Diulas -->
                            <td class="px-4 py-3.5">
                                @if($r->product)
                                    <div class="flex items-center gap-2.5 max-w-xs">
                                        <img src="{{ $r->product->image }}" alt="{{ $r->product->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                                        <div class="min-w-0">
                                            <h5 class="font-medium text-slate-900 truncate text-xs" title="{{ $r->product->name }}">{{ $r->product->name }}</h5>
                                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                <span class="bg-slate-100 text-slate-600 text-[10px] font-medium px-1.5 py-0.2 rounded border border-slate-200">
                                                    {{ $r->product->category }}
                                                </span>
                                                @if($r->variant)
                                                    <span class="text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-200 px-1.5 py-0.2 rounded font-medium truncate max-w-[120px]">
                                                        {{ $r->variant }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">Produk ID #{{ $r->product_id }}</span>
                                @endif
                            </td>

                            <!-- Rating Bintang -->
                            <td class="px-3 py-3.5 whitespace-nowrap text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="flex text-amber-400 text-xs">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span>{{ $i <= $r->rating ? '★' : '☆' }}</span>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-800 mt-0.5">{{ number_format($r->rating, 1) }}</span>
                                </div>
                            </td>

                            <!-- Komentar & Foto -->
                            <td class="px-4 py-3.5 max-w-md">
                                <p class="text-xs text-slate-700 leading-relaxed font-normal bg-slate-50 p-2.5 rounded-lg border border-slate-200/80">
                                    "{{ $r->comment ?: 'Tidak ada ulasan tertulis.' }}"
                                </p>
                                @if($r->image)
                                    <div class="mt-2 flex items-center gap-2">
                                        <button 
                                            type="button" 
                                            @click="previewImage = '{{ str_starts_with($r->image, 'http') ? $r->image : asset($r->image) }}'"
                                            class="inline-block border border-slate-200 rounded-lg overflow-hidden w-12 h-12 shrink-0 hover:opacity-80 transition-opacity cursor-pointer shadow-2xs"
                                            title="Klik untuk memperbesar foto"
                                        >
                                            <img src="{{ str_starts_with($r->image, 'http') ? $r->image : asset($r->image) }}" class="w-full h-full object-cover" alt="Foto Ulasan">
                                        </button>
                                        <span class="text-[10px] text-slate-400">1 Foto ulasan pembeli</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Waktu Ulasan -->
                            <td class="px-3 py-3.5 whitespace-nowrap text-xs">
                                <div class="font-medium text-slate-800">
                                    {{ $r->created_at ? $r->created_at->format('d M Y') : '-' }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $r->created_at ? $r->created_at->diffForHumans() : '-' }}
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <form action="{{ route('admin.reviews.delete', $r->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-rose-200 rounded-lg transition-colors cursor-pointer"
                                        title="Hapus Ulasan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="max-w-md mx-auto space-y-3">
                                    <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center text-amber-500 mx-auto border border-amber-200 shadow-2xs">
                                        <svg class="w-6 h-6 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-800 text-sm">Belum Ada Ulasan Produk</h4>
                                        <p class="text-xs text-slate-500 leading-relaxed mt-1">
                                            @if(request('search') || (request('rating') && request('rating') !== 'all') || (request('category') && request('category') !== 'all'))
                                                Tidak ditemukan ulasan yang cocok dengan kata kunci atau filter yang Anda pilih.
                                            @else
                                                Saat ini belum ada ulasan baru dari pembeli. Toko siap menerima pesanan riil pertama Anda!
                                            @endif
                                        </p>
                                    </div>

                                    @if(!request('search') && (!request('rating') || request('rating') === 'all') && (!request('category') || request('category') === 'all'))
                                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-left space-y-1.5 text-[11px] text-slate-600">
                                            <span class="font-semibold text-slate-800 block">Cara Kerja Ulasan Otomatis:</span>
                                            <div class="flex items-center gap-1.5 text-slate-500 flex-wrap">
                                                <span class="bg-white border border-slate-200 px-2 py-0.5 rounded font-medium text-slate-700">1. Pesanan Selesai</span>
                                                <span>&rarr;</span>
                                                <span class="bg-white border border-slate-200 px-2 py-0.5 rounded font-medium text-slate-700">2. Pembeli Beri Rating</span>
                                                <span>&rarr;</span>
                                                <span class="bg-white border border-slate-200 px-2 py-0.5 rounded font-medium text-slate-700">3. Muncul Otomatis di Sini</span>
                                            </div>
                                        </div>
                                    @endif

                                    @if(request('search') || (request('rating') && request('rating') !== 'all') || (request('category') && request('category') !== 'all'))
                                        <div class="pt-1">
                                            <a href="{{ route('admin.reviews') }}" class="px-3.5 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors inline-block shadow-xs">
                                                Lihat Semua Ulasan
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($reviews->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    Menampilkan <strong>{{ $reviews->firstItem() ?? 0 }}</strong> sampai <strong>{{ $reviews->lastItem() ?? 0 }}</strong> dari <strong>{{ $reviews->total() }}</strong> ulasan.
                </div>
                <div>
                    {{ $reviews->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Lightbox Preview Foto Ulasan -->
    <div 
        x-show="previewImage" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs"
        @keydown.escape.window="previewImage = null"
    >
        <div class="relative max-w-xl max-h-[90vh] bg-white rounded-xl p-2 shadow-2xl" @click.outside="previewImage = null">
            <button 
                type="button" 
                @click="previewImage = null"
                class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center text-xs hover:bg-slate-700 shadow-md cursor-pointer"
            >
                ✕
            </button>
            <img :src="previewImage" alt="Preview Foto Ulasan" class="max-h-[80vh] w-auto rounded-lg object-contain mx-auto">
        </div>
    </div>

</div>
@endsection
