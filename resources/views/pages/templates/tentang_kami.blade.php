@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="bg-slate-100 min-h-screen pb-20">
    
    <!-- Header Banner -->
    <div class="bg-blue-700 text-white pt-8 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl sm:text-3xl font-bold">{{ $page->title }}</h1>
            <p class="text-blue-200 text-sm mt-1">Kenali lebih dekat tentang toko kami.</p>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-10">
        
        <!-- About Box (Split Layout) -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                
                <!-- Left: Text Content -->
                <div class="p-6 sm:p-10 border-b lg:border-b-0 lg:border-r border-slate-200">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100">Cerita Toko Kami</h2>
                    
                    @if($page->content)
                        <div class="prose prose-sm sm:prose-base prose-slate max-w-none text-slate-600 prose-p:mb-4">
                            {!! $page->content !!}
                        </div>
                    @else
                        <p class="text-slate-500 italic">Belum ada deskripsi.</p>
                    @endif
                </div>
                
                <!-- Right: Image/Logo -->
                @if($page->banner_image)
                <div class="bg-slate-50 flex items-center justify-center p-8 min-h-[300px]">
                    <img src="{{ asset($page->banner_image) }}" alt="Banner" class="max-w-full max-h-[300px] object-contain drop-shadow-md">
                </div>
                @else
                <div class="bg-slate-50 flex items-center justify-center p-8 min-h-[300px]">
                    <div class="text-slate-400 text-center">
                        <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="text-sm">Tidak ada gambar</span>
                    </div>
                </div>
                @endif

            </div>
        </div>

        <!-- Dynamic Blocks -->
        @if($page->blocks && is_array($page->blocks) && count($page->blocks) > 0)
            <div class="space-y-6">
                @foreach($page->blocks as $block)
                    
                    <!-- Grid Info Block -->
                    @if($block['type'] === 'grid_info' && !empty($block['data']['items']))
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 sm:p-8">
                            @if(!empty($block['data']['title']))
                                <h2 class="text-xl font-bold text-slate-800 mb-6 border-l-4 border-blue-600 pl-3 leading-none">{{ $block['data']['title'] }}</h2>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                                @foreach($block['data']['items'] as $index => $item)
                                    <div class="flex items-start gap-4 p-4 rounded-lg bg-slate-50 border border-slate-100 hover:border-blue-200 hover:shadow-sm transition-all">
                                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <h4 class="text-base font-bold text-slate-800 mb-1">{{ $item['title'] ?? '' }}</h4>
                                            <p class="text-sm text-slate-600 leading-relaxed">{{ $item['desc'] ?? '' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- FAQ Block -->
                    @if($block['type'] === 'faq' && !empty($block['data']['items']))
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-slate-800 mb-6 border-l-4 border-blue-600 pl-3 leading-none">Pertanyaan Umum</h2>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" x-data="{ active: null }">
                                @foreach($block['data']['items'] as $index => $item)
                                    <div class="border border-slate-200 rounded-lg overflow-hidden h-fit">
                                        <button @click="active !== {{ $index }} ? active = {{ $index }} : active = null" class="flex items-center justify-between w-full p-4 text-left focus:outline-none bg-slate-50 hover:bg-slate-100 transition-colors">
                                            <span class="font-semibold text-slate-800 text-sm pr-4">{{ $item['q'] ?? '' }}</span>
                                            <svg class="w-4 h-4 text-slate-500 flex-shrink-0 transform transition-transform" :class="{'rotate-180': active === {{ $index }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div x-show="active === {{ $index }}" x-collapse class="p-4 text-sm text-slate-600 border-t border-slate-200 bg-white">
                                            {{ $item['a'] ?? '' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
