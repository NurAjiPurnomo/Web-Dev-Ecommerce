@extends('layouts.app')

@section('title', $article->title)

@section('content')

<div class="bg-slate-50 min-h-screen pb-24 font-sans">
    
    <!-- Hero Image Section -->
    <div class="w-full relative bg-slate-900 h-[50vh] sm:h-[60vh] lg:h-[70vh]">
        @if($article->thumbnail)
            <img src="{{ asset($article->thumbnail) }}" alt="{{ $article->title }}" class="absolute inset-0 w-full h-full object-cover opacity-70">
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-blue-700 to-slate-900"></div>
        @endif
        
        <!-- Gradient overlay for readability at the bottom -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-50 via-transparent to-transparent"></div>
        
        <!-- Top Nav / Breadcrumb overlaying the hero -->
        <div class="absolute top-0 inset-x-0 z-10 p-4 sm:p-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center gap-2 text-xs sm:text-sm font-semibold text-white/90 backdrop-blur-sm bg-black/20 w-max px-4 py-2 rounded-full">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Beranda</a>
                <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('articles.index') }}" class="hover:text-white transition-colors">Berita Promo</a>
                <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-white opacity-70">Detail Berita</span>
            </div>
        </div>
    </div>

    <!-- Article Content Wrapper (Floating over Hero) -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-32 sm:-mt-48 relative z-20">
        
        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200/50 p-6 sm:p-10 md:p-14 mb-16">
            
            <!-- Article Header -->
            <div class="mb-10 text-center">
                
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight mb-6">
                    {{ $article->title }}
                </h1>
                
                <div class="flex items-center justify-center gap-2 text-sm text-slate-600 font-medium">
                    <span>Oleh <strong class="text-slate-900">Admin Toko</strong></span>
                    <span class="text-slate-300">|</span>
                    <span>Diterbitkan {{ $article->created_at->format('d M Y') }}</span>
                </div>
            </div>

            <!-- Summary (Lead) -->
            @if($article->summary)
                <div class="text-xl md:text-2xl text-slate-700 font-semibold leading-relaxed mb-10 text-center px-4 sm:px-8 border-b border-slate-100 pb-10">
                    "{{ $article->summary }}"
                </div>
            @endif

            <!-- Body Content -->
            <div class="mx-auto max-w-3xl">
                <article class="prose prose-lg sm:prose-xl prose-slate max-w-none 
                    prose-p:leading-loose prose-p:text-slate-600 
                    prose-a:text-blue-700 hover:prose-a:text-blue-800 prose-a:font-semibold prose-a:underline-offset-4
                    prose-img:rounded-2xl prose-img:shadow-lg prose-img:w-full 
                    prose-headings:font-extrabold prose-headings:text-slate-900 
                    prose-h2:text-3xl prose-h3:text-2xl
                    prose-li:text-slate-600 prose-ul:list-disc prose-ol:list-decimal
                    mb-12">
                    {!! $article->content !!}
                </article>
            </div>

            <!-- Share Section -->
            <div class="mt-12 pt-8 border-t border-slate-100 text-center">
                <span class="block font-bold text-slate-800 mb-4">Bagikan Promo Ini</span>
                <div class="flex items-center justify-center gap-4">
                    <button class="text-blue-600 hover:text-blue-800 hover:underline font-medium text-sm transition-colors">Facebook</button>
                    <span class="text-slate-300">&bull;</span>
                    <button class="text-blue-400 hover:text-blue-600 hover:underline font-medium text-sm transition-colors">Twitter</button>
                    <span class="text-slate-300">&bull;</span>
                    <button class="text-green-600 hover:text-green-800 hover:underline font-medium text-sm transition-colors">WhatsApp</button>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Related Articles -->
    @if($relatedArticles->count() > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <div class="w-1.5 h-6 bg-blue-700 rounded-full"></div>
                Baca Promo Lainnya
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($relatedArticles as $related)
                <article class="bg-white rounded-2xl overflow-hidden shadow-xs border border-slate-200/60 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex flex-col">
                    <a href="{{ route('articles.detail', $related->slug) }}" class="block aspect-[16/10] overflow-hidden relative bg-slate-100">
                        @if($related->thumbnail)
                            <img src="{{ asset($related->thumbnail) }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </a>
                    
                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="text-lg font-bold text-slate-900 leading-tight mb-3 group-hover:text-blue-700 transition-colors line-clamp-2">
                            <a href="{{ route('articles.detail', $related->slug) }}">
                                {{ $related->title }}
                            </a>
                        </h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-5 line-clamp-2">
                            {{ $related->summary ?: Str::limit(strip_tags($related->content), 100) }}
                        </p>
                        
                        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
                            <span class="text-slate-500 font-medium">{{ $related->created_at->format('d M Y') }}</span>
                            <span class="font-bold text-blue-700 hover:underline">
                                Baca &rarr;
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
    @endif

</div>

@endsection
