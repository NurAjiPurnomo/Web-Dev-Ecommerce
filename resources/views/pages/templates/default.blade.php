@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="bg-slate-50 min-h-screen">
    
    <!-- Hero Banner Section -->
    @if($page->banner_image)
        <div class="w-full h-[40vh] md:h-[50vh] bg-slate-900 relative">
            <img src="{{ asset($page->banner_image) }}" alt="{{ $page->title }}" class="w-full h-full object-cover opacity-70">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent"></div>
            
            <div class="absolute bottom-0 inset-x-0 pb-12 px-4 sm:px-6 lg:px-8">
                <div class="max-w-4xl mx-auto text-center">
                    <h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight mb-4 shadow-sm">
                        {{ $page->title }}
                    </h1>
                </div>
            </div>
        </div>
    @else
        <!-- No Image Header -->
        <div class="bg-slate-900 text-white pt-24 pb-16 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-overlay"></div>
            <div class="max-w-4xl mx-auto relative z-10 text-center">
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4">{{ $page->title }}</h1>
            </div>
        </div>
    @endif

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative z-20 {{ $page->banner_image ? '-mt-10' : '' }}">
        
        <article class="bg-white rounded-2xl shadow-xl overflow-hidden mb-12 border border-slate-200">
            
            <!-- Main Content (TinyMCE) -->
            @if($page->content)
                <div class="px-6 sm:px-10 py-8 lg:py-12 border-b border-slate-100">
                    <div class="prose prose-lg prose-slate max-w-none prose-a:text-blue-700 prose-a:font-semibold hover:prose-a:text-blue-800 prose-headings:font-bold prose-headings:tracking-tight prose-img:rounded-2xl prose-img:shadow-md">
                        {!! $page->content !!}
                    </div>
                </div>
            @endif

            <!-- Custom Blocks -->
            @if($page->blocks && is_array($page->blocks) && count($page->blocks) > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($page->blocks as $block)
                        
                        <!-- FAQ Block -->
                        @if($block['type'] === 'faq' && !empty($block['data']['items']))
                            <div class="px-6 sm:px-10 py-10 bg-white">
                                <h2 class="text-2xl font-bold text-slate-900 mb-6 text-center">Pertanyaan yang Sering Diajukan</h2>
                                <div class="space-y-4" x-data="{ active: null }">
                                    @foreach($block['data']['items'] as $index => $item)
                                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white hover:border-blue-300 transition-colors">
                                            <button @click="active !== {{ $index }} ? active = {{ $index }} : active = null" class="flex items-center justify-between w-full p-5 text-left focus:outline-none">
                                                <span class="font-semibold text-slate-800">{{ $item['q'] ?? '' }}</span>
                                                <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-200" :class="{'rotate-180': active === {{ $index }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="active === {{ $index }}" x-collapse class="px-5 pb-5 pt-0">
                                                <p class="text-slate-600 leading-relaxed">{{ $item['a'] ?? '' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Grid Info Block -->
                        @if($block['type'] === 'grid_info' && !empty($block['data']['items']))
                            <div class="px-6 sm:px-10 py-12 bg-slate-50/50">
                                @if(!empty($block['data']['title']))
                                    <h2 class="text-2xl font-bold text-slate-900 mb-8 text-center">{{ $block['data']['title'] }}</h2>
                                @endif
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    @foreach($block['data']['items'] as $item)
                                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 text-center hover:-translate-y-1 transition-transform duration-300">
                                            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 border border-blue-100">
                                                {{ $item['icon'] ?? '✨' }}
                                            </div>
                                            <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $item['title'] ?? '' }}</h3>
                                            <p class="text-sm text-slate-600">{{ $item['desc'] ?? '' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Contact Cards Block -->
                        @if($block['type'] === 'contact_cards')
                            <div class="px-6 sm:px-10 py-12 bg-white">
                                <h2 class="text-2xl font-bold text-slate-900 mb-8 text-center">Hubungi Kami</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    @if(!empty($block['data']['email']))
                                        <a href="mailto:{{ $block['data']['email'] }}" class="flex items-center gap-4 p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition-all group">
                                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-0.5">Email</p>
                                                <p class="text-sm font-semibold text-slate-900">{{ $block['data']['email'] }}</p>
                                            </div>
                                        </a>
                                    @endif
                                    @if(!empty($block['data']['phone']))
                                        <a href="tel:{{ $block['data']['phone'] }}" class="flex items-center gap-4 p-5 rounded-2xl border border-slate-200 hover:border-emerald-500 hover:shadow-md transition-all group">
                                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-0.5">Telepon / WhatsApp</p>
                                                <p class="text-sm font-semibold text-slate-900">{{ $block['data']['phone'] }}</p>
                                            </div>
                                        </a>
                                    @endif
                                </div>
                                @if(!empty($block['data']['address']))
                                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 mb-8">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Alamat Lengkap</p>
                                                <p class="text-slate-800 leading-relaxed">{{ $block['data']['address'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($block['data']['map_url']))
                                    <div class="w-full aspect-video md:aspect-[21/9] rounded-2xl overflow-hidden border border-slate-200 shadow-inner">
                                        <iframe src="{{ $block['data']['map_url'] }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                    </div>
                                @endif
                            </div>
                        @endif

                    @endforeach
                </div>
            @endif

        </article>

    </div>
</div>
@endsection
