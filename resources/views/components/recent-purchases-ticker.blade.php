@if(!empty($recentPurchases) && count($recentPurchases) > 0)
<div class="bg-blue-900 border-b border-blue-950 text-white text-xs py-2 px-3 sm:px-4 shadow-xs relative z-50 overflow-hidden select-none">
    <style>
        @keyframes runningSlowMarquee {
            0% { transform: translate3d(0, 0, 0); }
            100% { transform: translate3d(-50%, 0, 0); }
        }
        .animate-running-slow {
            display: flex;
            width: max-content;
            animation: runningSlowMarquee 75s linear infinite;
            will-change: transform;
        }
        .animate-running-slow:hover {
            animation-play-state: paused !important;
        }
        .ticker-fade-mask {
            mask-image: linear-gradient(to right, transparent 0%, black 6%, black 94%, transparent 100%);
            -webkit-mask-image: linear-gradient(to right, transparent 0%, black 6%, black 94%, transparent 100%);
        }
    </style>

    <!-- LEFT & RIGHT GRADIENT FADE OVERLAYS FOR SMOOTH EDGE TRANSITION -->
    <div class="absolute left-0 top-0 bottom-0 w-10 sm:w-16 bg-gradient-to-r from-blue-900 via-blue-900/80 to-transparent z-20 pointer-events-none"></div>
    <div class="absolute right-0 top-0 bottom-0 w-10 sm:w-16 bg-gradient-to-l from-blue-900 via-blue-900/80 to-transparent z-20 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto flex items-center relative">
        <!-- SCROLLING SLOW MARQUEE TRACK WITH GRADIENT EDGE MASK -->
        <div class="overflow-hidden w-full relative flex items-center ticker-fade-mask">
            <div class="animate-running-slow flex items-center gap-4 py-0.5">
                <!-- FIRST LOOP -->
                @foreach($recentPurchases as $p)
                    <a href="{{ $p['product_url'] }}" class="inline-flex items-center gap-2 text-slate-100 hover:text-amber-300 transition-colors font-medium text-xs shrink-0 cursor-pointer group/item">
                        <span class="font-extrabold text-white text-[11px] group-hover/item:text-amber-300 transition-colors">{{ $p['name'] }}</span>
                        <span class="text-blue-100 text-[11px]">baru saja membeli</span>
                        <span class="font-bold text-amber-300 underline underline-offset-2 decoration-amber-400/50 text-[11px] max-w-[180px] sm:max-w-[260px] truncate">{{ $p['product_name'] }}</span>
                        <span class="text-[10px] text-blue-200/80 bg-blue-950/60 px-2 py-0.5 rounded-full border border-blue-800/80 font-normal shrink-0">{{ $p['time_ago'] }}</span>
                    </a>
                    <span class="text-blue-400/50 font-mono text-xs shrink-0 select-none">|</span>
                @endforeach

                <!-- DUPLICATED LOOP FOR SEAMLESS SCROLL -->
                @foreach($recentPurchases as $p)
                    <a href="{{ $p['product_url'] }}" class="inline-flex items-center gap-2 text-slate-100 hover:text-amber-300 transition-colors font-medium text-xs shrink-0 cursor-pointer group/item">
                        <span class="font-extrabold text-white text-[11px] group-hover/item:text-amber-300 transition-colors">{{ $p['name'] }}</span>
                        <span class="text-blue-100 text-[11px]">baru saja membeli</span>
                        <span class="font-bold text-amber-300 underline underline-offset-2 decoration-amber-400/50 text-[11px] max-w-[180px] sm:max-w-[260px] truncate">{{ $p['product_name'] }}</span>
                        <span class="text-[10px] text-blue-200/80 bg-blue-950/60 px-2 py-0.5 rounded-full border border-blue-800/80 font-normal shrink-0">{{ $p['time_ago'] }}</span>
                    </a>
                    <span class="text-blue-400/50 font-mono text-xs shrink-0 select-none">|</span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
