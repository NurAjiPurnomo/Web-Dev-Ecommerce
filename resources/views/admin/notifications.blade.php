@extends('admin.layout')

@section('title', 'Siaran & Pengumuman Toko')

@section('content')
<div 
    class="space-y-6" 
    x-data="{
        deleteConfirmId: null,
        deleteConfirmTitle: '',
        targetType: 'semua',
        applyTemplate(tmplTitle, tmplContent) {
            $refs.titleInput.value = tmplTitle;
            $refs.contentInput.value = tmplContent;
        },
        confirmDelete(id, title) {
            this.deleteConfirmId = id;
            this.deleteConfirmTitle = title;
        }
    }"
>

    <!-- Header Section (Konsisten dengan Dashboard, Pesanan & Pelanggan) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Siaran &amp; Pengumuman Toko</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kirim pengumuman massal atau informasi penting toko ke lonceng website seluruh pelanggan.</p>
        </div>
        <div>
            <a 
                href="{{ route('admin.orders') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium transition-colors cursor-pointer shadow-xs"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>Kelola Pesanan</span>
            </a>
        </div>
    </div>

    <!-- Alert Sukses / Info -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between gap-3 text-xs sm:text-sm animate-in fade-in duration-200">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs p-1 cursor-pointer">✕</button>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl flex items-center justify-between gap-3 text-xs sm:text-sm animate-in fade-in duration-200">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('info') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-blue-700 hover:text-blue-900 font-bold text-xs p-1 cursor-pointer">✕</button>
        </div>
    @endif

    <!-- FORMULIR: Buat Pengumuman Baru Langsung di Halaman -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 shadow-xs">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-5">
            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm sm:text-base font-semibold text-slate-900">Buat Pengumuman Baru</h2>
                <p class="text-xs text-slate-500">Pesan akan langsung tampil di lonceng navbar pelanggan saat mereka membuka website toko.</p>
            </div>
        </div>

        <!-- Template Cepat -->
        <div class="mb-4">
            <span class="text-xs font-semibold text-slate-500 block mb-1.5">Template Cepat:</span>
            <div class="flex items-center gap-2 flex-wrap">
                <button 
                    type="button" 
                    @click="applyTemplate('Pemberitahuan Jadwal Pengiriman Toko', 'Halo Sahabat Toko, pesanan Anda tetap kami proses sesuai antrean. Khusus hari libur ekspedisi paket akan diserahkan ke kurir pada hari kerja berikutnya.')" 
                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors cursor-pointer"
                >
                    Jadwal Pengiriman
                </button>
                <button 
                    type="button" 
                    @click="applyTemplate('Promo Spesial Terbatas Sedang Berlangsung!', 'Kabar gembira! Nikmati promo potongan harga menarik dan diskon ongkir untuk produk pilihan toko kami hari ini.')" 
                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors cursor-pointer"
                >
                    Promo Spesial
                </button>
                <button 
                    type="button" 
                    @click="applyTemplate('Informasi Pemeliharaan Sistem Toko', 'Kami sedang melakukan peningkatan performa sistem toko online. Seluruh proses transaksi dan layanan tetap berjalan aman.')" 
                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors cursor-pointer"
                >
                    Pemeliharaan Sistem
                </button>
            </div>
        </div>

        <!-- Form Pengumuman -->
        <form action="{{ route('admin.notifications.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Target Penerima -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Target Penerima</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-w-md">
                    <label 
                        :class="targetType === 'semua' ? 'border-blue-600 bg-blue-50/50 text-blue-900 font-semibold' : 'border-slate-200 text-slate-600 font-medium'" 
                        class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all text-xs"
                    >
                        <input type="radio" name="target" value="semua" x-model="targetType" class="text-blue-600 focus:ring-blue-500">
                        <span>Semua Pelanggan (Broadcast Massal)</span>
                    </label>

                    <label 
                        :class="targetType === 'pelanggan' ? 'border-blue-600 bg-blue-50/50 text-blue-900 font-semibold' : 'border-slate-200 text-slate-600 font-medium'" 
                        class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all text-xs"
                    >
                        <input type="radio" name="target" value="pelanggan" x-model="targetType" class="text-blue-600 focus:ring-blue-500">
                        <span>Pelanggan Tertentu</span>
                    </label>
                </div>
            </div>

            <!-- Pilih Pelanggan (Jika spesifik) -->
            <div x-show="targetType === 'pelanggan'" x-collapse>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Nama Pelanggan</label>
                <select 
                    name="user_id" 
                    class="w-full sm:max-w-md px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs text-slate-800 outline-hidden cursor-pointer"
                >
                    <option value="">-- Pilih Pelanggan Penerima --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Judul Pengumuman -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Pengumuman <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    name="title" 
                    x-ref="titleInput" 
                    required 
                    placeholder="Contoh: Pemberitahuan Libur Operasional Toko" 
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs sm:text-sm text-slate-800 outline-hidden"
                >
            </div>

            <!-- Isi Pesan Pengumuman -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Isi Pesan Pengumuman <span class="text-rose-500">*</span></label>
                <textarea 
                    name="content" 
                    x-ref="contentInput" 
                    required 
                    rows="4" 
                    placeholder="Tuliskan isi pengumuman atau instruksi penting untuk pelanggan di sini..." 
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs sm:text-sm text-slate-800 outline-hidden leading-relaxed"
                ></textarea>
            </div>

            <!-- Tombol Kirim -->
            <div class="pt-2 flex items-center justify-end">
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs sm:text-sm transition-colors cursor-pointer shadow-xs"
                >
                    Kirim Pengumuman Sekarang
                </button>
            </div>
        </form>
    </div>

    <!-- TABEL: Riwayat Pengumuman Toko yang Pernah Dikirim -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Riwayat Pengumuman Toko</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar siaran broadcast manual yang telah diterbitkan ke pembeli.</p>
            </div>
            <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md">
                Total: <strong class="text-slate-900">{{ $announcements->total() }}</strong> Pengumuman
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-semibold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 min-w-[340px]">Judul &amp; Pesan Pengumuman</th>
                        <th class="px-5 py-3.5 min-w-[180px]">Target Penerima</th>
                        <th class="px-5 py-3.5 min-w-[150px]">Tanggal Terbit</th>
                        <th class="px-5 py-3.5 min-w-[130px]">Status Tayang</th>
                        <th class="px-5 py-3.5 text-right min-w-[130px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($announcements as $a)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Judul & Pesan -->
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold text-xs sm:text-sm text-slate-900 leading-snug">
                                    {{ $a->title }}
                                </div>
                                @if($a->content)
                                    <p class="text-xs text-slate-600 font-normal leading-relaxed mt-1">
                                        {{ $a->content }}
                                    </p>
                                @endif
                            </td>

                            <!-- Target Penerima -->
                            <td class="px-5 py-4 align-top">
                                @if($a->user)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-blue-600 text-white font-semibold text-[10px] flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($a->user->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 truncate">{{ $a->user->name }}</div>
                                            <div class="text-[11px] text-slate-400 font-normal truncate">{{ $a->user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-xs font-medium">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                        </svg>
                                        <span>Semua Pelanggan</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Tanggal Terbit -->
                            <td class="px-5 py-4 align-top whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-800">
                                    {{ $a->created_at ? $a->created_at->format('d M Y') : '-' }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    {{ $a->created_at ? $a->created_at->format('H:i') . ' WIB' : '' }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $a->created_at ? $a->created_at->diffForHumans() : '' }}
                                </div>
                            </td>

                            <!-- Status Tayang -->
                            <td class="px-5 py-4 align-top whitespace-nowrap">
                                @if($a->status === 'ditayangkan')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Ditayangkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Disembunyikan
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-4 align-top text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Toggle Status -->
                                    <form action="{{ route('admin.notifications.toggle', $a->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="px-2.5 py-1 rounded-lg border text-xs font-medium transition-colors cursor-pointer {{ $a->status === 'ditayangkan' ? 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }}"
                                            title="{{ $a->status === 'ditayangkan' ? 'Sembunyikan dari lonceng pembeli' : 'Tayangkan kembali ke pembeli' }}"
                                        >
                                            {{ $a->status === 'ditayangkan' ? 'Sembunyikan' : 'Tayangkan' }}
                                        </button>
                                    </form>

                                    <!-- Tombol Hapus -->
                                    <button 
                                        type="button" 
                                        @click="confirmDelete({{ $a->id }}, '{{ addslashes($a->title) }}')" 
                                        class="p-1.5 rounded-lg text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 transition-colors cursor-pointer"
                                        title="Hapus Pengumuman"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">
                                <div class="max-w-sm mx-auto text-center">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center mx-auto mb-2.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-semibold text-slate-800">Belum Ada Pengumuman Siaran</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Anda belum pernah membuat pengumuman massal. Gunakan formulir di atas untuk mengirim pengumuman baru ke pelanggan.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    Menampilkan <strong>{{ $announcements->firstItem() ?? 0 }}</strong> sampai <strong>{{ $announcements->lastItem() ?? 0 }}</strong> dari <strong>{{ $announcements->total() }}</strong> pengumuman.
                </div>
                <div>
                    {{ $announcements->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- MODAL CONFIRM DELETE -->
    <div 
        x-show="deleteConfirmId" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        @keydown.escape.window="deleteConfirmId = null"
    >
        <div 
            class="relative w-full max-w-sm bg-white rounded-xl p-6 shadow-2xl border border-slate-100 text-center animate-in zoom-in-95 duration-150" 
            @click.outside="deleteConfirmId = null"
        >
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 border border-rose-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-900 mb-1">Hapus Pengumuman Ini?</h3>
            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                Pengumuman <strong class="text-slate-800" x-text="'&ldquo;' + deleteConfirmTitle + '&rdquo;'"></strong> akan dihapus permanen dari lonceng pembeli.
            </p>

            <form :action="'{{ url('/admin/notifications') }}/' + deleteConfirmId + '/delete'" method="POST" class="flex items-center justify-center gap-2">
                @csrf
                <button 
                    type="button" 
                    @click="deleteConfirmId = null" 
                    class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 text-xs font-medium hover:bg-slate-50 cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-medium shadow-xs cursor-pointer"
                >
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
