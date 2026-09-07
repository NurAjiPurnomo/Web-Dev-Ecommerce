@extends('admin.layout')

@section('title', 'Manajemen Produk')

@section('content')
<div x-data="productManager()" class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Manajemen Katalog Produk</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data barang, harga, varian warna/ukuran, stok, dan riwayat update katalog.</p>
        </div>
        <button 
            type="button" 
            @click="showAddModal = true"
            class="bg-blue-700 hover:bg-blue-800 text-white font-semibold text-xs sm:text-sm px-5 py-3 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 cursor-pointer shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Produk Baru</span>
        </button>
    </div>

    <!-- 4 QUICK KPI SUMMARY CARDS FOR PRODUCTS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        @php
            $totalProductsCount = count($products);
            $totalStockCount = $products->sum('stock');
            $totalSoldCount = $products->sum('sold');
            $activeProductsCount = $products->where('status', 'aktif')->where('stock', '>', 0)->count();
        @endphp

        <!-- SKU TOTAL -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total SKU</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-slate-900">{{ number_format($totalProductsCount, 0, ',', '.') }} Produk</h3>
            <p class="text-[11px] text-blue-700 font-bold">Terdaftar di Sistem</p>
        </div>

        <!-- STOK AKTIF -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Produk Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-slate-900">{{ number_format($activeProductsCount, 0, ',', '.') }} SKU</h3>
            <p class="text-[11px] text-emerald-700 font-bold">Siap Dijual</p>
        </div>

        <!-- TOTAL STOK FISIK -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Stok Fisik</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v1a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-slate-900">{{ number_format($totalStockCount, 0, ',', '.') }} pcs</h3>
            <p class="text-[11px] text-purple-700 font-bold">Stok di Gudang</p>
        </div>

        <!-- TOTAL TERJUAL -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Terjual</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-slate-900">{{ number_format($totalSoldCount, 0, ',', '.') }} pcs</h3>
            <p class="text-[11px] text-amber-700 font-bold">Volume Terjual</p>
        </div>
    </div>

    <!-- Search & Category Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <form action="{{ route('admin.products') }}" method="GET" class="w-full md:w-80 relative">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Cari nama produk / SKU..." 
                class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-9 pr-4 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none transition-all"
            >
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </form>

        <!-- Category Pill Filters (Horizontally Scrollable) -->
        <div class="flex items-center gap-1.5 w-full md:w-auto overflow-x-auto pb-1 md:pb-0 text-xs font-bold no-scrollbar">
            <a href="{{ route('admin.products', ['category' => 'all']) }}" class="px-3.5 py-2 rounded-xl border whitespace-nowrap transition-colors {{ request('category', 'all') === 'all' ? 'bg-blue-700 text-white border-blue-700 shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">Semua Kategori</a>
            <a href="{{ route('admin.products', ['category' => 'Pakaian']) }}" class="px-3.5 py-2 rounded-xl border whitespace-nowrap transition-colors {{ request('category') === 'Pakaian' ? 'bg-blue-700 text-white border-blue-700 shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">Pakaian</a>
            <a href="{{ route('admin.products', ['category' => 'Sepatu']) }}" class="px-3.5 py-2 rounded-xl border whitespace-nowrap transition-colors {{ request('category') === 'Sepatu' ? 'bg-blue-700 text-white border-blue-700 shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">Sepatu</a>
            <a href="{{ route('admin.products', ['category' => 'Gadget']) }}" class="px-3.5 py-2 rounded-xl border whitespace-nowrap transition-colors {{ request('category') === 'Gadget' ? 'bg-blue-700 text-white border-blue-700 shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">Gadget</a>
            <a href="{{ route('admin.products', ['category' => 'Aksesoris']) }}" class="px-3.5 py-2 rounded-xl border whitespace-nowrap transition-colors {{ request('category') === 'Aksesoris' ? 'bg-blue-700 text-white border-blue-700 shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">Aksesoris</a>
            <a href="{{ route('admin.products', ['category' => 'Rumah Tangga']) }}" class="px-3.5 py-2 rounded-xl border whitespace-nowrap transition-colors {{ request('category') === 'Rumah Tangga' ? 'bg-blue-700 text-white border-blue-700 shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">Rumah Tangga</a>
        </div>
    </div>

    <!-- DESKTOP PRODUCT TABLE VIEW (hidden on mobile, visible on md+) -->
    <div class="hidden md:block bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3.5">Detail Produk</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Harga Jual</th>
                        <th class="px-5 py-3.5">Stok</th>
                        <th class="px-5 py-3.5">Terjual</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Terakhir Update</th>
                        <th class="px-5 py-3.5 text-right">Kelola</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($products as $p)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $p->image }}" alt="{{ $p->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0">
                                    <div class="max-w-xs">
                                        <h4 class="font-semibold text-slate-900 line-clamp-1 text-sm">{{ $p->name }}</h4>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 font-mono mt-0.5">
                                            <span class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-bold">#SKU-00{{ $p->id }}</span>
                                            <span>•</span>
                                            <span class="text-slate-500 font-sans">Rilis: {{ $p->created_at ? $p->created_at->format('d/m/Y') : '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="bg-blue-50 text-blue-700 text-[11px] font-semibold px-2.5 py-1 rounded-lg border border-blue-200">{{ $p->category }}</span>
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900 text-sm">
                                {{ $p->formatted_price }}
                                @if($p->original_price)
                                    <div class="text-[11px] text-slate-400 line-through font-normal">{{ $p->formatted_original_price }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold {{ $p->stock > 0 ? 'text-slate-900' : 'text-red-600' }}">{{ $p->stock }} pcs</span>
                            </td>
                            <td class="px-5 py-4 text-slate-700 font-semibold">{{ $p->sold }} pcs</td>
                            <td class="px-5 py-4">
                                @if($p->stock > 0 && $p->status === 'aktif')
                                    <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-emerald-200">● Aktif</span>
                                @else
                                    <span class="bg-red-50 text-red-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-red-200">● Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                <div class="font-bold text-slate-800">{{ $p->updated_at ? $p->updated_at->format('d M Y, H:i') : ($p->created_at ? $p->created_at->format('d M Y, H:i') : '-') }}</div>
                                <div class="text-[10px] text-blue-700 font-semibold mt-0.5">({{ $p->updated_at ? $p->updated_at->diffForHumans() : ($p->created_at ? $p->created_at->diffForHumans() : '-') }})</div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        type="button" 
                                        @click='openEdit(@json($p))'
                                        class="text-xs font-bold text-blue-700 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg border border-blue-200 transition-colors cursor-pointer"
                                    >
                                        Edit
                                    </button>

                                    <form action="{{ route('admin.products.delete', $p->id) }}" method="POST" @submit.prevent="$dispatch('open-confirm', { message: 'Hapus produk ini? Produk yang dihapus akan ditarik dari katalog secara permanen.', action: () => $el.submit() })">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg border border-red-200 transition-colors cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">Tidak ada produk ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MOBILE PRODUCT CARDS VIEW (visible on mobile < md) -->
    <div class="md:hidden space-y-4">
        @forelse($products as $p)
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs space-y-3.5">
                <!-- Top Card Info -->
                <div class="flex items-start gap-3">
                    <img src="{{ $p->image }}" alt="{{ $p->name }}" class="w-16 h-16 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <span class="bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-0.5 rounded border border-blue-200">{{ $p->category }}</span>
                            @if($p->stock > 0 && $p->status === 'aktif')
                                <span class="bg-emerald-50 text-emerald-700 text-[10px] font-semibold px-2 py-0.5 rounded-full border border-emerald-200">● Aktif</span>
                            @else
                                <span class="bg-red-50 text-red-700 text-[10px] font-semibold px-2 py-0.5 rounded-full border border-red-200">● Nonaktif</span>
                            @endif
                        </div>
                        <h4 class="font-semibold text-slate-900 text-sm mt-1 truncate">{{ $p->name }}</h4>
                        <p class="text-[11px] text-slate-400 font-mono">SKU: #SKU-00{{ $p->id }}</p>
                    </div>
                </div>

                <!-- Price & Stock Grid -->
                <div class="grid grid-cols-3 gap-2 bg-slate-50 p-2.5 rounded-xl text-xs border border-slate-100">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold block uppercase">Harga Jual</span>
                        <span class="font-semibold text-slate-900">{{ $p->formatted_price }}</span>
                    </div>
                    <div class="text-center">
                        <span class="text-[10px] text-slate-400 font-bold block uppercase">Stok Gudang</span>
                        <span class="font-semibold {{ $p->stock > 0 ? 'text-slate-900' : 'text-red-600' }}">{{ $p->stock }} pcs</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 font-bold block uppercase">Terjual</span>
                        <span class="font-semibold text-slate-700">{{ $p->sold }} pcs</span>
                    </div>
                </div>

                <!-- Update Timestamp -->
                <div class="flex items-center justify-between text-[11px] text-slate-500 border-t border-slate-100 pt-2.5">
                    <span>Terakhir Update:</span>
                    <span class="font-bold text-blue-700">{{ $p->updated_at ? $p->updated_at->format('d M Y, H:i') : '-' }}</span>
                </div>

                <!-- Card Actions -->
                <div class="flex items-center gap-2 pt-1">
                    <button 
                        type="button" 
                        @click='openEdit(@json($p))'
                        class="flex-1 text-xs font-semibold text-blue-700 hover:text-blue-800 bg-blue-50 py-2 rounded-xl border border-blue-200 transition-colors text-center cursor-pointer flex items-center justify-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit Produk</span>
                    </button>

                    <form action="{{ route('admin.products.delete', $p->id) }}" method="POST" class="flex-1" @submit.prevent="$dispatch('open-confirm', { message: 'Hapus produk ini? Produk yang dihapus akan ditarik dari katalog secara permanen.', action: () => $el.submit() })">
                        @csrf
                        <button type="submit" class="w-full text-xs font-semibold text-red-600 hover:text-red-700 bg-red-50 py-2 rounded-xl border border-red-200 transition-colors text-center cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Hapus Produk</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-400 text-xs">
                Tidak ada produk ditemukan.
            </div>
        @endforelse
    </div>

    <!-- MODAL TAMBAH PRODUK BARU (RESPONSIVE MODAL) -->
    <div 
        x-show="showAddModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <div @click.outside="showAddModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl p-5 sm:p-6 space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base sm:text-lg">Tambah Produk Baru</h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
            </div>

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Sepatu Sneakers Casual White" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                            <option value="Pakaian">Pakaian</option>
                            <option value="Sepatu">Sepatu</option>
                            <option value="Aksesoris">Aksesoris</option>
                            <option value="Gadget">Gadget</option>
                            <option value="Rumah Tangga">Rumah Tangga</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Stok Total Gudang <span class="text-red-500">*</span></label>
                        <input type="number" name="stock" min="0" value="20" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" required placeholder="150000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Asli (Coret / Diskon)</label>
                        <input type="number" name="original_price" placeholder="250000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Gambar Utama Produk</label>
                        <input type="file" name="image" accept="image/*" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gambar Panduan Ukuran (Opsional)</label>
                        <input type="file" name="size_guide_image" accept="image/*" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <!-- TABEL VARIAN PRODUK DINAMIS -->
                <div class="border border-blue-200 bg-blue-50/50 p-4 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-semibold text-blue-900 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-5 5h5"/></svg>
                                Rincian Varian Produk (Warna, Stok, Ukuran &amp; Link Gambar)
                            </h4>
                            <p class="text-[11px] text-slate-500">Atur setiap varian warna beserta jumlah stoknya, opsi ukuran, dan link gambarnya.</p>
                        </div>
                        <button type="button" @click="addVariantRow('add')" class="px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-xs font-bold transition-all shadow-2xs flex items-center gap-1 cursor-pointer shrink-0">
                            + Tambah Varian
                        </button>
                    </div>

                    <!-- Dynamic Variant Rows -->
                    <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                        <template x-for="(varItem, index) in addVariants" :key="index">
                            <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-2xs space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-1.5">
                                    <span class="text-[11px] font-semibold text-blue-700" x-text="'Warna/Varian #' + (index + 1)"></span>
                                    <button type="button" @click="removeVariantRow('add', index)" class="text-red-500 hover:text-red-700 font-bold text-xs px-2 py-0.5 bg-red-50 hover:bg-red-100 rounded transition-colors cursor-pointer" title="Hapus Warna">
                                        ✕ Hapus
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Nama Warna</label>
                                        <input type="text" :name="'variants[' + index + '][color]'" x-model="varItem.color" placeholder="Misal: Kuning" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs font-bold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Gambar Khusus Warna</label>
                                        <input type="file" :name="'variant_images[' + index + ']'" accept="image/*" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-1.5 py-1 text-[10px] font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none file:mr-1 file:py-0.5 file:px-1 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <input type="hidden" :name="'variants[' + index + '][existing_image]'" :value="varItem.image">
                                    </div>
                                </div>
                                
                                <!-- Nested Sizes -->
                                <div class="bg-slate-50 rounded-lg p-2 border border-slate-200 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Daftar Ukuran & Harga</label>
                                        <button type="button" @click="varItem.sizes.push({name: '', price: 0, stock: 10})" class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-[10px] font-bold hover:bg-blue-200 transition-colors cursor-pointer">
                                            + Tambah Ukuran
                                        </button>
                                    </div>
                                    
                                    <template x-for="(sz, szIndex) in varItem.sizes" :key="szIndex">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1">
                                                <input type="text" :name="'variants[' + index + '][sizes][' + szIndex + '][name]'" x-model="sz.name" placeholder="Ukuran (S/40)" class="w-full bg-white border border-slate-200 rounded text-xs px-2 py-1 focus:border-blue-700 focus:outline-none font-bold">
                                            </div>
                                            <div class="flex-1">
                                                <input type="number" min="0" :name="'variants[' + index + '][sizes][' + szIndex + '][price]'" x-model.number="sz.price" placeholder="Harga" class="w-full bg-white border border-slate-200 rounded text-xs px-2 py-1 focus:border-blue-700 focus:outline-none font-bold">
                                            </div>
                                            <div class="w-20">
                                                <input type="number" min="0" :name="'variants[' + index + '][sizes][' + szIndex + '][stock]'" x-model.number="sz.stock" placeholder="Stok" class="w-full bg-white border border-slate-200 rounded text-xs px-2 py-1 focus:border-blue-700 focus:outline-none font-bold">
                                            </div>
                                            <button type="button" @click="varItem.sizes.splice(szIndex, 1)" class="text-red-500 hover:text-red-700 p-1 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Produk</label>
                    <textarea name="description" rows="2" placeholder="Deskripsi lengkap produk..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 flex gap-2 justify-end">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold shadow-md cursor-pointer">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PRODUK (RESPONSIVE MODAL) -->
    <div 
        x-show="showEditModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <div @click.outside="showEditModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl p-5 sm:p-6 space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base sm:text-lg">Edit Produk</h3>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
            </div>

            <form :action="'/admin/products/' + editModalData.id + '/update'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editModalData.name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" x-model="editModalData.category" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                            <option value="Pakaian">Pakaian</option>
                            <option value="Sepatu">Sepatu</option>
                            <option value="Aksesoris">Aksesoris</option>
                            <option value="Gadget">Gadget</option>
                            <option value="Rumah Tangga">Rumah Tangga</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Stok Total <span class="text-red-500">*</span></label>
                        <input type="number" name="stock" x-model="editModalData.stock" min="0" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" x-model="editModalData.price" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Asli (Coret)</label>
                        <input type="number" name="original_price" x-model="editModalData.original_price" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Produk</label>
                        <select name="status" x-model="editModalData.status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Gambar Utama Baru (Kosongkan jika tidak diubah)</label>
                        <input type="file" name="image" accept="image/*" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <template x-if="editModalData.image">
                            <div class="mt-2 flex items-center gap-2">
                                <img :src="editModalData.image" class="h-10 w-10 rounded object-cover border border-slate-200">
                                <span class="text-[10px] text-slate-500">Gambar Saat Ini</span>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Upload Gambar Panduan Ukuran Baru (Opsional)</label>
                    <input type="file" name="size_guide_image" accept="image/*" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <template x-if="editModalData.size_guide_image">
                        <p class="text-[10px] text-slate-500 mt-1">
                            Panduan saat ini: <a :href="editModalData.size_guide_image" target="_blank" class="text-blue-600 hover:underline">Lihat Gambar</a>
                        </p>
                    </template>
                </div>

                <!-- TABEL VARIAN PRODUK DINAMIS (EDIT MODAL) -->
                <div class="border border-blue-200 bg-blue-50/50 p-4 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-semibold text-blue-900 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-5 5h5"/></svg>
                                Rincian Varian Produk (Warna, Stok, Ukuran &amp; Link Gambar)
                            </h4>
                            <p class="text-[11px] text-slate-500">Atur setiap varian warna beserta jumlah stoknya, opsi ukuran, dan link gambarnya.</p>
                        </div>
                        <button type="button" @click="addVariantRow('edit')" class="px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-xs font-bold transition-all shadow-2xs flex items-center gap-1 cursor-pointer shrink-0">
                            + Tambah Varian
                        </button>
                    </div>

                    <!-- Dynamic Variant Rows -->
                    <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                        <template x-for="(varItem, index) in editVariants" :key="index">
                            <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-2xs space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-1.5">
                                    <span class="text-[11px] font-semibold text-blue-700" x-text="'Warna/Varian #' + (index + 1)"></span>
                                    <button type="button" @click="removeVariantRow('edit', index)" class="text-red-500 hover:text-red-700 font-bold text-xs px-2 py-0.5 bg-red-50 hover:bg-red-100 rounded transition-colors cursor-pointer" title="Hapus Warna">
                                        ✕ Hapus
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Nama Warna</label>
                                        <input type="text" :name="'variants[' + index + '][color]'" x-model="varItem.color" placeholder="Misal: Kuning" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs font-bold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Gambar Khusus Warna</label>
                                        <input type="file" :name="'variant_images[' + index + ']'" accept="image/*" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-1.5 py-1 text-[10px] font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none file:mr-1 file:py-0.5 file:px-1 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <input type="hidden" :name="'variants[' + index + '][existing_image]'" :value="varItem.image">
                                        <template x-if="varItem.image">
                                            <div class="mt-1 flex items-center gap-1.5">
                                                <img :src="varItem.image" class="h-6 w-6 rounded object-cover border border-slate-200">
                                                <span class="text-[9px] text-slate-500">Gambar Saat Ini</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                
                                <!-- Nested Sizes -->
                                <div class="bg-slate-50 rounded-lg p-2 border border-slate-200 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Daftar Ukuran & Harga</label>
                                        <button type="button" @click="varItem.sizes.push({name: '', price: 0, stock: 10})" class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-[10px] font-bold hover:bg-blue-200 transition-colors cursor-pointer">
                                            + Tambah Ukuran
                                        </button>
                                    </div>
                                    
                                    <template x-for="(sz, szIndex) in varItem.sizes" :key="szIndex">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1">
                                                <input type="text" :name="'variants[' + index + '][sizes][' + szIndex + '][name]'" x-model="sz.name" placeholder="Ukuran (S/40)" class="w-full bg-white border border-slate-200 rounded text-xs px-2 py-1 focus:border-blue-700 focus:outline-none font-bold">
                                            </div>
                                            <div class="flex-1">
                                                <input type="number" min="0" :name="'variants[' + index + '][sizes][' + szIndex + '][price]'" x-model.number="sz.price" placeholder="Harga" class="w-full bg-white border border-slate-200 rounded text-xs px-2 py-1 focus:border-blue-700 focus:outline-none font-bold">
                                            </div>
                                            <div class="w-20">
                                                <input type="number" min="0" :name="'variants[' + index + '][sizes][' + szIndex + '][stock]'" x-model.number="sz.stock" placeholder="Stok" class="w-full bg-white border border-slate-200 rounded text-xs px-2 py-1 focus:border-blue-700 focus:outline-none font-bold">
                                            </div>
                                            <button type="button" @click="varItem.sizes.splice(szIndex, 1)" class="text-red-500 hover:text-red-700 p-1 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Produk</label>
                    <textarea name="description" x-model="editModalData.description" rows="2" placeholder="Deskripsi lengkap..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 flex gap-2 justify-end">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold shadow-md cursor-pointer">Update Produk</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function productManager() {
    return {
        showAddModal: false,
        showEditModal: false,
        addVariants: [
            { color: 'Standar', image: '', sizes: [{ name: '', price: 0, stock: 10 }] }
        ],
        editVariants: [],
        editModalData: {
            id: '',
            name: '',
            category: 'Pakaian',
            stock: 0,
            price: 0,
            original_price: '',
            status: 'aktif',
            image: '',
            size_guide_image: '',
            images: '',
            description: ''
        },
        addVariantRow(type) {
            if (type === 'add') {
                this.addVariants.push({ color: '', image: '', sizes: [{ name: '', price: 0, stock: 10 }] });
            } else {
                this.editVariants.push({ color: '', image: '', sizes: [{ name: '', price: 0, stock: 10 }] });
            }
        },
        removeVariantRow(type, index) {
            if (type === 'add') {
                if (this.addVariants.length > 1) {
                    this.addVariants.splice(index, 1);
                }
            } else {
                this.editVariants.splice(index, 1);
            }
        },
        openEdit(p) {
            this.editModalData = {
                id: p.id,
                name: p.name || '',
                category: p.category || 'Pakaian',
                stock: p.stock !== undefined ? p.stock : 0,
                price: p.price !== undefined ? p.price : 0,
                original_price: p.original_price || '',
                status: p.status || 'aktif',
                image: p.image || '',
                size_guide_image: p.size_guide_image || '',
                images: Array.isArray(p.images) ? p.images.join('\n') : (p.images || ''),
                description: p.description || ''
            };

            // Format editVariants
            if (Array.isArray(p.variants) && p.variants.length > 0) {
                let grouped = {};
                p.variants.forEach(v => {
                    let col = v.color || 'Standar';
                    if (!grouped[col]) grouped[col] = { color: col, image: v.image || '', sizes: [] };
                    grouped[col].sizes.push({
                        name: v.size || (Array.isArray(v.sizes) ? v.sizes[0] : v.sizes) || '',
                        price: v.price || p.price || 0,
                        stock: v.stock !== undefined ? v.stock : 10
                    });
                });
                this.editVariants = Object.values(grouped);
            } else if (Array.isArray(p.colors) && p.colors.length > 0) {
                this.editVariants = p.colors.map(c => ({
                    color: typeof c === 'object' ? c.name : c,
                    image: typeof c === 'object' ? c.image : '',
                    sizes: [{
                        name: Array.isArray(p.sizes) ? p.sizes.join(', ') : (p.sizes || ''),
                        price: p.price || 0,
                        stock: p.stock || 10
                    }]
                }));
            } else {
                this.editVariants = [
                    { color: 'Standar', image: p.image || '', sizes: [{ name: 'S', price: p.price || 0, stock: p.stock || 10 }] }
                ];
            }

            this.showEditModal = true;
        }
    }
}
</script>
@endsection
