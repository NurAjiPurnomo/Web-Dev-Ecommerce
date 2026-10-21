@extends('layouts.app')

@section('title', $article->title . ' - TokoOnline')

@section('content')
<div class="bg-slate-50 min-h-screen py-6 sm:py-8 font-sans" x-data="{ copied: false }">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Breadcrumb -->
        <nav class="text-xs text-slate-500 flex items-center gap-1.5">
            <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
            <span>&gt;</span>
            <a href="{{ route('articles.index') }}" class="hover:text-blue-700 transition-colors">Kabar & Promo</a>
            <span>&gt;</span>
            <span class="text-slate-900 font-medium truncate max-w-[200px] sm:max-w-sm">{{ $article->title }}</span>
        </nav>

        @php
            $resolveImage = function($path) {
                if (!$path) return null;
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }
                return asset(ltrim($path, '/'));
            };

            $getBadge = function($title) {
                if (stripos($title, 'Flash Sale') !== false) return ['text' => 'Flash Sale', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'];
                if (stripos($title, 'Payday') !== false) return ['text' => 'Promo Payday', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'];
                if (stripos($title, 'Diskon') !== false || stripos($title, 'Promo') !== false) return ['text' => 'Diskon Spesial', 'bg' => 'bg-rose-50 text-rose-700 border-rose-200'];
                if (stripos($title, 'Cuci Gudang') !== false) return ['text' => 'Cuci Gudang', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200'];
                return ['text' => 'Info Promo Toko', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'];
            };

            $mainThumb = $resolveImage($article->thumbnail);
            $mainBadge = $getBadge($article->title);
        @endphp

        <!-- Main Article Container -->
        <article class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-6 sm:p-10 md:p-12 space-y-8">
            
            <!-- Article Header -->
            <div class="space-y-4 border-b border-slate-100 pb-6">
                <!-- Badge & Read Time -->
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border uppercase tracking-wider {{ $mainBadge['bg'] }}">
                        {{ $mainBadge['text'] }}
                    </span>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-xs text-slate-500 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        3 min baca
                    </span>
                </div>

                <!-- Main Title -->
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight tracking-tight">
                    {{ $article->title }}
                </h1>

                <!-- Author & Date Bar -->
                <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shadow-2xs">
                            TO
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-bold text-slate-800">Admin Toko</span>
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    Official
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Diterbitkan pada {{ $article->created_at->format('d F Y') }}
                            </p>
                        </div>
                    </div>

                    <!-- Quick Share Trigger -->
                    <div class="flex items-center gap-2">
                        <button 
                            @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 2500)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="copied ? 'Tersalin! ✓' : 'Salin Link'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Featured Image -->
            @if($mainThumb)
                <div class="rounded-2xl overflow-hidden border border-slate-200/80 shadow-2xs aspect-[16/9] sm:aspect-[21/9] bg-slate-100 relative">
                    <img 
                        src="{{ $mainThumb }}" 
                        alt="{{ $article->title }}" 
                        class="w-full h-full object-cover"
                    >
                </div>
            @endif

            <!-- Summary / Lead Box -->
            @if($article->summary)
                <div class="bg-blue-50/70 border-l-4 border-blue-600 rounded-r-2xl p-4 sm:p-5 text-slate-700 text-sm sm:text-base font-medium leading-relaxed italic">
                    "{{ $article->summary }}"
                </div>
            @endif

            <!-- Article Body Content -->
            <div class="prose prose-slate max-w-none 
                prose-p:text-slate-600 prose-p:leading-relaxed prose-p:text-sm sm:prose-p:text-base
                prose-headings:font-extrabold prose-headings:text-slate-900
                prose-h2:text-xl sm:prose-h2:text-2xl prose-h2:mt-8 prose-h2:mb-4
                prose-h3:text-lg sm:prose-h3:text-xl prose-h3:mt-6 prose-h3:mb-3
                prose-a:text-blue-700 hover:prose-a:text-blue-800 prose-a:font-semibold prose-a:underline
                prose-ul:list-disc prose-ol:list-decimal prose-li:text-slate-600 prose-li:text-sm sm:prose-li:text-base
                prose-img:rounded-2xl prose-img:shadow-xs prose-img:border prose-img:border-slate-200">
                {!! $article->content !!}
            </div>

            <!-- Commercial Callout inside Article -->
            <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-sm">
                <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 text-white text-xs font-semibold mb-2 backdrop-blur-xs">
                            🛍️ Promo Terbatas
                        </span>
                        <h4 class="text-lg sm:text-xl font-bold">Tertarik dengan Penawaran Ini?</h4>
                        <p class="text-blue-100 text-xs sm:text-sm mt-1 max-w-md">
                            Jangan lewatkan harga spesial untuk produk pilihan. Cek katalog promo kami sebelum kehabisan stok!
                        </p>
                    </div>
                    <a 
                        href="{{ route('catalog') }}?promo=1" 
                        class="shrink-0 px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-amber-950 font-bold text-xs sm:text-sm rounded-xl shadow-xs transition-colors flex items-center gap-2"
                    >
                        <span>Belanja Produk Promo</span>
                        <svg class="w-4 h-4 text-amber-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Share Buttons Section -->
            @php
                $shareUrl = urlencode(url()->current());
                $shareText = urlencode($article->title . ' - TokoOnline');
            @endphp
            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs sm:text-sm font-bold text-slate-800">
                    Bagikan info promo ini ke teman:
                </div>
                <div class="flex items-center gap-2">
                    <!-- WhatsApp -->
                    <a 
                        href="https://api.whatsapp.com/send?text={{ $shareText }}%20{{ $shareUrl }}" 
                        target="_blank" 
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold transition-colors"
                    >
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        <span>WhatsApp</span>
                    </a>

                    <!-- Facebook -->
                    <a 
                        href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" 
                        target="_blank" 
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-semibold transition-colors"
                    >
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        <span>Facebook</span>
                    </a>

                    <!-- Twitter / X -->
                    <a 
                        href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ $shareUrl }}" 
                        target="_blank" 
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 text-xs font-semibold transition-colors"
                    >
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        <span>X / Twitter</span>
                    </a>
                </div>
            </div>

        </article>

        <!-- Related Articles Section -->
        @if(isset($relatedArticles) && $relatedArticles->count() > 0)
            <div class="space-y-4 pt-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <div class="w-1.5 h-5 bg-blue-700 rounded-full"></div>
                        <span>Baca Promo & Berita Lainnya</span>
                    </h2>
                    <a href="{{ route('articles.index') }}" class="text-xs font-semibold text-blue-700 hover:text-blue-800 transition-colors">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @foreach($relatedArticles as $related)
                        @php
                            $rThumb = $resolveImage($related->thumbnail);
                            $rBadge = $getBadge($related->title);
                        @endphp
                        <article class="bg-white rounded-2xl overflow-hidden border border-slate-200/80 shadow-2xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group flex flex-col">
                            <a href="{{ route('articles.detail', $related->slug) }}" class="block aspect-[16/10] overflow-hidden relative bg-slate-100">
                                @if($rThumb)
                                    <img 
                                        src="{{ $rThumb }}" 
                                        alt="{{ $related->title }}" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-300">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                
                                <div class="absolute top-2.5 left-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border backdrop-blur-xs shadow-2xs {{ $rBadge['bg'] }} bg-white/95">
                                        {{ $rBadge['text'] }}
                                    </span>
                                </div>
                            </a>

                            <div class="p-4 sm:p-5 flex flex-col flex-1">
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mb-2">
                                    <span>{{ $related->created_at->format('d M Y') }}</span>
                                    <span>&bull;</span>
                                    <span>2 min</span>
                                </div>

                                <h3 class="text-sm sm:text-base font-bold text-slate-900 leading-snug group-hover:text-blue-700 transition-colors line-clamp-2 mb-2">
                                    <a href="{{ route('articles.detail', $related->slug) }}">
                                        {{ $related->title }}
                                    </a>
                                </h3>

                                <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-400">Tim Toko</span>
                                    <a href="{{ route('articles.detail', $related->slug) }}" class="font-bold text-blue-700 group-hover:underline">
                                        Baca &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
