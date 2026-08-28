@extends('admin.layout')

@section('title', 'Pusat Manajemen Promo & Campaign - Admin Panel')

@section('content')
<div x-data="{ showAddNotifModal: false }" class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">Pusat Pemasaran</span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Pengaturan Tampilan Promo &amp; Broadcast</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-1">Pusat Kelola Halaman Promo</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola banner promo, voucher toko, produk flash sale, dan pengumuman broadcast yang dibuat oleh Admin</p>
        </div>
    </div>

    <!-- 4 KPI SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <!-- Banner Promo Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Banner Promo</span>
                <div class="text-2xl font-bold text-slate-900">{{ $banners->where('status', 'aktif')->count() }} <span class="text-xs font-semibold text-slate-400">/ {{ $banners->count() }} Banner</span></div>
                <p class="text-[11px] text-blue-700 font-semibold">Tampil di Hero Slider &amp; Promo</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>

        <!-- Voucher Aktif Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Voucher Toko</span>
                <div class="text-2xl font-bold text-slate-900">{{ $vouchers->where('status', 'aktif')->count() }} <span class="text-xs font-semibold text-slate-400">Voucher</span></div>
                <p class="text-[11px] text-emerald-700 font-semibold">Tampil di Pusat Voucher Promo</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 011 1.732 2 2 0 01-1 1.732V17a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 01-1-1.732 2 2 0 011-1.732V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
        </div>

        <!-- Produk Flash Sale Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Flash Sale</span>
                <div class="text-2xl font-bold text-slate-900">{{ $promoProducts->count() }} <span class="text-xs font-semibold text-slate-400">SKU Diskon</span></div>
                <p class="text-[11px] text-purple-700 font-semibold">Auto Flash Sale Beranda &amp; Promo</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-700 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>

        <!-- Broadcast Pengumuman Admin Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Broadcast Admin</span>
                <div class="text-2xl font-bold text-slate-900">{{ $announcements->where('status', 'ditayangkan')->count() }} <span class="text-xs font-semibold text-slate-400">Aktif</span></div>
                <p class="text-[11px] text-amber-700 font-semibold">Tampil di Lonceng / Banner Toko</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- SECTION 1: HERO PROMO BANNER MANAGEMENT -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 bg-blue-700 rounded-full"></span>
                    <span>1. Banner Campaign Utama Halaman Promo</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Banner yang aktif pada urutan pertama akan otomatis dijadikan Banner Header Halaman Promo</p>
            </div>
            <a href="{{ route('admin.banners') }}" class="bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Kelola / Tambah Banner</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($banners as $b)
                <div class="border border-slate-200 rounded-xl p-4 flex gap-4 items-center bg-slate-50/50 hover:bg-slate-50 transition-colors">
                    <img src="{{ $b->image }}" alt="{{ $b->title }}" class="w-24 h-20 object-cover rounded-lg border border-slate-200 shrink-0">
                    <div class="space-y-1 min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-800 font-semibold text-[10px] px-2 py-0.5 rounded">{{ $b->subtitle ?: 'BANNER PROMO' }}</span>
                            @if($b->status === 'aktif')
                                <span class="bg-emerald-100 text-emerald-800 font-semibold text-[10px] px-2 py-0.5 rounded border border-emerald-200">Aktif</span>
                            @else
                                <span class="bg-slate-200 text-slate-600 font-semibold text-[10px] px-2 py-0.5 rounded">Nonaktif</span>
                            @endif
                        </div>
                        <h4 class="font-semibold text-slate-900 text-sm truncate">{{ $b->title }}</h4>
                        @if($b->highlight_text)
                            <p class="text-xs font-bold text-amber-600">{{ $b->highlight_text }}</p>
                        @endif
                        <p class="text-[11px] text-slate-500 line-clamp-1">{{ $b->description }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center py-6 text-slate-400 text-xs">Belum ada banner promo. Klik Kelola Banner untuk membuat.</div>
            @endforelse
        </div>
    </div>

    <!-- SECTION 2: VOUCHER & KUPON PROMO -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-600 rounded-full"></span>
                    <span>2. Kupon &amp; Voucher Diskon Toko</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Voucher aktif akan langsung dapat diklaim pembeli pada Pusat Voucher Halaman Promo</p>
            </div>
            <a href="{{ route('admin.vouchers') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Kelola / Buat Voucher</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Kode Voucher</th>
                        <th class="px-4 py-3">Jenis Diskon</th>
                        <th class="px-4 py-3">Nominal Potongan</th>
                        <th class="px-4 py-3">Min. Belanja</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3 font-mono font-semibold text-blue-700">{{ $v->code }}</td>
                            <td class="px-4 py-3 uppercase font-bold text-slate-700">{{ $v->type }}</td>
                            <td class="px-4 py-3 font-bold text-emerald-700">{{ $v->formatted_discount }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $v->formatted_min_spend }}</td>
                            <td class="px-4 py-3">
                                @if($v->status === 'aktif')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-semibold px-2.5 py-0.5 rounded-full border border-emerald-200">Tampil di Promo</span>
                                @else
                                    <span class="bg-slate-200 text-slate-600 text-[10px] font-semibold px-2.5 py-0.5 rounded-full">Sembunyi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada voucher promo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 3: PRODUK DISKON & FLASH SALE -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 bg-purple-600 rounded-full"></span>
                    <span>3. Produk Diskon &amp; Flash Sale</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Produk yang memiliki harga coret / diskon otomatis masuk ke Halaman Promo &amp; Flash Sale Beranda</p>
            </div>
            <a href="{{ route('admin.products') }}" class="bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edit Diskon di Manajemen Produk</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            @forelse($promoProducts->take(8) as $p)
                <div class="border border-slate-200 rounded-xl p-3 bg-white space-y-2 relative">
                    @if($p->discount)
                        <span class="absolute top-2 right-2 bg-red-600 text-white font-semibold text-[10px] px-2 py-0.5 rounded">
                            {{ $p->discount }}
                        </span>
                    @endif
                    <img src="{{ $p->image }}" alt="{{ $p->name }}" class="w-full h-32 object-cover rounded-lg border border-slate-100">
                    <h4 class="font-bold text-slate-900 text-xs truncate">{{ $p->name }}</h4>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-blue-700 text-xs">{{ $p->formatted_price }}</span>
                        @if($p->original_price)
                            <span class="text-[10px] text-slate-400 line-through">{{ $p->formatted_original_price }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center py-6 text-slate-400 text-xs">Belum ada produk promo diskon. Edit produk di Manajemen Produk untuk memberi diskon.</div>
            @endforelse
        </div>
    </div>

    <!-- SECTION 4: PENGUMUMAN & BROADCAST PROMO TOKO (DIBUAT OLEH ADMIN) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 bg-amber-600 rounded-full"></span>
                    <span>4. Pengumuman &amp; Broadcast Promo Toko (Dibuat oleh Admin)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Siarkan pesan promo toko, pengumuman libur toko, atau event diskon ke lonceng notifikasi pelanggan</p>
            </div>
            <button 
                type="button" 
                @click="showAddNotifModal = true"
                class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Broadcast Promo Baru</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Judul Broadcast Promo</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Sasaran</th>
                        <th class="px-4 py-3">Tanggal Terbit</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($announcements as $n)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3 font-bold text-slate-900">
                                {{ $n->title }}
                                @if($n->content)
                                    <div class="text-[11px] font-normal text-slate-500 line-clamp-1">{{ $n->content }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="bg-purple-50 text-purple-700 font-bold text-[10px] px-2 py-0.5 rounded border border-purple-200">{{ strtoupper($n->type) }}</span>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-700">{{ strtoupper($n->target) }}</td>
                            <td class="px-4 py-3 text-slate-500 font-mono">{{ $n->created_at ? $n->created_at->format('d M Y') : '-' }}</td>
                            <td class="px-4 py-3">
                                @if($n->status === 'ditayangkan')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-semibold px-2.5 py-0.5 rounded-full border border-emerald-200">● Ditayangkan</span>
                                @else
                                    <span class="bg-slate-200 text-slate-600 text-[10px] font-semibold px-2.5 py-0.5 rounded-full">● Selesai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('admin.notifications.toggle', $n->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="redirect_to_promos" value="1">
                                    <button type="submit" class="text-xs font-bold {{ $n->status === 'ditayangkan' ? 'text-red-600 bg-red-50 border-red-200 hover:bg-red-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }} px-3 py-1 rounded-lg border transition-colors cursor-pointer">
                                        {{ $n->status === 'ditayangkan' ? 'Hentikan' : 'Tayangkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada broadcast promo buatan admin disiarkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL BUAT BROADCAST PROMO BARU -->
    <div 
        x-show="showAddNotifModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <div @click.outside="showAddNotifModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-semibold text-slate-900 text-base">Terbitkan Broadcast Promo Toko</h3>
                <button type="button" @click="showAddNotifModal = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>

            <form action="{{ route('admin.notifications.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="redirect_to_promos" value="1">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Judul Broadcast Promo <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: 📢 Promo Spesial Gajian Diskon s.d 70%!" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tipe Broadcast <span class="text-red-500">*</span></label>
                        <select name="type" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                            <option value="banner">Banner Promo</option>
                            <option value="popup">Pop-up Pengumuman</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Sasaran Pengguna <span class="text-red-500">*</span></label>
                        <select name="target" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                            <option value="semua">Semua Pengguna</option>
                            <option value="pelanggan">Pelanggan Saja</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Isi Pesan Broadcast Promo</label>
                    <textarea name="content" rows="3" placeholder="Tuliskan deskripsi detail pesan promo atau pengumuman resmi..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 flex gap-2 justify-end">
                    <button type="button" @click="showAddNotifModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md cursor-pointer">Siarkan Broadcast Promo</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
