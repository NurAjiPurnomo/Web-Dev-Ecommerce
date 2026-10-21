@extends('admin.layout')

@section('title', 'Produk')

@section('content')
<div x-data="productManager()" class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Katalog Produk</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola daftar produk, stok gudang, variasi harga, dan status penjualan.</p>
        </div>
        <div>
            <button 
                type="button" 
                @click="showAddModal = true"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Produk</span>
            </button>
        </div>
    </div>

    <!-- Ringkasan Produk (KPI Cards) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Produk -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Total Produk</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($totalProductsCount, 0, ',', '.') }}</div>
            <p class="text-xs text-slate-500 mt-0.5">Item terdaftar di katalog</p>
        </div>

        <!-- Produk Aktif -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Produk Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($activeProductsCount, 0, ',', '.') }}</div>
            <p class="text-xs text-emerald-600 font-medium mt-0.5">Siap dijual ke pembeli</p>
        </div>

        <!-- Total Stok Fisik -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Total Stok Fisik</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v1a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($totalStockCount, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">pcs</span></div>
            <p class="text-xs text-slate-500 mt-0.5">Persediaan di gudang</p>
        </div>

        <!-- Total Terjual -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Total Terjual</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($totalSoldCount, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">pcs</span></div>
            <p class="text-xs text-amber-600 font-medium mt-0.5">Total volume terjual</p>
        </div>
    </div>

    <!-- Main Content Container: Status Tabs, Filter & Product List -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <!-- Status Tabs (Shopee / Tokopedia Seller Centre) -->
        <div class="border-b border-slate-200 px-4 sm:px-6 pt-2 bg-white">
            <nav class="flex items-center gap-6 overflow-x-auto no-scrollbar -mb-px text-xs sm:text-sm font-medium">
                @php
                    $tabs = [
                        'all'          => ['label' => 'Semua', 'count' => $tabCounts['all']],
                        'active'       => ['label' => 'Aktif', 'count' => $tabCounts['active']],
                        'low_stock'    => ['label' => 'Stok Menipis', 'count' => $tabCounts['low_stock'], 'highlight' => 'amber'],
                        'out_of_stock' => ['label' => 'Habis', 'count' => $tabCounts['out_of_stock'], 'highlight' => 'rose'],
                        'inactive'     => ['label' => 'Nonaktif', 'count' => $tabCounts['inactive']],
                    ];
                @endphp

                @foreach($tabs as $tabKey => $tabData)
                    @php
                        $isActive = ($status === $tabKey);
                        $queryParams = request()->query();
                        $queryParams['status'] = $tabKey;
                        $tabUrl = route('admin.products', $queryParams);
                    @endphp
                    <a 
                        href="{{ $tabUrl }}" 
                        class="py-3 border-b-2 whitespace-nowrap transition-colors flex items-center gap-1.5 {{ $isActive ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}"
                    >
                        <span>{{ $tabData['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[11px] {{ $isActive ? 'bg-blue-100 text-blue-700 font-semibold' : 'bg-slate-100 text-slate-600' }} {{ isset($tabData['highlight']) && $tabData['count'] > 0 ? ($tabData['highlight'] === 'amber' ? 'bg-amber-100 text-amber-800 font-semibold' : 'bg-rose-100 text-rose-800 font-semibold') : '' }}">
                            {{ $tabData['count'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Search & Filter Toolbar -->
        <div class="p-4 sm:p-5 bg-slate-50/50 border-b border-slate-200">
            <form action="{{ route('admin.products') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                @if($status !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 max-w-2xl">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search }}" 
                            placeholder="Cari nama produk atau SKU (misal: #SKU-001)..." 
                            class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all shadow-xs"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Category Dropdown Filter -->
                    <div class="sm:w-48">
                        <select 
                            name="category" 
                            onchange="this.form.submit()" 
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all shadow-xs cursor-pointer"
                        >
                            <option value="all" {{ $category === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-medium transition-colors shadow-xs cursor-pointer shrink-0"
                    >
                        Cari
                    </button>
                </div>

                <!-- Reset Filter Link -->
                @if($search || $category !== 'all' || $status !== 'all')
                    <a 
                        href="{{ route('admin.products') }}" 
                        class="text-xs text-slate-500 hover:text-slate-800 flex items-center gap-1 font-medium self-center md:self-auto"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reset Filter</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Desktop Product Table View (md+) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-medium text-xs">
                    <tr>
                        <th class="px-4 py-3 font-medium">Informasi Produk</th>
                        <th class="px-3 py-3 font-medium">Kategori</th>
                        <th class="px-3 py-3 font-medium">Harga</th>
                        <th class="px-3 py-3 font-medium">Stok</th>
                        <th class="px-3 py-3 font-medium">Terjual</th>
                        <th class="px-3 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($products as $p)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <!-- Info Produk -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $p->image }}" alt="{{ $p->name }}" class="w-11 h-11 rounded-lg object-cover border border-slate-200 bg-slate-100 shrink-0">
                                    <div class="min-w-0 max-w-[220px] lg:max-w-[260px] xl:max-w-xs">
                                        <h4 class="font-medium text-slate-900 truncate text-xs sm:text-sm" title="{{ $p->name }}">{{ $p->name }}</h4>
                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-0.5 flex-wrap">
                                            <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-sans font-medium">#SKU-00{{ $p->id }}</span>
                                            @if(!empty($p->variants) && is_array($p->variants) && count($p->variants) > 0)
                                                <span>•</span>
                                                <span class="text-slate-500">{{ count($p->variants) }} Varian</span>
                                            @endif
                                            <span>•</span>
                                            <span class="text-slate-400" title="{{ $p->updated_at ? $p->updated_at->format('d M Y, H:i') : '' }}">
                                                Update: {{ $p->updated_at ? $p->updated_at->diffForHumans() : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Kategori -->
                            <td class="px-3 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-normal bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $p->category }}
                                </span>
                            </td>

                            <!-- Harga -->
                            <td class="px-3 py-3 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 text-xs sm:text-sm">{{ $p->formatted_price }}</div>
                                @if($p->original_price && $p->original_price > $p->price)
                                    <div class="text-[11px] text-slate-400 line-through">{{ $p->formatted_original_price }}</div>
                                @endif
                            </td>

                            <!-- Stok -->
                            <td class="px-3 py-3 whitespace-nowrap">
                                @if($p->stock <= 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        Habis (0)
                                    </span>
                                @elseif($p->stock <= 5)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200" title="Stok tersisa sedikit">
                                        Menipis ({{ $p->stock }})
                                    </span>
                                @else
                                    <span class="font-medium text-slate-800">{{ $p->stock }} pcs</span>
                                @endif
                            </td>

                            <!-- Terjual -->
                            <td class="px-3 py-3 whitespace-nowrap">
                                <span class="font-medium text-slate-700">{{ $p->sold }} pcs</span>
                            </td>

                            <!-- Status (Klik untuk Cepat Nonaktifkan / Aktifkan Produk) -->
                            <td class="px-3 py-3 whitespace-nowrap">
                                <form action="{{ route('admin.products.toggleStatus', $p->id) }}" method="POST" class="inline">
                                    @csrf
                                    @if($p->status === 'aktif' && $p->stock > 0)
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer group" title="Status Aktif di toko. Klik untuk Nonaktifkan (Arsipkan)">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Aktif</span>
                                        </button>
                                    @elseif($p->status === 'nonaktif')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition-colors cursor-pointer group" title="Status Nonaktif (diarsipkan). Klik untuk Aktifkan kembali ke toko">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Nonaktif</span>
                                        </button>
                                    @else
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors cursor-pointer group" title="Stok Habis. Klik untuk Nonaktifkan">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Habis</span>
                                        </button>
                                    @endif
                                </form>
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a 
                                        href="{{ route('product.detail', ['id' => $p->id, 'slug' => $p->slug]) }}" 
                                        target="_blank" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors border border-slate-200"
                                        title="Lihat Produk di Toko"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span>Lihat</span>
                                    </a>

                                    <button 
                                        type="button" 
                                        @click="openEditById({{ $p->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors border border-blue-200 cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit</span>
                                    </button>

                                    <form action="{{ route('admin.products.delete', $p->id) }}" method="POST" @submit.prevent="$dispatch('open-confirm', { message: 'Hapus produk ini? Produk yang dihapus akan ditarik dari katalog secara permanen.', action: () => $el.submit() })">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors border border-rose-200 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="text-xs font-medium text-slate-700">Tidak ada produk ditemukan</p>
                                    <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau ganti status filter produk.</p>
                                    @if($search || $category !== 'all' || $status !== 'all')
                                        <a href="{{ route('admin.products') }}" class="inline-block mt-2 text-xs text-blue-600 hover:text-blue-700 font-medium">Reset Semua Filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Product Cards View (< md) -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($products as $p)
                <div class="p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <img src="{{ $p->image }}" alt="{{ $p->name }}" class="w-16 h-16 rounded-lg object-cover border border-slate-200 bg-slate-100 shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-normal bg-slate-100 text-slate-700 border border-slate-200">{{ $p->category }}</span>
                                <form action="{{ route('admin.products.toggleStatus', $p->id) }}" method="POST" class="inline">
                                    @csrf
                                    @if($p->status === 'aktif' && $p->stock > 0)
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-pointer" title="Klik untuk nonaktifkan"><span class="w-1 h-1 rounded-full bg-emerald-500"></span>Aktif</button>
                                    @elseif($p->status === 'nonaktif')
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200 cursor-pointer" title="Klik untuk aktifkan"><span class="w-1 h-1 rounded-full bg-slate-400"></span>Nonaktif</button>
                                    @else
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200 cursor-pointer" title="Klik untuk nonaktifkan"><span class="w-1 h-1 rounded-full bg-rose-500"></span>Habis</button>
                                    @endif
                                </form>
                            </div>
                            <h4 class="font-medium text-slate-900 text-xs mt-1 truncate">{{ $p->name }}</h4>
                            <p class="text-[11px] text-slate-400 font-mono">#SKU-00{{ $p->id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 bg-slate-50 p-2.5 rounded-lg text-xs border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-500 block">Harga</span>
                            <span class="font-semibold text-slate-900 text-xs">{{ $p->formatted_price }}</span>
                        </div>
                        <div class="text-center">
                            <span class="text-[10px] text-slate-500 block">Stok</span>
                            <span class="font-medium {{ $p->stock <= 0 ? 'text-rose-600' : ($p->stock <= 5 ? 'text-amber-600' : 'text-slate-800') }} text-xs">{{ $p->stock }} pcs</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-500 block">Terjual</span>
                            <span class="font-medium text-slate-700 text-xs">{{ $p->sold }} pcs</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                        <span>Terakhir Update:</span>
                        <span class="text-slate-700 font-medium">{{ $p->updated_at ? $p->updated_at->format('d M Y, H:i') : '-' }}</span>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <a 
                            href="{{ route('product.detail', ['id' => $p->id, 'slug' => $p->slug]) }}" 
                            target="_blank" 
                            class="px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg text-center"
                        >
                            Lihat
                        </a>
                        <button 
                            type="button" 
                            @click="openEditById({{ $p->id }})"
                            class="flex-1 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 py-1.5 rounded-lg border border-blue-200 transition-colors text-center cursor-pointer flex items-center justify-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit</span>
                        </button>
                        <form action="{{ route('admin.products.delete', $p->id) }}" method="POST" class="flex-1" @submit.prevent="$dispatch('open-confirm', { message: 'Hapus produk ini? Produk yang dihapus akan ditarik dari katalog secara permanen.', action: () => $el.submit() })">
                            @csrf
                            <button type="submit" class="w-full text-xs font-medium text-rose-600 hover:text-rose-700 bg-rose-50 py-1.5 rounded-lg border border-rose-200 transition-colors text-center cursor-pointer flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    Tidak ada produk ditemukan.
                </div>
            @endforelse
        </div>

        <!-- Pagination Footer (Matching User Design with Blue Active Indicator) -->
        @if ($products->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-slate-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan <span class="text-slate-900 font-semibold">{{ $products->firstItem() }}</span> - <span class="text-slate-900 font-semibold">{{ $products->lastItem() }}</span> dari <span class="text-slate-900 font-semibold">{{ $products->total() }}</span> produk
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    {{-- Previous Page Link --}}
                    @if ($products->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center text-slate-300 cursor-not-allowed select-none">
                            <svg class="w-4 h-4 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors select-none" title="Sebelumnya">
                            <svg class="w-4 h-4 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $products->currentPage();
                        $lastPage = $products->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $products->url(1) }}" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors select-none">1</a>
                        @if($start > 2)
                            <span class="w-6 h-7 sm:h-8 flex items-center justify-center text-xs sm:text-sm text-slate-400 select-none">...</span>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        @if ($i == $currentPage)
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-[3px] bg-blue-600 text-white flex items-center justify-center text-xs sm:text-sm font-medium shadow-xs select-none">
                                {{ $i }}
                            </span>
                        @else
                            <a href="{{ $products->url($i) }}" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors select-none">
                                {{ $i }}
                            </a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="w-6 h-7 sm:h-8 flex items-center justify-center text-xs sm:text-sm text-slate-400 select-none">...</span>
                        @endif
                        <a href="{{ $products->url($lastPage) }}" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors select-none">{{ $lastPage }}</a>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors select-none" title="Selanjutnya">
                            <svg class="w-4 h-4 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="w-8 h-8 flex items-center justify-center text-slate-300 cursor-not-allowed select-none">
                            <svg class="w-4 h-4 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                </div>
            </div>
        @endif
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
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Stok Total Gudang <span class="text-red-500">*</span></label>
                        <input type="number" name="stock" min="0" value="20" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" required placeholder="150000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Asli (Coret / Diskon)</label>
                        <input type="number" name="original_price" placeholder="250000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-900 mb-1">Berat Produk (Gram) <span class="text-red-500">*</span></label>
                        <input type="number" name="weight" min="1" value="1000" required placeholder="1000" class="w-full bg-blue-50/70 border border-blue-300 rounded-xl px-3 py-2 text-xs font-bold text-blue-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                        <span class="text-[10px] text-slate-500 block mt-0.5">Misal: 500 gr, 1000 gr = 1 kg.</span>
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

                <!-- SECTION: SPESIFIKASI PRODUK (DETAIL INFORMASI PRODUK) -->
                <div class="border border-slate-200 bg-slate-50/70 p-4 rounded-2xl space-y-3.5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Spesifikasi Produk (Detail Informasi Tampil di Halaman Produk)
                            </h4>
                            <p class="text-[11px] text-slate-500">Isi spesifikasi produk asli Anda (Merek, Bahan, Model, Garansi, dll.).</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Merek / Brand</label>
                            <input type="text" name="specs[Merek]" placeholder="Misal: No Brand / Forti" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Bahan Pakaian / Material</label>
                            <input type="text" name="specs[Bahan Pakaian]" placeholder="Misal: Katun Fleece 280gsm / Combed 30s" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Model / Potongan</label>
                            <input type="text" name="specs[Model / Potongan]" placeholder="Misal: Regular Fit / Slim Fit" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Asal Produk</label>
                            <input type="text" name="specs[Asal Produk]" value="Indonesia" placeholder="Indonesia" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kondisi</label>
                            <input type="text" name="specs[Kondisi]" value="Baru (100% Original)" placeholder="Baru (100% Original)" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Garansi</label>
                            <input type="text" name="specs[Garansi]" value="Garansi Resmi Toko" placeholder="Misal: Garansi Toko 1 Bulan" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                    </div>

                    <!-- Custom Specs Rows (Dinamis: e.g. Tipe Lengan, Gaya, No. SNI, dll.) -->
                    <div class="pt-2 border-t border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-700">Spesifikasi Kustom Tambahan:</span>
                            <button type="button" @click="addCustomSpecRow('add')" class="text-[10px] bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold px-2.5 py-1 rounded-lg transition-colors cursor-pointer flex items-center gap-1">
                                + Tambah Kolom Kustom
                            </button>
                        </div>
                        <template x-for="(spec, sIdx) in addCustomSpecs" :key="sIdx">
                            <div class="flex items-center gap-2">
                                <input type="text" x-model="spec.key" placeholder="Nama Label (mis: Gaya / Tipe Lengan)" class="w-1/2 bg-white border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                                <input type="text" :name="'specs[' + spec.key + ']'" x-model="spec.val" placeholder="Nilai (mis: Casual / Panjang)" class="w-1/2 bg-white border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                                <button type="button" @click="addCustomSpecs.splice(sIdx, 1)" class="text-red-500 hover:text-red-700 p-1 cursor-pointer" title="Hapus">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Deskripsi Produk</label>
                        <button type="button" @click="insertKeunggulan('add')" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 cursor-pointer bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Sisipkan Template Keunggulan</span>
                        </button>
                    </div>
                    <textarea name="description" rows="3" placeholder="Deskripsi lengkap produk..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"></textarea>
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
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Stok Total <span class="text-red-500">*</span></label>
                        <input type="number" name="stock" x-model="editModalData.stock" min="0" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" x-model="editModalData.price" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Asli (Coret)</label>
                        <input type="number" name="original_price" x-model="editModalData.original_price" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-900 mb-1">Berat Produk (Gram) <span class="text-red-500">*</span></label>
                        <input type="number" name="weight" x-model="editModalData.weight" min="1" required placeholder="1000" class="w-full bg-blue-50/70 border border-blue-300 rounded-xl px-3 py-2 text-xs font-bold text-blue-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                        <span class="text-[10px] text-slate-500 block mt-0.5">Misal: 500 gr, 1000 gr = 1 kg.</span>
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

                <!-- SECTION: SPESIFIKASI PRODUK (DETAIL INFORMASI PRODUK) - EDIT MODAL -->
                <div class="border border-slate-200 bg-slate-50/70 p-4 rounded-2xl space-y-3.5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Spesifikasi Produk (Detail Informasi Tampil di Halaman Produk)
                            </h4>
                            <p class="text-[11px] text-slate-500">Edit spesifikasi produk (Merek, Bahan, Model, Garansi, dll.).</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Merek / Brand</label>
                            <input type="text" name="specs[Merek]" x-model="editModalData.specs['Merek']" placeholder="Misal: No Brand / Forti" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Bahan Pakaian / Material</label>
                            <input type="text" name="specs[Bahan Pakaian]" x-model="editModalData.specs['Bahan Pakaian']" placeholder="Misal: Katun Fleece 280gsm / Combed 30s" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Model / Potongan</label>
                            <input type="text" name="specs[Model / Potongan]" x-model="editModalData.specs['Model / Potongan']" placeholder="Misal: Regular Fit / Slim Fit" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Asal Produk</label>
                            <input type="text" name="specs[Asal Produk]" x-model="editModalData.specs['Asal Produk']" placeholder="Indonesia" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kondisi</label>
                            <input type="text" name="specs[Kondisi]" x-model="editModalData.specs['Kondisi']" placeholder="Baru (100% Original)" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Garansi</label>
                            <input type="text" name="specs[Garansi]" x-model="editModalData.specs['Garansi']" placeholder="Misal: Garansi Toko 1 Bulan" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                        </div>
                    </div>

                    <!-- Custom Specs Rows (Edit) -->
                    <div class="pt-2 border-t border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-700">Spesifikasi Kustom Tambahan:</span>
                            <button type="button" @click="addCustomSpecRow('edit')" class="text-[10px] bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold px-2.5 py-1 rounded-lg transition-colors cursor-pointer flex items-center gap-1">
                                + Tambah Kolom Kustom
                            </button>
                        </div>
                        <template x-for="(spec, sIdx) in editCustomSpecs" :key="sIdx">
                            <div class="flex items-center gap-2">
                                <input type="text" x-model="spec.key" placeholder="Nama Label (mis: Gaya / Tipe Lengan)" class="w-1/2 bg-white border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                                <input type="text" :name="'specs[' + spec.key + ']'" x-model="spec.val" placeholder="Nilai (mis: Casual / Panjang)" class="w-1/2 bg-white border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-900 focus:border-blue-700 focus:outline-none">
                                <button type="button" @click="editCustomSpecs.splice(sIdx, 1)" class="text-red-500 hover:text-red-700 p-1 cursor-pointer" title="Hapus">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Deskripsi Produk</label>
                        <button type="button" @click="insertKeunggulan('edit')" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 cursor-pointer bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Sisipkan Template Keunggulan</span>
                        </button>
                    </div>
                    <textarea name="description" x-model="editModalData.description" rows="3" placeholder="Deskripsi lengkap..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 flex gap-2 justify-end">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold shadow-md cursor-pointer">Update Produk</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script id="products-data" type="application/json">
{!! json_encode($products->items()) !!}
</script>

<script>
function productManager() {
    let productsList = [];
    try {
        const el = document.getElementById('products-data');
        if (el) {
            const parsed = JSON.parse(el.textContent);
            productsList = Array.isArray(parsed) ? parsed : (parsed.data || []);
        }
    } catch(e) {
        console.error('Failed to parse products-data JSON:', e);
    }

    return {
        productsList: productsList,
        showAddModal: false,
        showEditModal: false,
        addCustomSpecs: [],
        editCustomSpecs: [],
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
            weight: 1000,
            status: 'aktif',
            image: '',
            size_guide_image: '',
            images: '',
            description: '',
            specs: {
                'Merek': '',
                'Bahan Pakaian': '',
                'Model / Potongan': '',
                'Asal Produk': 'Indonesia',
                'Kondisi': 'Baru (100% Original)',
                'Garansi': 'Garansi Resmi Toko'
            }
        },
        addCustomSpecRow(type) {
            if (type === 'add') {
                this.addCustomSpecs.push({ key: '', val: '' });
            } else {
                this.editCustomSpecs.push({ key: '', val: '' });
            }
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
        openEditById(id) {
            const p = this.productsList.find(item => item.id == id);
            if (p) {
                this.openEdit(p);
            } else {
                console.warn('Produk tidak ditemukan untuk ID:', id);
            }
        },
        openEdit(p) {
            const standardKeys = ['Merek', 'Bahan Pakaian', 'Model / Potongan', 'Asal Produk', 'Kondisi', 'Garansi'];
            let pSpecs = {};
            if (typeof p.specs === 'string') {
                try { pSpecs = JSON.parse(p.specs); } catch(e) {}
            } else if (typeof p.specs === 'object' && p.specs !== null) {
                pSpecs = p.specs;
            }
            
            let specsInit = {
                'Merek': pSpecs['Merek'] || pSpecs['Brand'] || '',
                'Bahan Pakaian': pSpecs['Bahan Pakaian'] || pSpecs['Bahan'] || '',
                'Model / Potongan': pSpecs['Model / Potongan'] || pSpecs['Model'] || '',
                'Asal Produk': pSpecs['Asal Produk'] || 'Indonesia',
                'Kondisi': pSpecs['Kondisi'] || 'Baru (100% Original)',
                'Garansi': pSpecs['Garansi'] || 'Garansi Resmi Toko'
            };

            this.editCustomSpecs = [];
            for (let k in pSpecs) {
                if (!standardKeys.includes(k) && !['Brand', 'Bahan', 'Model', 'Stok', 'Berat Produk', 'Dikirim Dari', 'Kategori'].includes(k)) {
                    this.editCustomSpecs.push({ key: k, val: pSpecs[k] });
                }
            }

            this.editModalData = {
                id: p.id,
                name: p.name || '',
                category: p.category || 'Pakaian',
                stock: p.stock !== undefined ? p.stock : 0,
                price: p.price !== undefined ? p.price : 0,
                original_price: p.original_price || '',
                weight: p.weight !== undefined ? p.weight : 1000,
                status: p.status || 'aktif',
                image: p.image || '',
                size_guide_image: p.size_guide_image || '',
                images: Array.isArray(p.images) ? p.images.join('\n') : (p.images || ''),
                description: p.description || '',
                specs: specsInit
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
        },
        insertKeunggulan(type) {
            const tpl = "\n\nKeunggulan & Detail Produk:\n- Material original grade premium dengan jaminan daya tahan maksimal.\n- Sangat nyaman digunakan untuk pemakaian harian maupun perjalanan.\n- Desain modern, stylish, dan eksklusif sesuai tren terbaru.\n- 100% Original Brand dengan Garansi Resmi Toko.\n\n* Catatan: Harap lakukan video unboxing saat paket diterima untuk verifikasi garansi.";
            if (type === 'edit') {
                this.editModalData.description = (this.editModalData.description || '').trim() + tpl;
            } else {
                const textarea = document.querySelector('form[action*="/admin/products"] textarea[name="description"]');
                if (textarea) {
                    textarea.value = (textarea.value || '').trim() + tpl;
                }
            }
        }
    }
}
</script>
@endsection
