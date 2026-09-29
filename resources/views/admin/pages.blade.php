@extends('admin.layout')

@section('title', 'Halaman CMS')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Halaman CMS</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola halaman statis toko seperti Syarat &amp; Ketentuan, Kebijakan, dan Info Toko.</p>
    </div>
    <button onclick="openAddModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors flex items-center gap-2 text-xs sm:text-sm shadow-2xs cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Tambah Halaman
    </button>
</div>

@if ($errors->any())
<div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
    <ul class="list-disc pl-5">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Pages Table -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                <tr>
                    <th class="py-3 px-4 font-semibold">Judul Halaman</th>
                    <th class="py-3 px-4 font-semibold">URL (Slug)</th>
                    <th class="py-3 px-4 font-semibold">Template Dasar</th>
                    <th class="py-3 px-4 font-semibold">Status</th>
                    <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($pages as $p)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-4 font-medium text-slate-800">{{ $p->title }}</td>
                    <td class="py-3 px-4 text-slate-500">
                        <a href="{{ route('page.show', $p->slug) }}" target="_blank" class="text-blue-600 hover:underline">/page/{{ $p->slug }}</a>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 capitalize">{{ str_replace('_', ' ', $p->template) }}</span>
                    </td>
                    <td class="py-3 px-4">
                        @if($p->status === 'aktif')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Draft</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick='editPage(@json($p))' class="p-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded transition-colors" title="Edit Halaman & Blok">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <form action="{{ route('admin.pages.toggle', $p->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded transition-colors" title="{{ $p->status === 'aktif' ? 'Jadikan Draft' : 'Aktifkan' }}">
                                    @if($p->status === 'aktif')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('admin.pages.delete', $p->id) }}" method="POST" class="inline" @submit.prevent="$dispatch('open-confirm', { message: 'Hapus halaman ini? Halaman yang dihapus tidak akan dapat diakses lagi oleh pengunjung.', action: () => $el.submit() })">
                                @csrf
                                <button type="submit" class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded transition-colors" title="Hapus Halaman">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2"></path></svg>
                            <p>Belum ada halaman yang dibuat.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- AlpineJS Modal Add/Edit Page -->
<div 
    x-data="pageBuilder()" 
    @open-page-modal.window="initModal($event.detail)"
    x-show="isOpen" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    x-cloak
