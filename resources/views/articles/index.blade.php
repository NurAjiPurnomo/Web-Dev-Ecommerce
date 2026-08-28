@extends('layouts.app')

@section('title', 'Berita & Promo Terbaru')

@section('content')
<div class="bg-slate-50 min-h-screen pb-24 font-sans">
    
    <!-- Hero / Featured Article Section -->
    @if($featured = $articles->first())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-12">
            <div class="bg-white rounded-3xl overflow-hidden shadow-xs border border-slate-200/60 group flex flex-col lg:flex-row hover:shadow-md transition-shadow duration-300">
                <!-- Image Side -->
                <div class="lg:w-7/12 relative overflow-hidden aspect-video lg:aspect-auto min-h-[300px] lg:min-h-[450px]">
                    @if($featured->thumbnail)
                        <img src="{{ $featured->thumbnail }}" alt="{{ $featured->title }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-in-out">
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-700 to-blue-900"></div>
                    @endif
                    <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors duration-500"></div>
                </div>
                
                <!-- Content Side -->
                <div class="lg:w-5/12 p-8 sm:p-12 flex flex-col justify-center">
                    
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 leading-tight mb-4 group-hover:text-blue-700 transition-colors line-clamp-3">
                        <a href="{{ route('articles.detail', $featured->slug) }}" class="focus:outline-none">
                            {{ $featured->title }}
                        </a>
                    </h1>
                    
                    <p class="text-slate-500 text-base leading-relaxed mb-8 line-clamp-3">
                        {{ $featured->summary ?: Str::limit(strip_tags($featured->content), 150) }}
                    </p>
                    
                    <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-4">
                        <div class="text-sm font-medium text-slate-600 flex items-center gap-2">
                            <span>Ditulis oleh <strong class="text-slate-900">Admin Toko</strong></span>
                            <span class="text-slate-300">|</span>
                            <span>{{ $featured->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Articles Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <div class="w-1.5 h-6 bg-blue-700 rounded-full"></div>
                Kabar Terkini & Promo Eksklusif
            </h2>
        </div>

        @if($articles->count() > 1 || ($articles->currentPage() > 1 && $articles->count() > 0))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($articles as $index => $article)
                    @if($index === 0 && $articles->currentPage() == 1) @continue @endif
                    
                    <article class="bg-white rounded-2xl overflow-hidden shadow-xs border border-slate-200/60 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex flex-col">
                        <a href="{{ route('articles.detail', $article->slug) }}" class="block aspect-[16/10] overflow-hidden relative bg-slate-100">
                            @if($article->thumbnail)
                                <img src="{{ $article->thumbnail }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </a>
                        
                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="text-lg font-bold text-slate-900 leading-tight mb-3 group-hover:text-blue-700 transition-colors line-clamp-2">
                                <a href="{{ route('articles.detail', $article->slug) }}">
                                    {{ $article->title }}
                                </a>
                            </h3>
                            <p class="text-slate-500 text-sm leading-relaxed mb-5 line-clamp-2">
                                {{ $article->summary ?: Str::limit(strip_tags($article->content), 100) }}
                            </p>
                            
                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
                                <span class="text-slate-500 font-medium">{{ $article->created_at->format('d M Y') }}</span>
                                <span class="font-bold text-blue-700 hover:underline">
                                    Baca &rarr;
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12 flex justify-center">
                {{ $articles->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3l-4 4m0 0l-4-4m4 4V4"/></svg>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Belum Ada Berita</h3>
                <p class="text-slate-500">Berita promo akan segera hadir di halaman ini.</p>
            </div>
        @endif
    </div>

</div>

<style>
    /* Hide scrollbar for top nav */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

@endsection
