@extends('admin.layout')

@section('title', 'Manajemen Banner Promo')

@section('content')
<div x-data="bannerManager()" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight">Manajemen Banner Promo Slide</h1>
            <p class="text-xs sm:text-sm text-slate-500">Kelola gambar banner slider, judul promo, badge, dan link tujuan di halaman utama</p>
        </div>
        <button 
            @click="openCreate()" 
            class="inline-flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl shadow-xs transition-colors cursor-pointer shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Banner Promo</span>
        </button>
    </div>

    <!-- Banners Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($banners as $b)
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-2xs flex flex-col justify-between group">
                <div>
                    <!-- Image Preview Container -->
                    <div class="relative w-full h-44 bg-slate-100 overflow-hidden">
                        <img src="{{ $b->image }}" alt="{{ $b->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-slate-950/70 p-4 flex flex-col justify-between text-white">
                            <div class="flex items-center justify-between">
                                <span class="bg-blue-600 text-white text-[10px] font-semibold px-2.5 py-1 rounded-md uppercase tracking-wider shadow-xs">
                                    {{ $b->subtitle ?: 'BANNER PROMO' }}
                                </span>
                                <span class="{{ $b->status === 'aktif' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }} text-[10px] font-bold px-2 py-0.5 rounded shadow-xs uppercase">
                                    {{ $b->status === 'aktif' ? 'Tayang' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div>
                                @if($b->highlight_text)
                                    <span class="text-amber-400 font-bold text-xs block mb-0.5">{{ $b->highlight_text }}</span>
                                @endif
                                <h3 class="font-semibold text-sm sm:text-base leading-snug line-clamp-1 drop-shadow-sm">{{ $b->title }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Details Content -->
                    <div class="p-4 space-y-3">
                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                            {{ $b->description ?: 'Tidak ada deskripsi banner.' }}
                        </p>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                            <div class="flex items-center gap-1.5 truncate max-w-[180px]">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <span class="truncate font-mono text-[11px]">{{ $b->button_url }}</span>
                            </div>
                            <span class="bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded border border-slate-200">
                                {{ $b->button_text }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                    <form action="{{ route('admin.banners.toggle', $b->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs font-bold px-2.5 py-1.5 rounded-lg border transition-colors cursor-pointer {{ $b->status === 'aktif' ? 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }}">
                            {{ $b->status === 'aktif' ? 'Sembunyikan' : 'Tayangkan' }}
                        </button>
                    </form>

                    <div class="flex items-center gap-1.5">
                        <button 
                            @click='openEdit(@json($b))' 
                            class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg border border-blue-200 transition-colors cursor-pointer"
                        >
                            Edit
                        </button>
                        <form action="{{ route('admin.banners.delete', $b->id) }}" method="POST" @submit.prevent="$dispatch('open-confirm', { message: 'Hapus banner promo ini? Banner tidak akan lagi tampil di halaman utama.', action: () => $el.submit() })">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 px-2.5 py-1.5 rounded-lg border border-rose-200 transition-colors cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400">
                <div class="max-w-sm mx-auto space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center mx-auto text-blue-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-700 text-base">Belum Ada Banner Promo</h3>
                    <p class="text-xs text-slate-400">Tambahkan banner promo pertama Anda untuk mempercantik slider halaman utama toko.</p>
                    <button @click="openCreate()" class="inline-block bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl">
                        + Tambah Banner Pertama
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- MODAL CREATE / EDIT BANNER -->
    <div 
        x-show="showModal" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.away="showModal = false" 
            class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-5 border border-slate-100 transform transition-all"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900" x-text="isEdit ? 'Edit Banner Promo' : 'Tambah Banner Promo Baru'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer">&times;</button>
            </div>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul Utama Banner <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="title" 
                        x-model="form.title" 
                        required 
                        placeholder="Contoh: GAYA CASUAL STYLISH" 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium focus:bg-white focus:border-blue-700 focus:outline-none"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sub-Judul / Label Top</label>
                        <input 
                            type="text" 
                            name="subtitle" 
                            x-model="form.subtitle" 
                            placeholder="Contoh: PROMO KHUSUS HARI INI" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Highlight Diskon / Teks Emas</label>
                        <input 
                            type="text" 
                            name="highlight_text" 
                            x-model="form.highlight_text" 
                            placeholder="Contoh: DISKON UP TO 50% OFF" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">URL Gambar Banner (Unsplash / Image URL) <span class="text-red-500">*</span></label>
                    <input 
                        type="url" 
                        name="image" 
                        x-model="form.image" 
                        required 
                        placeholder="https://images.unsplash.com/photo-1490578474895..." 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium focus:bg-white focus:border-blue-700 focus:outline-none"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Teks Tombol Aksi</label>
                        <input 
                            type="text" 
                            name="button_text" 
                            x-model="form.button_text" 
                            placeholder="Belanja Sekarang" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">URL Link Tujuan Tombol</label>
                        <input 
                            type="text" 
                            name="button_url" 
                            x-model="form.button_url" 
                            placeholder="/catalog" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi Singkat Promo</label>
                    <textarea 
                        name="description" 
                        x-model="form.description" 
                        rows="2" 
                        placeholder="Tuliskan keterangan promo banner..." 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs sm:text-sm font-medium focus:bg-white focus:border-blue-700 focus:outline-none"
                    ></textarea>
                </div>

                <template x-if="isEdit">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Banner</label>
                        <select name="status" x-model="form.status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-semibold focus:bg-white focus:border-blue-700 focus:outline-none">
                            <option value="aktif">Aktif (Tayang)</option>
                            <option value="nonaktif">Nonaktif (Sembunyi)</option>
                        </select>
                    </div>
                </template>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 shadow-xs transition-colors cursor-pointer" x-text="isEdit ? 'Simpan Perubahan' : 'Terbitkan Banner'"></button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function bannerManager() {
    return {
        showModal: false,
        isEdit: false,
        formAction: '{{ route("admin.banners.store") }}',
        form: {
            id: null,
            title: '',
            subtitle: '',
            highlight_text: '',
            description: '',
            image: '',
            button_text: 'Belanja Sekarang',
            button_url: '/catalog',
            status: 'aktif',
        },
        openCreate() {
            this.isEdit = false;
            this.formAction = '{{ route("admin.banners.store") }}';
            this.form = {
                id: null,
                title: '',
                subtitle: '',
                highlight_text: '',
                description: '',
                image: '',
                button_text: 'Belanja Sekarang',
                button_url: '/catalog',
                status: 'aktif',
            };
            this.showModal = true;
        },
        openEdit(b) {
            this.isEdit = true;
            this.formAction = '/admin/banners/' + b.id + '/update';
            this.form = {
                id: b.id,
                title: b.title || '',
                subtitle: b.subtitle || '',
                highlight_text: b.highlight_text || '',
                description: b.description || '',
                image: b.image || '',
                button_text: b.button_text || 'Belanja Sekarang',
                button_url: b.button_url || '/catalog',
                status: b.status || 'aktif',
            };
            this.showModal = true;
        }
    };
}
</script>
@endsection