>
    <!-- Backdrop -->
    <div x-show="isOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <!-- Modal Panel -->
    <div x-show="isOpen" x-transition class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[95vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between p-4 md:p-6 border-b border-slate-200 bg-slate-50">
            <h3 class="text-xl font-bold text-slate-800" x-text="modalTitle">Tambah Halaman Baru</h3>
            <button @click="closeModal()" class="text-slate-400 hover:text-slate-700 bg-white p-1.5 rounded-lg border border-slate-200 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="p-0 overflow-y-auto flex-1 bg-slate-100">
            <form id="formAddPage" :action="formAction" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div x-html="methodPut"></div>
                
                <!-- Hidden Input for Blocks JSON -->
                <input type="hidden" name="page_blocks" :value="JSON.stringify(blocks)">
                
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-6">
                    <h4 class="text-lg font-bold text-slate-900 mb-4 pb-2 border-b">1. Pengaturan Dasar Halaman</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Halaman <span class="text-red-500">*</span></label>
                            <input type="text" name="title" x-model="pageData.title" required class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Template Dasar <span class="text-red-500">*</span></label>
                            <select name="template" x-model="pageData.template" required class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white">
                                <option value="default">Halaman Standar (Kosong)</option>
                                <option value="tentang_kami">Tentang Kami (Profil Perusahaan)</option>
                                <option value="hubungi_kami">Hubungi Kami (Kontak & Peta)</option>
                                <option value="faq">Pusat Bantuan (FAQ)</option>
                                <option value="kebijakan_privasi">Kebijakan Privasi (Legal & Syarat)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Gambar Banner / Hero Utama (Opsional)</label>
                            <input type="file" name="banner_image" id="bannerImage" accept="image/*" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-2 border bg-slate-50">
                            <p class="text-xs text-slate-500 mt-1">Maks 2MB. Diabaikan jika menggunakan Page Builder Blok Banner.</p>
                        </div>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="checkbox" name="show_in_navbar" x-model="pageData.show_in_navbar" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-700">Tampilkan link di Navbar Atas</span>
                            </label>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Munculkan di Footer</label>
                                <select name="footer_column" x-model="pageData.footer_column" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-2.5 border bg-white">
                                    <option value="">- Jangan Tampilkan di Footer -</option>
                                    <option value="Layanan Pelanggan">Kolom: Layanan Pelanggan</option>
                                    <option value="Kebijakan">Kolom: Kebijakan & Legal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-6">
                    <h4 class="text-lg font-bold text-slate-900 mb-4 pb-2 border-b">2. Teks Konten Utama (Opsional)</h4>
                    <p class="text-xs text-slate-500 mb-3">Jika Anda ingin mengetik bebas bergaya Microsoft Word, gunakan kotak ini. Teks ini akan muncul sebelum Blok Khusus.</p>
                    <textarea name="content" id="pageContent" rows="10" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white"></textarea>
                </div>

                <!-- PAGE BUILDER BLOCKS -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between border-b pb-4 mb-6">
                        <div>
                            <h4 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                3. Page Builder (Blok Khusus)
                            </h4>
                            <p class="text-xs text-slate-500 mt-1">Tambahkan blok desain pre-made khusus untuk merakit halaman E-Commerce yang mewah.</p>
                        </div>
                        <div class="relative" x-data="{ menuOpen: false }">
                            <button type="button" @click="menuOpen = !menuOpen" @click.away="menuOpen = false" class="inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Blok Desain
                            </button>
                            <div x-show="menuOpen" x-transition class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-10">
                                <button type="button" @click="addBlock('faq'); menuOpen = false" class="w-full text-left px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-3">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Blok Akordeon (FAQ)
                                </button>
                                <button type="button" @click="addBlock('grid_info'); menuOpen = false" class="w-full text-left px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-3">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    Blok Grid 3 Kolom
                                </button>
                                <button type="button" @click="addBlock('contact_cards'); menuOpen = false" class="w-full text-left px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-3">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Blok Kartu Kontak
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Empty State -->
                        <div x-show="blocks.length === 0" class="text-center py-10 bg-slate-50 border border-dashed border-slate-300 rounded-xl">
                            <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <p class="text-slate-500 font-medium">Belum ada blok yang ditambahkan.</p>
                            <p class="text-xs text-slate-400 mt-1">Klik tombol "Tambah Blok Desain" di atas.</p>
                        </div>

                        <!-- Render Blocks Loop -->
                        <template x-for="(block, index) in blocks" :key="block.id">
                            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm relative group">
                                <!-- Block Header -->
                                <div class="bg-slate-100 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded bg-white border border-slate-200 flex items-center justify-center font-bold text-slate-500 text-xs shadow-sm" x-text="index + 1"></div>
                                        <h5 class="font-bold text-slate-800 uppercase tracking-wide text-sm" x-text="getBlockName(block.type)"></h5>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-50 group-hover:opacity-100 transition-opacity">
                                        <button type="button" @click="moveBlockUp(index)" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded" title="Pindah ke Atas">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        </button>
                                        <button type="button" @click="moveBlockDown(index)" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded" title="Pindah ke Bawah">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div class="w-px h-4 bg-slate-300 mx-1"></div>
                                        <button type="button" @click="removeBlock(index)" class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded" title="Hapus Blok">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Block Body: FAQ -->
                                <div x-show="block.type === 'faq'" class="p-5 bg-white space-y-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <p class="text-sm font-medium text-slate-600">Daftar Pertanyaan & Jawaban</p>
                                        <button type="button" @click="addFaqItem(index)" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100">+ Tambah Baris Q&A</button>
                                    </div>
                                    <template x-for="(item, i) in block.data.items" :key="i">
                                        <div class="flex gap-4 items-start p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                            <div class="flex-1 space-y-3">
                                                <input type="text" x-model="item.q" placeholder="Pertanyaan (Misal: Apakah ada garansi?)" class="w-full rounded border-slate-300 text-sm font-semibold p-2">
                                                <textarea x-model="item.a" placeholder="Jawaban (Misal: Ya, produk kami dilindungi garansi 1 tahun.)" rows="2" class="w-full rounded border-slate-300 text-sm p-2 text-slate-600"></textarea>
                                            </div>
                                            <button type="button" @click="removeFaqItem(index, i)" class="text-red-500 hover:text-red-700 p-2 mt-1 bg-white rounded shadow-sm border border-slate-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <!-- Block Body: Grid Info -->
                                <div x-show="block.type === 'grid_info'" class="p-5 bg-white space-y-4">
                                    <div class="mb-4">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Grid (Opsional)</label>
                                        <input type="text" x-model="block.data.title" placeholder="Contoh: Mengapa Memilih Kami?" class="w-full rounded border-slate-300 text-sm p-2 mb-4">
                                    </div>
                                    <div class="flex items-center justify-between mb-4">
                                        <p class="text-sm font-medium text-slate-600">Item Grid (Otomatis 3-kolom di web)</p>
                                        <button type="button" @click="addGridItem(index)" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100">+ Tambah Info Card</button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <template x-for="(item, i) in block.data.items" :key="i">
                                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg relative">
                                                <button type="button" @click="removeGridItem(index, i)" class="absolute top-2 right-2 text-red-400 hover:text-red-600 bg-white p-1 rounded-full shadow-sm">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                                <div class="space-y-3 pt-2">
                                                    <div class="hidden"></div>
                                                    <input type="text" x-model="item.title" placeholder="Judul (Misal: Pengiriman Cepat)" class="w-full rounded border-slate-300 text-sm font-bold p-2">
                                                    <textarea x-model="item.desc" placeholder="Deskripsi singkat..." rows="2" class="w-full rounded border-slate-300 text-sm p-2"></textarea>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Block Body: Contact Cards -->
                                <div x-show="block.type === 'contact_cards'" class="p-5 bg-white space-y-4">
                                    <p class="text-sm text-slate-500 mb-4">Isi data kontak yang ingin ditampilkan sebagai Kartu Kontak mewah.</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Perusahaan</label>
                                            <input type="email" x-model="block.data.email" placeholder="cs@tokoonline.com" class="w-full rounded border-slate-300 text-sm p-2">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / Telepon</label>
                                            <input type="text" x-model="block.data.phone" placeholder="+62 812-3456-7890" class="w-full rounded border-slate-300 text-sm p-2">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Kantor / Toko</label>
                                            <textarea x-model="block.data.address" placeholder="Jl. Sudirman No. 123, Jakarta Pusat..." rows="2" class="w-full rounded border-slate-300 text-sm p-2"></textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Google Maps Embed URL (Opsional)</label>
                                            <input type="text" x-model="block.data.map_url" placeholder="https://www.google.com/maps/embed?pb=..." class="w-full rounded border-slate-300 text-sm p-2 text-slate-500 font-mono text-xs">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>
                </div>

            </form>
        </div>
        <div class="flex items-center justify-end p-4 md:p-6 border-t border-slate-200 bg-white gap-3">
            <button type="button" @click="closeModal()" class="px-5 py-2.5 text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 font-medium text-sm transition-colors">Batal</button>
            <button type="submit" form="formAddPage" class="px-6 py-2.5 bg-blue-700 text-white rounded-xl hover:bg-blue-800 font-bold text-sm shadow-md transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan & Terbitkan
            </button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.7/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#pageContent',
        height: 400,
        menubar: false,
        plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic textcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image link | removeformat',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 16px; }',
        images_upload_handler: function (blobInfo, success, failure) {
            success('data:' + blobInfo.blob().type + ';base64,' + blobInfo.base64());
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('pageBuilder', () => ({
            isOpen: false,
            modalTitle: 'Tambah Halaman Baru',
            formAction: '{{ route("admin.pages.store") }}',
            methodPut: '',
            
            pageData: {
                title: '',
                template: 'default',
                show_in_navbar: false,
                footer_column: ''
            },
            
            blocks: [],

            init() {
                // Watch for template changes for auto-fill on new pages
                this.$watch('pageData.template', (val) => {
                    if (this.isNewPage && this.blocks.length === 0) { // Only auto-fill if it's a new page and empty
                        this.applyTemplateDummy(val);
                    }
                });
            },

            initModal(page = null) {
                this.isOpen = true;
                if (page) {
                    this.isNewPage = false;
                    this.modalTitle = 'Edit Halaman: ' + page.title;
                    this.formAction = '/admin/pages/' + page.id + '/update';
                    this.methodPut = ''; // Because updatePage uses POST in web.php based on the route Route::post('/pages/{id}/update')
                    
                    this.pageData.title = page.title;
                    this.pageData.template = page.template || 'default';
                    this.pageData.show_in_navbar = page.show_in_navbar;
                    this.pageData.footer_column = page.footer_column || '';
                    
                    if(tinymce.get('pageContent')) {
                        tinymce.get('pageContent').setContent(page.content || '');
                    }
                    
                    this.blocks = Array.isArray(page.blocks) ? page.blocks : [];
                } else {
                    this.isNewPage = true;
                    this.modalTitle = 'Tambah Halaman Baru';
                    this.formAction = '{{ route("admin.pages.store") }}';
                    this.methodPut = '';
                    this.pageData = { title: '', template: 'default', show_in_navbar: false, footer_column: '' };
                    this.blocks = [];
                    if(tinymce.get('pageContent')) tinymce.get('pageContent').setContent('');
                }
                document.getElementById('bannerImage').value = '';
            },
            
            applyTemplateDummy(template) {
                if (template === 'tentang_kami') {
                    if(tinymce.get('pageContent')) {
                        tinymce.get('pageContent').setContent('<h2>Sejarah Perusahaan Kami</h2><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>');
                    }
                    this.blocks = [{
                        id: 'block_' + Date.now(),
                        type: 'grid_info',
                        data: {
                            title: 'Nilai-Nilai Perusahaan',
                            items: [
                                { title: 'Fokus Pelanggan', desc: 'Kami selalu mengutamakan kepuasan pelanggan dalam setiap layanan.' },
                                { title: 'Kualitas Premium', desc: 'Produk yang kami sediakan telah melewati quality control ketat.' },
                                { title: 'Integritas', desc: 'Kejujuran dan transparansi adalah fondasi bisnis kami.' }
                            ]
                        }
                    }];
                } else if (template === 'faq') {
                    if(tinymce.get('pageContent')) {
                        tinymce.get('pageContent').setContent('<p>Berikut adalah jawaban atas pertanyaan yang sering diajukan oleh pelanggan kami. Jika Anda tidak menemukan jawaban yang dicari, silakan hubungi tim support kami.</p>');
                    }
                    this.blocks = [{
                        id: 'block_' + Date.now(),
                        type: 'faq',
                        data: {
                            items: [
                                { q: 'Bagaimana cara melacak pesanan saya?', a: 'Anda dapat melacak pesanan melalui menu "Lacak Pesanan" dengan memasukkan nomor resi yang telah kami kirimkan via email.' },
                                { q: 'Apakah ada garansi pengembalian produk?', a: 'Ya, kami menyediakan garansi pengembalian 14 hari sejak barang diterima jika barang rusak atau tidak sesuai deskripsi.' },
                                { q: 'Metode pembayaran apa saja yang diterima?', a: 'Kami menerima pembayaran melalui transfer bank (BCA, Mandiri, BNI, BRI), kartu kredit, dan dompet digital (GoPay, OVO, Dana).' }
                            ]
                        }
                    }];
                } else if (template === 'hubungi_kami') {
                    if(tinymce.get('pageContent')) {
                        tinymce.get('pageContent').setContent('<p>Tim layanan pelanggan kami siap membantu Anda menjawab berbagai pertanyaan seputar produk, pesanan, dan keluhan. Jangan ragu untuk menghubungi kami melalui saluran di bawah ini.</p>');
                    }
                    this.blocks = [{
                        id: 'block_' + Date.now(),
                        type: 'contact_cards',
                        data: {
                            email: 'support@tokoonline.com',
                            phone: '+62 811-1234-5678',
                            address: 'Gedung TokoOnline Lt. 5, Jl. Jend. Sudirman No. 99, Jakarta Pusat 10220',
                            map_url: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126920.24056238639!2d106.74412217736413!3d-6.229746473138803!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945e34b9d%3A0x5371bf0fdad786a2!2sJakarta%2C%20Daerah%20Khusus%20Ibukota%20Jakarta!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid'
                        }
                    }];
                } else if (template === 'kebijakan_privasi') {
                    if(tinymce.get('pageContent')) {
                        tinymce.get('pageContent').setContent('<h2>1. Pengumpulan Informasi</h2><p>Kami mengumpulkan informasi dari Anda ketika Anda mendaftar di situs kami, masuk ke akun Anda, melakukan pembelian, masuk dalam kontes, dan/atau ketika Anda keluar. Informasi yang dikumpulkan mencakup nama Anda, alamat email, nomor telepon, dan/atau kartu kredit.</p><h2>2. Penggunaan Informasi</h2><p>Segala informasi yang kami kumpulkan dari Anda dapat digunakan untuk:</p><ul><li>Personalisasi pengalaman Anda dan tanggapan sesuai kebutuhan individual Anda</li><li>Menyediakan konten iklan yang disesuaikan</li><li>Meningkatkan situs web kami</li><li>Meningkatkan layanan pelanggan dan kebutuhan dukungan Anda</li><li>Menghubungi Anda melalui email</li></ul><h2>3. Privasi E-Commerce</h2><p>Kami adalah pemilik tunggal dari informasi yang dikumpulkan pada situs ini. Informasi identitas pribadi Anda tidak akan dijual, dipertukarkan, ditransfer, atau diberikan kepada perusahaan lain dengan alasan apa pun, tanpa persetujuan Anda, selain dari hanya semata-mata untuk memenuhi permohonan dan/atau transaksi, misalnya untuk pengiriman pesanan.</p>');
                    }
                    this.blocks = [];
                } else {
                    if(tinymce.get('pageContent')) tinymce.get('pageContent').setContent('');
                    this.blocks = [];
                }
            },
            
            closeModal() {
                this.isOpen = false;
            },

            getBlockName(type) {
                const names = {
                    'faq': 'Akordeon Tanya Jawab (FAQ)',
                    'grid_info': 'Grid Info 3 Kolom',
                    'contact_cards': 'Kartu Informasi Kontak & Peta'
                };
                return names[type] || 'Blok Kustom';
            },

            addBlock(type) {
                let defaultData = {};
                if (type === 'faq') {
                    defaultData = { items: [{q: '', a: ''}] };
                } else if (type === 'grid_info') {
                    defaultData = { title: '', items: [{title: '', desc: ''}] };
                } else if (type === 'contact_cards') {
                    defaultData = { email: '', phone: '', address: '', map_url: '' };
                }

                this.blocks.push({
                    id: 'block_' + Date.now(),
                    type: type,
                    data: defaultData
                });
            },

            removeBlock(index) {
                window.dispatchEvent(new CustomEvent('open-confirm', {
                    detail: {
                        message: 'Hapus blok konten ini?',
                        action: () => this.blocks.splice(index, 1)
                    }
                }));
            },
            
            moveBlockUp(index) {
                if (index > 0) {
                    const temp = this.blocks[index];
                    this.blocks[index] = this.blocks[index - 1];
                    this.blocks[index - 1] = temp;
                }
            },

            moveBlockDown(index) {
                if (index < this.blocks.length - 1) {
                    const temp = this.blocks[index];
                    this.blocks[index] = this.blocks[index + 1];
                    this.blocks[index + 1] = temp;
                }
            },

            // FAQ helpers
            addFaqItem(blockIndex) {
                this.blocks[blockIndex].data.items.push({q: '', a: ''});
            },
            removeFaqItem(blockIndex, itemIndex) {
                this.blocks[blockIndex].data.items.splice(itemIndex, 1);
            },

            // Grid helpers
            addGridItem(blockIndex) {
                this.blocks[blockIndex].data.items.push({title: '', desc: ''});
            },
            removeGridItem(blockIndex, itemIndex) {
                this.blocks[blockIndex].data.items.splice(itemIndex, 1);
            }
        }));
    });

    // Wrapper function to trigger Alpine modal from outside
    function openAddModal() {
        window.dispatchEvent(new CustomEvent('open-page-modal'));
    }
    
    function editPage(page) {
        window.dispatchEvent(new CustomEvent('open-page-modal', { detail: page }));
    }
</script>
@endsection
