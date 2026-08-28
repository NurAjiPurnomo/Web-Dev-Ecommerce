<!-- WORLD-CLASS MODERN FLOATING TOAST NOTIFICATION POPUP (3-SECOND AUTO DISMISS) -->
<div 
    id="global-toast-notification"
    x-data="{
        open: false,
        title: '',
        image: '',
        qty: 1,
        progress: 100,
        timer: null,
        progressInterval: null,

        show(detail) {
            this.title = (detail && (detail.title || detail.name)) ? (detail.title || detail.name) : 'Produk Pilihan';
            this.image = (detail && detail.image) ? detail.image : '';
            this.qty = (detail && detail.qty) ? detail.qty : 1;
            this.open = true;
            this.progress = 100;

            if (this.timer) clearTimeout(this.timer);
            if (this.progressInterval) clearInterval(this.progressInterval);

            let startTime = Date.now();
            let duration = 3000;

            this.progressInterval = setInterval(() => {
                let elapsed = Date.now() - startTime;
                let remaining = Math.max(0, duration - elapsed);
                this.progress = (remaining / duration) * 100;
            }, 30);

            this.timer = setTimeout(() => {
                this.close();
            }, duration);
        },

        close() {
            this.open = false;
            if (this.timer) clearTimeout(this.timer);
            if (this.progressInterval) clearInterval(this.progressInterval);
        }
    }"
    x-init="
        window.addEventListener('show-toast', (e) => {
            show(e.detail);
        });
        @if(session()->has('toast_added'))
            let sessionToast = @json(session('toast_added'));
            setTimeout(() => {
                show({
                    title: sessionToast.name,
                    image: sessionToast.image,
                    qty: sessionToast.qty
                });
            }, 300);
        @endif
    "
    class="fixed top-20 right-4 left-4 sm:left-auto sm:max-w-md z-[99999] pointer-events-none"
>
    <!-- Toast Card -->
    <div 
        x-show="open" 
        x-cloak
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
        class="bg-slate-900 text-white rounded-2xl p-4 shadow-2xl border border-slate-800 flex items-center justify-between gap-3 pointer-events-auto relative overflow-hidden"
    >
        <!-- Progress Bar Indicator at Bottom (3 Seconds) -->
        <div 
            class="absolute bottom-0 left-0 h-1 bg-emerald-500 transition-all duration-75 linear"
            :style="'width: ' + progress + '%'"
        ></div>

        <!-- Left: Image & Success Info -->
        <div class="flex items-center gap-3 min-w-0">
            <template x-if="image">
                <img :src="image" class="w-11 h-11 rounded-xl object-cover border border-slate-700 shrink-0">
            </template>
            <template x-if="!image">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </template>

            <div class="min-w-0 space-y-0.5">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h5 class="text-[10px] font-black text-emerald-400 uppercase tracking-wider">Berhasil Ditambahkan!</h5>
                </div>
                <p class="text-xs font-bold text-slate-100 truncate" x-text="title || 'Produk telah masuk ke keranjang'"></p>
                <p class="text-[11px] text-slate-400" x-text="qty ? (qty + ' item masuk keranjang belanja') : 'Produk berhasil ditambahkan'"></p>
            </div>
        </div>

        <!-- Right: Action & Close -->
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('cart') }}" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition-colors whitespace-nowrap shadow-xs">
                Keranjang
            </a>
            <button type="button" @click="close()" class="text-slate-400 hover:text-white p-1 rounded-lg transition-colors cursor-pointer">
                ✕
            </button>
        </div>
    </div>
</div>
