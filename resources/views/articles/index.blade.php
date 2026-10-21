@extends('layouts.app')

@section('title', 'Kabar & Promo Terbaru - TokoOnline')

@section('content')
<div class="bg-slate-50 min-h-screen py-6 sm:py-8 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Breadcrumb & Header Section -->
        <div class="space-y-4">
            <!-- Breadcrumb -->
            <nav class="text-xs text-slate-500 flex items-center gap-1.5">
                <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
                <span>&gt;</span>
                <span class="text-slate-900 font-medium">Kabar & Promo</span>
            </nav>

            <!-- Page Title Bar -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-6 border-b border-slate-200">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 border border-blue-200/80 text-blue-700 text-xs font-semibold mb-2 shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        <span>Pusat Informasi & Promo Toko</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Kabar Terkini & Promo Eksklusif
                    </h1>
                    <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                        Temukan update penawaran spesial, diskon belanja hemat, dan inspirasi produk resmi dari TokoOnline.
                    </p>
                </div>

                <!-- Search Form -->
                <form action="{{ route('articles.index') }}" method="GET" class="w-full md:w-80">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ request('q') }}"
                            placeholder="Cari promo atau artikel..." 
                            class="w-full bg-white text-xs sm:text-sm rounded-xl pl-9 pr-20 py-2.5 border border-slate-200 focus:border-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-700 transition-all shadow-2xs"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        @if(request('tag'))
                            <input type="hidden" name="tag" value="{{ request('tag') }}">
                        @endif
                        @if(request('q'))
                            <a href="{{ route('articles.index', ['tag' => request('tag')]) }}" class="absolute inset-y-0 right-14 pr-1 flex items-center text-xs text-slate-400 hover:text-slate-600">
                                Batal
                            </a>
                        @endif
                        <button 
                            type="submit" 
                            class="absolute inset-y-1 right-1 px-3 bg-blue-700 hover:bg-blue-800 text-white font-semibold text-xs rounded-lg transition-colors cursor-pointer"
                        >
                            Cari
                        </button>
                    </div>
                </form>
            </div>

            <!-- Category Filter Chips -->
            @php
                $activeTag = request('tag', 'semua');
                $chips = [
                    ['label' => 'Semua', 'val' => 'semua', 'icon' => 'sparkles'],
                    ['label' => 'Promo & Diskon', 'val' => 'Promo', 'icon' => 'tag'],
                    ['label' => 'Flash Sale', 'val' => 'Flash Sale', 'icon' => 'bolt'],
                    ['label' => 'Gajian & Payday', 'val' => 'Payday', 'icon' => 'calendar'],
                    ['label' => 'Fashion & Cuci Gudang', 'val' => 'Cuci Gudang', 'icon' => 'shopping-bag'],
                ];
            @endphp
            <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-1 hide-scrollbar">
                @foreach($chips as $c)
                    @php
                        $isActive = ($activeTag === $c['val']) || ($c['val'] === 'semua' && (!$activeTag || $activeTag === 'semua'));
                    @endphp
                    <a 
                        href="{{ route('articles.index', array_merge(request()->except(['page']), ['tag' => $c['val'] === 'semua' ? null : $c['val']])) }}"
                        class="shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shadow-2xs {{ $isActive ? 'bg-blue-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200' }}"
                    >
                        {{ $c['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        @php
            // Helper for image url resolution
            $resolveImage = function($path) {
                if (!$path) return null;
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }
                return asset(ltrim($path, '/'));
            };

            // Helper for category badge text
            $getBadge = function($title) {
                if (stripos($title, 'Flash Sale') !== false) return ['text' => 'Flash Sale', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'];
                if (stripos($title, 'Payday') !== false) return ['text' => 'Promo Payday', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'];
                if (stripos($title, 'Diskon') !== false || stripos($title, 'Promo') !== false) return ['text' => 'Diskon Spesial', 'bg' => 'bg-rose-50 text-rose-700 border-rose-200'];
                if (stripos($title, 'Cuci Gudang') !== false) return ['text' => 'Cuci Gudang', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200'];
                return ['text' => 'Kabar Toko', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'];
            };
        @endphp

        <!-- Hero / Featured Article Section (Only on page 1 without search filter) -->
        @if(!request('q') && !request('tag') && ($articles->currentPage() == 1) && ($featured = $articles->first()))
            @php
                $featuredThumb = $resolveImage($featured->thumbnail);
                $fBadge = $getBadge($featured->title);
            @endphp
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200/90 shadow-2xs hover:shadow-md transition-all duration-300 group">
                <div class="grid grid-cols-1 lg:grid-cols-12 items-stretch">
                    <!-- Image Side (7 Cols on desktop) -->
                    <div class="lg:col-span-7 relative min-h-[260px] sm:min-h-[340px] lg:min-h-[400px] bg-slate-100 overflow-hidden">
                        @if($featuredThumb)
                            <img 
                                src="{{ $featuredThumb }}" 
                                alt="{{ $featured->title }}" 
                                class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                            >
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-700 to-indigo-900 flex items-center justify-center text-white/50">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                        
                        <!-- Top Floating Badge -->
                        <div class="absolute top-4 left-4 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-400 text-amber-950 shadow-sm">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span>Promo Pilihan</span>
                            </span>
                        </div>
                    </div>

                    <!-- Content Side (5 Cols on desktop) -->
                    <div class="lg:col-span-5 p-6 sm:p-8 lg:p-10 flex flex-col justify-between bg-white">
                        <div>
                            <!-- Category & Read time -->
                            <div class="flex items-center gap-2 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold border uppercase tracking-wider {{ $fBadge['bg'] }}">
                                    {{ $fBadge['text'] }}
                                </span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-xs text-slate-500 font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    3 min baca
                                </span>
                            </div>

                            <!-- Title: Clean slate-900 with subtle hover blue -->
                            <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 leading-snug group-hover:text-blue-700 transition-colors line-clamp-3">
                                <a href="{{ route('articles.detail', $featured->slug) }}">
                                    {{ $featured->title }}
                                </a>
                            </h2>

                            <!-- Excerpt -->
                            <p class="text-slate-500 text-sm sm:text-base leading-relaxed mt-3.5 line-clamp-3">
                                {{ $featured->summary ?: Str::limit(strip_tags($featured->content), 150) }}
                            </p>
                        </div>

                        <!-- Footer Info -->
                        <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    TO
                                </div>
                                <div class="text-xs leading-tight">
                                    <p class="font-bold text-slate-800">Admin Toko</p>
                                    <p class="text-slate-400 mt-0.5">{{ $featured->created_at->format('d M Y') }}</p>
                                </div>
                            </div>

                            <a 
                                href="{{ route('articles.detail', $featured->slug) }}" 
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-2xs hover:shadow transition-all shrink-0"
                            >
                                <span>Baca Promo</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Recent Articles Grid Section -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <div class="w-1.5 h-5 bg-blue-700 rounded-full"></div>
                    <span>
                        @if(request('q'))
                            Hasil Pencarian: "{{ request('q') }}"
                        @elseif(request('tag') && request('tag') !== 'semua')
                            Kategori: {{ request('tag') }}
                        @else
                            Katalog Berita & Promo
                        @endif
                    </span>
                </h2>
                
                <span class="text-xs text-slate-500 font-medium">
                    Total {{ $articles->total() }} Artikel
                </span>
            </div>

            @php
                // Check if we should skip the first item (because it's shown in hero)
                $skipFirst = (!request('q') && !request('tag') && $articles->currentPage() == 1);
            @endphp

            @if($articles->count() > ($skipFirst ? 1 : 0))
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
                    @foreach($articles as $index => $article)
                        @if($skipFirst && $index === 0) 
                            @continue 
                        @endif

                        @php
                            $thumb = $resolveImage($article->thumbnail);
                            $badge = $getBadge($article->title);
                        @endphp

                        <article class="bg-white rounded-2xl overflow-hidden border border-slate-200/80 shadow-2xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group flex flex-col">
                            <!-- Thumbnail -->
                            <a href="{{ route('articles.detail', $article->slug) }}" class="block aspect-[16/10] overflow-hidden relative bg-slate-100">
                                @if($thumb)
                                    <img 
                                        src="{{ $thumb }}" 
                                        alt="{{ $article->title }}" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif

                                <!-- Category Badge overlay -->
                                <div class="absolute top-3 left-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold border backdrop-blur-xs shadow-2xs {{ $badge['bg'] }} bg-white/95">
                                        {{ $badge['text'] }}
                                    </span>
                                </div>
                            </a>

                            <!-- Card Body -->
                            <div class="p-5 sm:p-6 flex flex-col flex-1">
                                <!-- Meta Row -->
                                <div class="flex items-center gap-2 text-xs text-slate-400 mb-2.5">
                                    <span class="flex items-center gap-1 font-medium text-slate-500">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $article->created_at->format('d M Y') }}
                                    </span>
                                    <span>&bull;</span>
                                    <span>2 min baca</span>
                                </div>

                                <!-- Title -->
                                <h3 class="text-base font-bold text-slate-900 leading-snug group-hover:text-blue-700 transition-colors line-clamp-2 mb-2">
                                    <a href="{{ route('articles.detail', $article->slug) }}">
                                        {{ $article->title }}
                                    </a>
                                </h3>

                                <!-- Summary Excerpt -->
                                <p class="text-xs sm:text-sm text-slate-500 line-clamp-2 leading-relaxed mb-4">
                                    {{ $article->summary ?: Str::limit(strip_tags($article->content), 95) }}
                                </p>

                                <!-- Card Footer -->
                                <div class="mt-auto pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-400 font-medium">Oleh Tim Toko</span>
                                    <a href="{{ route('articles.detail', $article->slug) }}" class="inline-flex items-center gap-1 font-bold text-blue-700 group-hover:gap-1.5 transition-all">
                                        <span>Baca Promo</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($articles->hasPages())
                    <div class="mt-10 flex justify-center">
                        {{ $articles->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-2xs max-w-lg mx-auto">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-1">Belum Ada Artikel Promo</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mb-6">
                        @if(request('q') || request('tag'))
                            Tidak ada artikel yang cocok dengan pencarian Anda. Coba kata kunci lain.
                        @else
                            Informasi promo dan diskon baru akan segera kami publikasikan di sini.
                        @endif
                    </p>
                    @if(request('q') || request('tag'))
                        <a 
                            href="{{ route('articles.index') }}" 
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors"
                        >
                            Reset Filter
                        </a>
                    @else
                        <a 
                            href="{{ route('catalog') }}" 
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold rounded-xl transition-colors"
                        >
                            Belanja di Katalog
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Commercial E-Commerce Callout Banner -->
        <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-3xl p-7 sm:p-10 text-white shadow-md relative overflow-hidden">
            <!-- Decorative Glow Elements -->
            <div class="absolute -right-8 -bottom-8 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -left-8 -top-8 w-48 h-48 bg-blue-400/20 rounded-full blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-white text-xs font-semibold mb-2.5 backdrop-blur-xs">
                        <span>🛍️ Penawaran Spesial Hari Ini</span>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Siap Nikmati Promo & Diskon Belanja?
                    </h3>
                    <p class="text-blue-100 text-xs sm:text-sm mt-1.5 max-w-xl leading-relaxed">
                        Jelajahi ribuan produk terbaik dengan potongan harga eksklusif, jaminan 100% original, dan promo gratis ongkir.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                    <a 
                        href="{{ route('promo') }}" 
                        class="w-full sm:w-auto px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-amber-950 font-bold text-xs sm:text-sm rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4 text-amber-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                        <span>Lihat Semua Promo</span>
                    </a>
                    <a 
                        href="{{ route('catalog') }}" 
                        class="w-full sm:w-auto px-5 py-2.5 bg-white/15 hover:bg-white/25 text-white font-semibold text-xs sm:text-sm rounded-xl border border-white/20 backdrop-blur-xs transition-colors text-center"
                    >
                        Buka Katalog Produk
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection
