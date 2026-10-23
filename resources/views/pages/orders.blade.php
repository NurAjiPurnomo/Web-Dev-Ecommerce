@extends('layouts.app')

@section('content')


<div x-data="orderManagerApp()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">

    <!-- ==================== MODE 1: LIST VIEW (FULL WIDTH) ==================== -->
    <div x-show="viewMode === 'list'" x-cloak class="space-y-3.5 sm:space-y-4">

        <!-- Page Header & Breadcrumb -->
        <div class="space-y-1">
            <nav class="flex text-xs text-slate-500 gap-1.5 items-center">
                <a href="{{ route('home') }}" class="hover:text-blue-700">Beranda</a>
                <span>/</span>
                <a href="{{ route('profile') }}" class="hover:text-blue-700">Profil Saya</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Manajer Pesanan</span>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 pt-0.5">
                <div>
                    <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Manajer Pesanan</span>
                    </h1>
                    <p class="text-xs text-slate-500">Kelola, lacak status, dan lihat rincian transaksi belanja Anda</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-medium">Total Pesanan:</span>
                    <span class="bg-blue-50 text-blue-700 font-bold text-xs px-3 py-1 rounded-full border border-blue-200" x-text="filteredOrders.length + ' Transaksi'"></span>
                </div>
            </div>
        </div>

        <!-- Filter Status Tabs (Shopee Style with Icons) -->
        <div class="bg-white border border-slate-200 rounded-xl p-1.5 shadow-2xs">
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-1 text-xs">
                <button 
                    type="button" 
                    @click="activeFilter = 'semua'; selectedOrderIndex = 0" 
                    :class="activeFilter === 'semua' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                    class="w-full px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span>Semua</span>
                    <span x-show="orders.length > 0" class="ml-0.5 bg-blue-100 text-blue-800 text-[10px] font-extrabold px-1.5 py-0.5 rounded-full" x-text="orders.length"></span>
                </button>
                <button 
                    type="button" 
                    @click="activeFilter = 'belum_bayar'; selectedOrderIndex = 0" 
                    :class="activeFilter === 'belum_bayar' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                    class="w-full px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span>Belum Bayar</span>
                    <span x-show="countBelumBayar > 0" class="ml-0.5 bg-red-100 text-red-700 text-[10px] font-extrabold px-1.5 py-0.5 rounded-full" x-text="countBelumBayar"></span>
                </button>
                <button 
                    type="button" 
                    @click="activeFilter = 'diproses'; selectedOrderIndex = 0" 
                    :class="activeFilter === 'diproses' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                    class="w-full px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Diproses</span>
                    <span x-show="countDiproses > 0" class="ml-0.5 bg-blue-100 text-blue-800 text-[10px] font-extrabold px-1.5 py-0.5 rounded-full" x-text="countDiproses"></span>
                </button>
                <button 
                    type="button" 
                    @click="activeFilter = 'dikirim'; selectedOrderIndex = 0" 
                    :class="activeFilter === 'dikirim' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                    class="w-full px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    <span>Dikirim</span>
                    <span x-show="countDikirim > 0" class="ml-0.5 bg-blue-100 text-blue-800 text-[10px] font-extrabold px-1.5 py-0.5 rounded-full" x-text="countDikirim"></span>
                </button>
                <button 
                    type="button" 
                    @click="activeFilter = 'selesai'; selectedOrderIndex = 0" 
                    :class="activeFilter === 'selesai' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                    class="w-full px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Selesai</span>
                    <span x-show="countSelesai > 0" class="ml-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-1.5 py-0.5 rounded-full" x-text="countSelesai"></span>
                </button>
                <button 
                    type="button" 
                    @click="activeFilter = 'retur'; selectedOrderIndex = 0" 
                    :class="activeFilter === 'retur' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                    class="w-full px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Pengembalian</span>
                    <span x-show="countRetur > 0" class="ml-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold px-1.5 py-0.5 rounded-full" x-text="countRetur"></span>
                </button>
                <button 
                    type="button" 
                    @click="activeFilter = 'batal'; selectedOrderIndex = 0" 
                    :class="activeFilter === 'batal' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                    class="w-full px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Dibatalkan</span>
                    <span x-show="countBatal > 0" class="ml-0.5 bg-slate-200 text-slate-700 text-[10px] font-extrabold px-1.5 py-0.5 rounded-full" x-text="countBatal"></span>
                </button>
            </div>
        </div>

        <!-- Search Input -->
        <div class="relative">
            <input 
                type="text" 
                x-model="searchQuery" 
                placeholder="Cari No. Invoice / Nama Barang..." 
                class="w-full bg-white border border-slate-200 rounded-lg pl-9 pr-4 py-2 text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 shadow-2xs"
            >
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <!-- Full Width List of Order Cards -->
        <div class="space-y-3 sm:space-y-3.5">
            <template x-for="(order, idx) in filteredOrders" :key="idx + '_' + (order.id || order.raw_id || idx)">
                <div 
                    class="bg-white border border-slate-200 rounded-xl p-3.5 sm:p-4 shadow-2xs space-y-3 group"
                >
                    <!-- Card Header: ID & Status Badge -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-slate-900 text-xs sm:text-sm group-hover:text-blue-700 transition-colors" x-text="order.id"></span>
                            <span class="text-xs text-slate-300">•</span>
                            <span class="text-xs text-slate-500" x-text="order.date"></span>
                        </div>
                        
                        <!-- Status Badges -->
                        <div class="flex items-center gap-2">
                            <template x-if="order.status === 'belum_bayar' || order.status === 'belum_dibayar'">
                                <span class="bg-amber-100 text-amber-800 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-amber-200">
                                    Belum Bayar
                                </span>
                            </template>
                            <template x-if="order.status === 'diproses'">
                                <span class="bg-yellow-100 text-yellow-800 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-yellow-200">
                                    Diproses
                                </span>
                            </template>
                            <template x-if="order.status === 'dikirim'">
                                <span class="bg-blue-100 text-blue-800 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-blue-200 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                    <span>Dikirim</span>
                                </span>
                            </template>
                            <template x-if="order.status === 'selesai'">
                                <span class="bg-emerald-100 text-emerald-800 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-emerald-200">
                                    Selesai
                                </span>
                            </template>
                            <template x-if="order.status === 'retur' || order.status === 'komplain'">
                                <span class="bg-amber-100 text-amber-900 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                    <span>Retur Diajukan</span>
                                </span>
                            </template>
                            <template x-if="order.status === 'retur_proses'">
                                <span class="bg-sky-100 text-sky-900 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-sky-300 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-600 animate-pulse"></span>
                                    <span>Retur Disetujui (Kirim Barang)</span>
                                </span>
                            </template>
                            <template x-if="order.status === 'retur_disetujui'">
                                <span class="bg-emerald-100 text-emerald-900 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-emerald-300 flex items-center gap-1">
                                    <span>✓ Retur Selesai</span>
                                </span>
                            </template>
                            <template x-if="order.status === 'retur_ditolak'">
                                <span class="bg-rose-100 text-rose-900 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-rose-300 flex items-center gap-1">
                                    <span>✕ Retur Ditolak</span>
                                </span>
                            </template>
                            <template x-if="order.status === 'pengajuan_batal'">
                                <span class="bg-amber-100 text-amber-900 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                    <span>Menunggu Konfirmasi Batal</span>
                                </span>
                            </template>
                            <template x-if="order.status === 'batal'">
                                <span class="bg-red-100 text-red-800 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-red-200">
                                    Dibatalkan
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Products List inside Card -->
                    <div class="divide-y divide-slate-100">
                        <template x-for="(item, itemIdx) in (order.items || [])" :key="itemIdx">
                            <div class="flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <!-- Rigid Thumbnail Box -->
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0 flex-shrink-0" style="width: 64px; height: 64px; min-width: 64px; min-height: 64px;">
                                        <img 
                                            :src="item.image || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600'" 
                                            :alt="item.name"
                                            class="w-full h-full object-cover block"
                                            style="width: 64px; height: 64px; min-width: 64px; min-height: 64px; object-fit: cover;"
                                            loading="lazy"
                                        >
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 line-clamp-1 leading-snug" x-text="item.name"></h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="item.variant ? 'Variasi: ' + item.variant : ''"></p>
                                        <div class="flex items-center gap-1.5 mt-0.5 text-xs">
                                            <span class="text-slate-500 font-medium" x-text="'x' + item.qty"></span>
                                            <span class="text-slate-300">•</span>
                                            <span class="text-slate-600 font-medium" x-text="formatRupiah(item.price)"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 pl-2">
                                    <span class="text-xs sm:text-sm font-extrabold text-slate-900 block" x-text="formatRupiah(item.price * item.qty)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Card Footer: Total & Action -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-2.5 border-t border-slate-100">
                        <div>
                            <span class="text-[11px] text-slate-500 font-medium block">Total Tagihan Pesanan:</span>
                            <span class="font-black text-blue-700 text-sm sm:text-base" x-text="formatRupiah(order.total_amount)"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="order.status === 'belum_bayar' || order.status === 'belum_dibayar' || order.status === 'diproses' || order.status === 'dikemas'">
                                <button 
                                    type="button" 
                                    @click="openCancelModal(order.raw_id || order.id)"
                                    class="text-xs font-semibold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-lg transition-all cursor-pointer"
                                >
                                    Batalkan
                                </button>
                            </template>

                            <button 
                                type="button" 
                                @click="selectOrder(idx)" 
                                class="w-full sm:w-auto text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 px-3.5 py-1.5 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs hover:shadow-sm"
                            >
                                <span>Lihat Rincian Pesanan</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="filteredOrders.length === 0">
                <div class="bg-white border border-slate-200 rounded-xl p-8 sm:p-10 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Tidak ada pesanan ditemukan</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">Coba pilih tab status filter di atas atau cari dengan kata kunci nama produk / no invoice yang lain.</p>
                </div>
            </template>
        </div>

    </div>

    <!-- ==================== MODE 2: DETAIL VIEW (FULL PAGE) ==================== -->
    <div x-show="viewMode === 'detail'" x-cloak class="space-y-4">

        <!-- Back Button & Breadcrumb Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <button 
                type="button" 
                @click="goBackToList()" 
                class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3.5 py-1.5 rounded-lg transition-all shadow-2xs cursor-pointer w-fit"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Manajer Pesanan</span>
            </button>

            <nav class="flex text-xs text-slate-500 gap-1.5 items-center">
                <a href="{{ route('home') }}" class="hover:text-blue-700">Beranda</a>
                <span>/</span>
                <span>Manajer Pesanan</span>
                <span>/</span>
                <span class="text-slate-900 font-bold" x-text="activeOrder ? activeOrder.id : 'Detail Pesanan'"></span>
            </nav>
        </div>

        <!-- Full Width Order Details Container -->
        <template x-if="activeOrder">
            <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-6 shadow-2xs space-y-4 sm:space-y-5">

                <!-- 1. Header: Order ID, Timestamp & Action Menu -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-900" x-text="activeOrder.id"></h2>
                            <span class="text-xs text-slate-400">|</span>
                            <span class="text-xs text-slate-400 font-medium" x-text="activeOrder.raw_id"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Waktu Transaksi: <span x-text="activeOrder.date"></span></p>
                    </div>

                    <!-- Action Menu (Three Dots) -->
                    <div class="relative" x-data="{ openMenu: false }">
                        <button 
                            type="button" 
                            @click="openMenu = !openMenu" 
                            @click.outside="openMenu = false"
                            class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>

                        <div 
                            x-show="openMenu" 
                            x-transition 
                            class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-50 text-xs space-y-0.5" 
                            style="display: none;"
                        >
                            <a href="#" @click.prevent="copyTrackingResi(activeOrder.courier.resi); openMenu = false" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Salin Nomor Resi</a>
                            <a href="#" @click.prevent="window.print()" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Cetak Halaman ini</a>
                            <a href="#" @click.prevent="alert('Pusat Bantuan Toko Online aktif 24 jam')" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Bantuan Pesanan</a>
                        </div>
                    </div>
                </div>

                <!-- 2. Visual Tracking: Horizontal Step-by-step Progress Bar -->
                <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 sm:p-5 space-y-3.5">
                    <div class="flex items-center justify-between text-xs sm:text-sm font-bold text-slate-800">
                        <span x-text="['retur', 'retur_proses', 'retur_disetujui', 'retur_ditolak'].includes(activeOrder.status) ? 'Alur Pengembalian Barang & Dana (Retur)' : 'Status Lacak Pengiriman'"></span>
                        <span :class="['retur', 'retur_proses', 'retur_disetujui'].includes(activeOrder.status) ? 'text-amber-700 font-extrabold' : 'text-blue-700'" x-text="activeOrder.status_label"></span>
                    </div>

                    <!-- Progress Steps FOR RETURN PROCESS (SHOPEE / TOKOPEDIA STYLE) -->
                    <template x-if="['retur', 'retur_proses', 'retur_disetujui', 'retur_ditolak'].includes(activeOrder.status)">
                        <div class="relative pt-1 pb-1">
                            <!-- Connecting line behind circles -->
                            <div class="absolute top-4 sm:top-4.5 left-[12.5%] right-[12.5%] h-0.5 bg-slate-200 -translate-y-1/2 z-0" aria-hidden="true">
                                <div class="h-full bg-blue-600 transition-all duration-300" 
                                     :style="'width: ' + ((activeOrder.return_tracking_data ? activeOrder.return_tracking_data.current_step : 1) <= 1 ? '0%' : ((activeOrder.return_tracking_data ? activeOrder.return_tracking_data.current_step : 1) === 2 ? '33.33%' : ((activeOrder.return_tracking_data ? activeOrder.return_tracking_data.current_step : 1) === 3 ? '66.66%' : '100%')))">
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-2 relative z-10 text-center">
                                <!-- Step 1: Komplain Diajukan -->
                                <div class="space-y-1.5">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-[1.5px] text-blue-700 border-blue-700 shadow-2xs">
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </div>
                                    <p class="text-[11px] sm:text-xs font-semibold leading-tight text-slate-900">Komplain<br>Diajukan</p>
                                </div>

                                <!-- Step 2: Retur Disetujui (Resi Aktif) -->
                                <div class="space-y-1.5">
                                    <div 
                                        :class="(activeOrder.return_tracking_data ? activeOrder.return_tracking_data.current_step >= 2 : (activeOrder.status !== 'retur')) ? 'text-blue-700 border-blue-700 shadow-2xs' : 'text-slate-400 border-slate-300'"
                                        class="w-8 h-8 sm:w-9 sm:h-9 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-[1.5px]"
                                    >
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-[11px] sm:text-xs font-semibold leading-tight" :class="(activeOrder.return_tracking_data ? activeOrder.return_tracking_data.current_step >= 2 : (activeOrder.status !== 'retur')) ? 'text-slate-900 font-bold' : 'text-slate-400'">Retur<br>Disetujui</p>
                                </div>

                                <!-- Step 3: Pengiriman ke Penjual -->
                                <div class="space-y-1.5">
                                    <div 
                                        :class="(activeOrder.return_tracking_data ? activeOrder.return_tracking_data.current_step >= 3 : false) ? 'text-blue-700 border-blue-700 shadow-2xs' : 'text-slate-400 border-slate-300'"
                                        class="w-8 h-8 sm:w-9 sm:h-9 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-[1.5px]"
                                    >
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                        </svg>
                                    </div>
                                    <p class="text-[11px] sm:text-xs font-semibold leading-tight" :class="(activeOrder.return_tracking_data ? activeOrder.return_tracking_data.current_step >= 3 : false) ? 'text-slate-900 font-bold' : 'text-slate-400'">Pengiriman<br>ke Penjual</p>
                                </div>

                                <!-- Step 4: Selesai & Refund -->
                                <div class="space-y-1.5">
                                    <div 
                                        :class="(activeOrder.return_tracking_data ? activeOrder.return_tracking_data.current_step >= 4 : (activeOrder.status === 'retur_disetujui')) ? 'text-emerald-600 border-emerald-600 shadow-2xs' : 'text-slate-400 border-slate-300'"
                                        class="w-8 h-8 sm:w-9 sm:h-9 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-[1.5px]"
                                    >
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-[11px] sm:text-xs font-semibold leading-tight" :class="(activeOrder.return_tracking_data ? activeOrder.return_tracking_data.current_step >= 4 : (activeOrder.status === 'retur_disetujui')) ? 'text-emerald-700 font-bold' : 'text-slate-400'">Selesai &<br>Refund</p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Progress Steps FOR REGULAR FORWARD DELIVERY -->
                    <template x-if="!['retur', 'retur_proses', 'retur_disetujui', 'retur_ditolak'].includes(activeOrder.status)">
                        <div class="relative pt-1 pb-1">
                            <!-- Connecting line behind circles -->
                            <div class="absolute top-4 sm:top-4.5 left-[12.5%] right-[12.5%] h-0.5 bg-slate-200 -translate-y-1/2 z-0" aria-hidden="true">
                                <div class="h-full bg-blue-600 transition-all duration-300" 
                                     :style="'width: ' + (activeOrder.step <= 1 ? '0%' : (activeOrder.step === 2 ? '33.33%' : (activeOrder.step === 3 ? '66.66%' : '100%')))">
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-2 relative z-10 text-center">
                                <!-- Step 1: Menunggu Konfirmasi -->
                                <div class="space-y-1.5">
                                    <div 
                                        :class="activeOrder.step >= 1 ? 'text-blue-700 border-blue-700 shadow-2xs' : 'text-slate-400 border-slate-300'"
                                        class="w-8 h-8 sm:w-9 sm:h-9 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-[1.5px]"
                                    >
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-[11px] sm:text-xs font-semibold leading-tight" :class="activeOrder.step >= 1 ? 'text-slate-900' : 'text-slate-400'">Menunggu<br>Konfirmasi</p>
                                </div>

                                <!-- Step 2: Pesanan Diproses -->
                                <div class="space-y-1.5">
                                    <div 
                                        :class="activeOrder.step >= 2 ? 'text-blue-700 border-blue-700 shadow-2xs' : 'text-slate-400 border-slate-300'"
                                        class="w-8 h-8 sm:w-9 sm:h-9 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-[1.5px]"
                                    >
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </div>
                                    <p class="text-[11px] sm:text-xs font-semibold leading-tight" :class="activeOrder.step >= 2 ? 'text-slate-900' : 'text-slate-400'">Pesanan<br>Diproses</p>
                                </div>

                                <!-- Step 3: Sedang Dikirim -->
                                <div class="space-y-1.5">
                                    <div 
                                        :class="activeOrder.step >= 3 ? 'text-blue-700 border-blue-700 shadow-2xs' : 'text-slate-400 border-slate-300'"
                                        class="w-8 h-8 sm:w-9 sm:h-9 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-[1.5px]"
                                    >
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                        </svg>
                                    </div>
                                    <p class="text-[11px] sm:text-xs font-semibold leading-tight" :class="activeOrder.step >= 3 ? 'text-slate-900' : 'text-slate-400'">Sedang<br>Dikirim</p>
                                </div>

                                <!-- Step 4: Sampai Tujuan -->
                                <div class="space-y-1.5">
                                    <div 
                                        :class="activeOrder.step >= 4 ? 'text-blue-700 border-blue-700 shadow-2xs' : 'text-slate-400 border-slate-300'"
                                        class="w-8 h-8 sm:w-9 sm:h-9 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-[1.5px]"
                                    >
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-[11px] sm:text-xs font-semibold leading-tight" :class="activeOrder.step >= 4 ? 'text-blue-700 font-bold' : 'text-slate-400'">Sampai<br>Tujuan</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- ========================================== -->
                <!-- A. KARTU TRACKING KHUSUS STATUS RETUR      -->
                <!-- ========================================== -->
                <template x-if="['retur', 'retur_proses', 'retur_disetujui', 'retur_ditolak'].includes(activeOrder.status)">
                    <div class="space-y-4">
                        <!-- Kartu Pelacakan Real-time Pengembalian Barang -->
                        <div class="border border-slate-200/80 rounded-2xl bg-white shadow-2xs overflow-hidden">
                            <!-- Header Info Pengiriman Retur -->
                            <div class="px-5 py-4 bg-slate-50/70 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-9 h-9 rounded-lg bg-amber-50 border border-amber-200/80 text-amber-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </div>
                                    <div class="space-y-0.5 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-bold text-slate-900" x-text="(activeOrder.return_tracking_data ? activeOrder.return_tracking_data.courier_name : (activeOrder.courier ? activeOrder.courier.name : 'J&T Express')) + ' (Pengembalian Barang)'"></span>
                                            <span class="text-[10px] font-semibold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-200">
                                                Bebas Ongkir (Cashless)
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 flex-wrap pt-0.5">
                                            <span class="text-xs text-slate-500 font-medium">No. Resi Retur:</span>
                                            <template x-if="activeOrder.return_data && activeOrder.return_data.return_tracking_number">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-mono font-bold text-slate-900 bg-white px-2.5 py-0.5 rounded border border-slate-200 select-all shadow-2xs" x-text="activeOrder.return_data.return_tracking_number"></span>
                                                    <button 
                                                        type="button" 
                                                        @click="copyTrackingResi(activeOrder.return_data.return_tracking_number)"
                                                        class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded transition cursor-pointer border"
                                                        :class="copiedResi ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                                                        title="Salin Nomor Resi Retur"
                                                    >
                                                        <svg x-show="!copiedResi" class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                        <svg x-show="copiedResi" class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        <span x-text="copiedResi ? 'Tersalin!' : 'Salin'"></span>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="!activeOrder.return_data || !activeOrder.return_data.return_tracking_number">
                                                <span class="text-xs text-slate-400 italic">Menunggu verifikasi admin & penerbitan resi</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Terkini Retur & Riwayat Checkpoint Realtime -->
                            <div class="p-4 sm:p-5 space-y-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full shrink-0" :class="activeOrder.return_tracking_data ? 'bg-emerald-600' : 'bg-amber-500'"></span>
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900" x-text="activeOrder.return_tracking_data ? activeOrder.return_tracking_data.status_title : 'Pengajuan Retur Dalam Proses'"></h4>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed pl-4" x-text="activeOrder.return_tracking_data ? activeOrder.return_tracking_data.status_desc : 'Silakan serahkan paket barang ke counter kurir terdekat.'"></p>
                                </div>

                                <!-- Timeline Checkpoints Realtime dari Ekspedisi -->
                                <template x-if="activeOrder.return_tracking_data && activeOrder.return_tracking_data.timeline && activeOrder.return_tracking_data.timeline.length > 0">
                                    <div class="pt-3 border-t border-slate-100 space-y-2.5">
                                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                            <span>Riwayat Perjalanan Pengembalian Paket</span>
                                            <span class="text-[10px] text-slate-400 font-normal">Sinkronisasi Real-Time</span>
                                        </div>
                                        <div class="space-y-3.5 relative pl-4 border-l-2 border-slate-200 ml-1.5 mt-2">
                                            <template x-for="(tItem, tIdx) in activeOrder.return_tracking_data.timeline" :key="tIdx">
                                                <div class="relative text-xs">
                                                    <div 
                                                        class="w-2.5 h-2.5 rounded-full absolute -left-[21px] top-1 border-2 border-white"
                                                        :class="tItem.current ? 'bg-emerald-600 ring-2 ring-emerald-200' : (tItem.completed ? 'bg-emerald-600' : 'bg-slate-300')"
                                                    ></div>
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                                                        <span class="font-bold" :class="tItem.current ? 'text-emerald-950 font-extrabold' : 'text-slate-800'" x-text="tItem.title"></span>
                                                        <span class="text-[11px] text-slate-400 font-normal shrink-0" x-text="tItem.time"></span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-600 leading-tight mt-0.5" x-text="tItem.desc"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <!-- Petunjuk Ringkas Drop-off Paket ke Gerai (Cashless) - Hanya tampil jika belum diserahkan/jalan -->
                                <template x-if="activeOrder.return_data && activeOrder.return_data.return_tracking_number && (!activeOrder.return_tracking_data || !['picked', 'in_transit', 'dropping_off', 'delivered'].includes(activeOrder.return_tracking_data.latest_status))">
                                    <div class="text-xs text-slate-600 space-y-1.5 bg-amber-50/70 border border-amber-200 rounded-xl p-3.5">
                                        <p class="font-bold text-amber-950 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-amber-800 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span>Langkah Penyerahan Paket Retur ke Gerai:</span>
                                        </p>
                                        <ol class="list-decimal list-inside space-y-1 text-slate-700 pl-1 leading-relaxed">
                                            <li>Kemas barang retur dengan aman dan rapat ke dalam kardus/plastik.</li>
                                            <li>Bawa paket ke gerai/counter <span class="font-bold text-slate-900" x-text="activeOrder.return_tracking_data ? activeOrder.return_tracking_data.courier_name : 'J&T Express'"></span> terdekat.</li>
                                            <li>Tunjukkan <strong>Nomor Resi Cashless</strong> di atas ke petugas counter. <strong>Anda tidak perlu membayar uang tunai untuk ongkos kirim (Bebas Biaya di Counter)</strong>.</li>
                                        </ol>
                                    </div>
                                </template>

                                <!-- Form Input Manual jika resi otomatis belum diterbitkan -->
                                <template x-if="!activeOrder.return_data || !activeOrder.return_data.return_tracking_number">
                                    <div class="space-y-2.5 pt-2 border-t border-amber-200">
                                        <p class="text-xs text-amber-900 leading-relaxed">
                                            Alamat Gudang Toko Penerima Retur:<br>
                                            <strong class="text-slate-950 bg-white/80 px-2.5 py-1 rounded-lg border border-amber-300 inline-block mt-1 font-semibold">{{ $storeAddress ?? 'Jl. Tanjung, RT 01 / RW 02, Tanjungharja, Kec. Kramat, Kab. Tegal, Jawa Tengah 52181' }}</strong>
                                        </p>

                                        <form @submit.prevent="submitReturnTrackingForm()" class="space-y-2.5 pt-1">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                <div>
                                                    <label class="block text-[11px] font-bold text-amber-900 mb-1">Nomor Resi Pengembalian <span class="text-red-500">*</span></label>
                                                    <input 
                                                        type="text" 
                                                        x-model="returnTrackingForm.number" 
                                                        required
                                                        placeholder="Contoh: JP1234567890 / SOCAG..." 
                                                        class="w-full bg-white border border-amber-300 rounded-lg px-3 py-1.5 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-bold text-amber-900 mb-1">Struk / Bukti Kirim (Opsional)</label>
                                                    <input 
                                                        type="file" 
                                                        id="return_shipping_receipt"
                                                        accept="image/*,application/pdf"
                                                        class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-amber-100 file:text-amber-900 cursor-pointer"
                                                    >
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 pt-1">
                                                <button 
                                                    type="submit" 
                                                    :disabled="submittingReturnTracking"
                                                    class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition-all cursor-pointer disabled:opacity-50 shadow-xs"
                                                >
                                                    Simpan Resi Manual
                                                </button>
                                                <a 
                                                    :href="'https://wa.me/{{ $waNumber ?? '6281234567890' }}?text=Halo%20Admin,%20saya%20sudah%20mengirim%20retur%20pesanan%20' + activeOrder.id" 
                                                    target="_blank" 
                                                    class="text-xs text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1.5 ml-2"
                                                >
                                                    <span>Konfirmasi via WhatsApp &rarr;</span>
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Accordion Dropdown: Riwayat Pengiriman Awal ke Pembeli -->
                        <div class="border border-slate-200/80 rounded-2xl bg-white shadow-2xs overflow-hidden" x-data="{ showForwardHistory: false }">
                            <button 
                                type="button" 
                                @click="showForwardHistory = !showForwardHistory" 
                                class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100 flex items-center justify-between text-xs font-bold text-slate-700 transition cursor-pointer"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Lihat Riwayat Pengiriman Awal ke Pembeli (Saat Barang Diterima)</span>
                                </span>
                                <div class="flex items-center gap-1 text-slate-500">
                                    <span class="text-[11px] font-normal" x-text="showForwardHistory ? 'Sembunyikan' : 'Tampilkan'"></span>
                                    <svg class="w-4 h-4 transform transition-transform duration-200" :class="showForwardHistory ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            <div x-show="showForwardHistory" x-cloak class="p-4 sm:p-5 border-t border-slate-100 space-y-3 bg-white">
                                <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-100">
                                    <span class="text-slate-500">Ekspedisi Awal: <strong class="text-slate-900" x-text="activeOrder.courier ? activeOrder.courier.name : 'Kurir'"></strong></span>
                                    <span class="text-slate-500 font-mono">No. Resi: <strong class="text-slate-900 select-all" x-text="activeOrder.courier ? activeOrder.courier.resi : '-'"></strong></span>
                                </div>
                                <template x-if="activeOrder.tracking_data && activeOrder.tracking_data.timeline && activeOrder.tracking_data.timeline.length > 0">
                                    <div class="space-y-3 relative pl-4 border-l-2 border-slate-200 ml-1.5 mt-2">
                                        <template x-for="(fItem, fIdx) in activeOrder.tracking_data.timeline" :key="fIdx">
                                            <template x-if="fItem.time && fItem.time !== '-' && (fItem.completed || fItem.current)">
                                                <div class="relative text-xs">
                                                    <div class="w-2.5 h-2.5 rounded-full absolute -left-[21px] top-1 bg-slate-400 border-2 border-white"></div>
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                                                        <span class="font-bold text-slate-800" x-text="fItem.title"></span>
                                                        <span class="text-[11px] text-slate-400 font-normal shrink-0" x-text="fItem.time"></span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-600 leading-tight mt-0.5" x-text="fItem.desc"></p>
                                                </div>
                                            </template>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ========================================== -->
                <!-- B. KARTU TRACKING PESANAN REGULER BIASA    -->
                <!-- ========================================== -->
                <template x-if="!['retur', 'retur_proses', 'retur_disetujui', 'retur_ditolak'].includes(activeOrder.status)">
                    <div class="border border-slate-200/80 rounded-2xl bg-white shadow-2xs overflow-hidden">
                        <!-- Header Info Pengiriman -->
                        <div class="px-5 py-4 bg-slate-50/70 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                </div>
                                <div class="space-y-0.5 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-slate-900" x-text="activeOrder.courier ? activeOrder.courier.name : 'Kurir Reguler'"></span>
                                        <span class="text-[10px] font-semibold bg-white text-slate-600 px-2 py-0.5 rounded-full border border-slate-200">Reguler</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap pt-0.5">
                                        <span class="text-xs text-slate-500 font-medium">No. Resi:</span>
                                        <template x-if="activeOrder.courier && activeOrder.courier.resi && activeOrder.courier.resi !== 'Belum Diterbitkan' && activeOrder.courier.resi !== 'Dalam Proses'">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 select-all" x-text="activeOrder.courier.resi"></span>
                                                <button 
                                                    type="button" 
                                                    @click="copyTrackingResi(activeOrder.courier.resi)"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded transition cursor-pointer border"
                                                    :class="copiedResi ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                                                    title="Salin Nomor Resi"
                                                >
                                                    <svg x-show="!copiedResi" class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                    <svg x-show="copiedResi" class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span x-text="copiedResi ? 'Tersalin!' : 'Salin'"></span>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="!activeOrder.courier || !activeOrder.courier.resi || activeOrder.courier.resi === 'Belum Diterbitkan' || activeOrder.courier.resi === 'Dalam Proses'">
                                            <span class="text-xs text-slate-400 italic">Menunggu penyerahan paket ke kurir</span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Lacak Pengiriman -->
                            <div class="flex items-center gap-2 shrink-0">
                                <button 
                                    type="button" 
                                    @click="showTrackingModal = true" 
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-colors cursor-pointer shadow-2xs"
                                >
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Lacak Pengiriman</span>
                                </button>
                            </div>
                        </div>

                        <!-- Status Terkini & Riwayat Singkat Forward Delivery -->
                        <template x-if="activeOrder.tracking_data">
                            <div class="p-4 sm:p-5 space-y-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900" x-text="activeOrder.tracking_data.status_title"></h4>
                                        </div>
                                        <p class="text-xs text-slate-600 leading-relaxed pl-4" x-text="activeOrder.tracking_data.status_desc"></p>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="showTrackingModal = true" 
                                        class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline shrink-0 inline-flex items-center gap-1 cursor-pointer transition-colors pt-0.5"
                                    >
                                        <span>Lihat Detail</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </button>
                                </div>

                                <!-- Granular Shopee-Style Timeline History -->
                                <template x-if="activeOrder.tracking_data.timeline && activeOrder.tracking_data.timeline.length > 0">
                                    <div class="pt-3 border-t border-slate-100 space-y-2">
                                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Riwayat Status Terakhir:</div>
                                        <div class="space-y-3 relative pl-4 border-l-2 border-slate-200 ml-1.5 mt-2">
                                            <template x-for="(tItem, tIdx) in activeOrder.tracking_data.timeline" :key="tIdx">
                                                <template x-if="tItem.time && tItem.time !== '-' && tItem.time !== 'Menunggu update ekspedisi' && (tItem.completed || tItem.current)">
                                                    <div class="relative text-xs">
                                                        <div 
                                                            class="w-2.5 h-2.5 rounded-full absolute -left-[21px] top-1 border-2 border-white"
                                                            :class="tItem.current ? 'bg-blue-600 ring-2 ring-blue-200' : (tItem.completed ? 'bg-slate-400' : 'bg-slate-200')"
                                                        ></div>
                                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                                                            <span class="font-bold text-slate-800" x-text="tItem.title"></span>
                                                            <span class="text-[11px] text-slate-400 font-normal shrink-0" x-text="tItem.time"></span>
                                                        </div>
                                                        <p class="text-[11px] text-slate-600 leading-tight mt-0.5" x-text="tItem.desc"></p>
                                                    </div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- 3. Products List -->
                <div class="space-y-4">
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 border-b border-slate-100 pb-2">Produk Yang Dibeli</h3>
                    <div class="divide-y divide-slate-100 border border-slate-200/80 rounded-2xl bg-white shadow-2xs overflow-hidden">
                        <template x-for="(item, itemIdx) in activeOrder.items" :key="itemIdx">
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <img 
                                        :src="item.image || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600'" 
                                        :alt="item.name"
                                        class="w-16 h-16 rounded-xl object-cover border border-slate-200 shrink-0 bg-slate-50"
                                        style="width: 64px; height: 64px; min-width: 64px; min-height: 64px; object-fit: cover;"
                                        loading="lazy"
                                    >
                                    <div class="space-y-1">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug" x-text="item.name"></h4>
                                        <p class="text-xs text-slate-500" x-text="item.variant"></p>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="text-slate-500" x-text="item.qty + ' x'"></span>
                                            <span class="font-semibold text-slate-800" x-text="formatRupiah(item.price)"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                    <span class="font-extrabold text-slate-900 text-sm sm:text-base" x-text="formatRupiah(item.price * item.qty)"></span>
                                    <template x-if="activeOrder.status === 'selesai'">
                                        <div>
                                            <template x-if="!item.is_reviewed">
                                                <button 
                                                    type="button" 
                                                    @click="openReviewModal(item)" 
                                                    class="text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-300 px-3 py-1.5 rounded-lg transition-colors cursor-pointer"
                                                >
                                                    Beri Ulasan ⭐
                                                </button>
                                            </template>
                                            <template x-if="item.is_reviewed">
                                                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg">
                                                    ✓ Sudah Dinilai
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 4. Delivery Address & Courier Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Delivery Address -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 sm:p-5 space-y-2">
                        <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                            <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                Alamat Pengiriman
                            </span>
                            <span class="text-[10px] font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">Penerima</span>
                        </div>
                        <p class="font-bold text-slate-900 text-sm" x-text="activeOrder.recipient.name"></p>
                        <p class="text-xs text-slate-600 font-medium" x-text="activeOrder.recipient.phone"></p>
                        <p class="text-xs text-slate-600 leading-relaxed" x-text="activeOrder.recipient.address"></p>
                    </div>

                    <!-- Courier Info -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 sm:p-5 space-y-2">
                        <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                            <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                                Informasi Kurir & Resi
                            </span>
                        </div>
                        <p class="font-bold text-slate-900 text-sm" x-text="activeOrder.courier.name"></p>
                        <p class="text-xs text-slate-600 font-mono font-bold" x-text="'No. Resi: ' + activeOrder.courier.resi"></p>
                        <p class="text-xs text-slate-500" x-text="'Estimasi Tiba: ' + activeOrder.courier.etd"></p>
                    </div>
                </div>

                <!-- 5. Price Breakdown -->
                <div class="bg-slate-50 rounded-2xl p-4 sm:p-5 border border-slate-200/60 space-y-2.5 text-xs sm:text-sm">
                    <h4 class="font-extrabold text-slate-900 uppercase tracking-wider text-xs border-b border-slate-200/60 pb-2">Rincian Pembayaran</h4>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Subtotal Produk</span>
                        <span class="font-bold text-slate-900" x-text="formatRupiah(activeOrder.subtotal)"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Ongkos Kirim</span>
                        <span class="font-bold text-slate-900" x-text="formatRupiah(activeOrder.shipping_cost)"></span>
                    </div>
                    <template x-if="activeOrder.discount > 0">
                        <div class="flex items-center justify-between text-emerald-700 font-medium">
                            <span>Diskon Voucher (<span x-text="activeOrder.voucher_code"></span>)</span>
                            <span class="font-bold" x-text="'- ' + formatRupiah(activeOrder.discount)"></span>
                        </div>
                    </template>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Biaya Layanan Aplikasi</span>
                        <span class="font-bold text-emerald-600">GRATIS</span>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-sm sm:text-base font-extrabold text-slate-900">Grand Total Tagihan</span>
                        <span class="text-lg sm:text-2xl font-black text-blue-700" x-text="formatRupiah(activeOrder.total_amount)"></span>
                    </div>
                </div>

                <!-- 6. Action Buttons Bar -->
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                    <!-- STATUS 1: BELUM BAYAR -->
                    <template x-if="activeOrder.status === 'belum_bayar' || activeOrder.status === 'belum_dibayar'">
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full">
                            <a 
                                :href="'{{ route('checkout.success') }}?order_id=' + activeOrder.id"
                                class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl transition-all shadow-xs text-center flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span>Selesaikan Pembayaran Sekarang</span>
                            </a>

                            <button 
                                type="button"
                                @click="openCancelModal(activeOrder.raw_id)"
                                class="w-full sm:w-auto bg-white hover:bg-red-50 text-red-600 border border-slate-300 hover:border-red-200 font-semibold text-xs sm:text-sm px-5 py-3.5 rounded-xl transition-colors cursor-pointer text-center"
                            >
                                Batalkan Pesanan
                            </button>
                        </div>
                    </template>

                    <!-- STATUS 2: DIPROSES / DIKEMAS -->
                    <template x-if="activeOrder.status === 'diproses' || activeOrder.status === 'dikemas'">
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full">
                            <button 
                                type="button" 
                                @click="alert('Menghubungi Penjual untuk Pesanan: ' + activeOrder.id)"
                                class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl transition-all shadow-xs cursor-pointer flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span>Hubungi Penjual</span>
                            </button>

                            <button 
                                type="button" 
                                @click="openCancelModal(activeOrder.raw_id || activeOrder.id)"
                                class="w-full sm:w-auto bg-white hover:bg-red-50 text-red-600 border border-slate-300 hover:border-red-200 font-semibold text-xs sm:text-sm px-5 py-3.5 rounded-xl transition-colors cursor-pointer text-center"
                            >
                                Batalkan Pesanan
                            </button>

                            <button 
                                type="button" 
                                @click="window.print()"
                                class="w-full sm:w-auto bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold text-xs sm:text-sm px-5 py-3.5 rounded-xl transition-colors cursor-pointer text-center"
                            >
                                Cetak Invoice
                            </button>
                        </div>
                    </template>

                    <!-- STATUS 3: DIKIRIM -->
                    <template x-if="activeOrder.status === 'dikirim'">
                        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 w-full">
                            <!-- Show Pesanan Diterima button ONLY when parcel has arrived at destination or step 4 -->
                            <template x-if="activeOrder.tracking_data && (activeOrder.tracking_data.current_step === 4 || (activeOrder.tracking_data.status_title && activeOrder.tracking_data.status_title.toLowerCase().includes('diterima')))">
                                <div class="flex items-center gap-3 w-full sm:w-auto">
                                    <button 
                                        type="button" 
                                        @click="openReturnModal()"
                                        class="w-full sm:w-auto bg-white hover:bg-rose-50 text-slate-700 hover:text-rose-700 border border-slate-300 hover:border-rose-200 font-semibold text-xs sm:text-sm px-5 py-2.5 rounded-xl transition-all cursor-pointer flex items-center justify-center gap-1.5"
                                    >
                                        <svg class="w-4 h-4 text-slate-500 hover:text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Ajukan Pengembalian</span>
                                    </button>

                                    <button 
                                        type="button" 
                                        @click="openCompleteConfirmModal(activeOrder)"
                                        class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm px-6 py-2.5 rounded-xl transition-all shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
                                    >
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Pesanan Diterima</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- STATUS 4: SELESAI -->
                    <template x-if="activeOrder.status === 'selesai'">
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full">
                            <template x-if="hasUnreviewedItems">
                                <button 
                                    type="button" 
                                    @click="openReviewModal()"
                                    class="w-full sm:flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl transition-all shadow-xs cursor-pointer flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Beri Penilaian Produk</span>
                                </button>
                            </template>

                            <a 
                                href="{{ route('catalog') }}"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm px-6 py-3.5 rounded-xl transition-colors text-center cursor-pointer"
                            >
                                Beli Lagi
                            </a>
                        </div>
                    </template>

                    <!-- STATUS RETUR / KOMPLAIN -->
                    <template x-if="activeOrder.status === 'retur' || activeOrder.status === 'komplain'">
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full">
                            <div class="flex-1 bg-amber-50 text-amber-900 text-xs sm:text-sm font-medium px-4 py-3 rounded-xl border border-amber-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Pengajuan Retur / Komplain dalam proses peninjauan oleh Tim Admin Toko. Mohon tunggu tanggapan 1x24 jam.</span>
                            </div>
                        </div>
                    </template>

                    <!-- STATUS PENGAJUAN BATAL -->
                    <template x-if="activeOrder.status === 'pengajuan_batal'">
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full">
                            <div class="flex-1 bg-amber-50 text-amber-900 text-xs sm:text-sm font-medium px-4 py-3 rounded-xl border border-amber-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Pengajuan pembatalan Anda sedang menunggu konfirmasi/persetujuan dari Admin.</span>
                            </div>
                        </div>
                    </template>

                    <!-- STATUS 5: BATAL -->
                    <template x-if="activeOrder.status === 'batal'">
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full">
                            <div class="flex-1 bg-red-50 text-red-800 text-xs sm:text-sm font-medium px-4 py-3 rounded-xl border border-red-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Pesanan dibatalkan oleh sistem.</span>
                            </div>
                            <a 
                                href="{{ route('catalog') }}"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-colors text-center cursor-pointer flex items-center justify-center gap-1.5"
                            >
                                Beli Lagi
                            </a>
                        </div>
                    </template>
                </div>



            </div>
        </template>
    </div>

    <!-- MODAL RATING & ULASAN PRODUK -->
    <div x-show="showReviewModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showReviewModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 relative">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Penilaian Produk</h3>
                        <p class="text-xs text-slate-500">Ulasan Anda membantu pembeli lain membuat keputusan</p>
                    </div>
                </div>
                <button type="button" @click="showReviewModal = false" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-lg cursor-pointer transition-colors">&times;</button>
            </div>

            <form @submit.prevent="submitReview()" class="space-y-4">
                <!-- Product Info Snippet -->
                <template x-if="selectedReviewItem">
                    <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200/80">
                        <img :src="selectedReviewItem.image" class="w-12 h-12 rounded-lg object-cover border border-slate-200">
                        <div class="text-xs">
                            <p class="font-bold text-slate-900 leading-snug" x-text="selectedReviewItem.name"></p>
                            <p class="text-slate-500 mt-0.5" x-text="selectedReviewItem.variant"></p>
                        </div>
                    </div>
                </template>

                <!-- Star Rating Picker -->
                <div class="text-center space-y-2 py-2 bg-slate-50/50 rounded-xl border border-slate-100">
                    <label class="block text-xs font-bold text-slate-700">Kualitas Produk</label>
                    <div class="flex items-center justify-center gap-2">
                        <template x-for="star in [1,2,3,4,5]" :key="star">
                            <button 
                                type="button" 
                                @click="selectedRating = star"
                                class="p-1 focus:outline-none transition-transform hover:scale-125 cursor-pointer"
                            >
                                <svg 
                                    class="w-8 h-8" 
                                    :class="star <= selectedRating ? 'text-amber-400 fill-amber-400' : 'text-slate-300 fill-slate-200'"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                    <p class="text-xs font-bold text-amber-700 h-4" x-text="getRatingLabel(selectedRating)"></p>
                </div>

                <!-- Chips Suggestions -->
                <div class="space-y-1.5">
                    <span class="text-[11px] text-slate-500 font-medium">Ide Ulasan Cepat:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" @click="addChip('Kualitas produk sangat bagus!')" class="text-[11px] bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 px-2.5 py-1 rounded-lg transition-colors cursor-pointer">+ Kualitas Bagus</button>
                        <button type="button" @click="addChip('Pengiriman sangat cepat & rapi.')" class="text-[11px] bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 px-2.5 py-1 rounded-lg transition-colors cursor-pointer">+ Pengiriman Cepat</button>
                        <button type="button" @click="addChip('Respon penjual ramah dan cepat.')" class="text-[11px] bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 px-2.5 py-1 rounded-lg transition-colors cursor-pointer">+ Penjual Ramah</button>
                        <button type="button" @click="addChip('Produk original 100% dan sesuai deskripsi.')" class="text-[11px] bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 px-2.5 py-1 rounded-lg transition-colors cursor-pointer">+ Original 100%</button>
                    </div>
                </div>

                <!-- Comment Textarea -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ulasan Tertulis</label>
                    <textarea 
                        x-model="reviewComment" 
                        rows="3" 
                        placeholder="Bagikan pengalaman Anda menggunakan produk ini..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:border-blue-700 focus:bg-white"
                    ></textarea>
                </div>

                <!-- Upload Photo -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto / Video Produk (Opsional)</label>
                    <input 
                        type="file" 
                        id="review_image"
                        accept="image/*"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                    >
                </div>

                <!-- Anonymous Checkbox -->
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="anon" x-model="isAnonymous" class="rounded text-blue-700 focus:ring-blue-700">
                    <label for="anon" class="text-xs text-slate-600">Sembunyikan nama pengguna Anda pada ulasan (Contoh: N*r A*i P*r*o*o)</label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm py-3 rounded-xl transition-all shadow-md cursor-pointer"
                    >
                        Kirim Ulasan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PENGAJUAN RETUR / REFUND GARANSI TOKO -->
    <div x-show="showReturnModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showReturnModal = false" class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 relative">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">Form Pengajuan Retur & Komplain</h3>
                        <p class="text-xs text-slate-500">Klaim garansi toko resmi dengan bukti unboxing transparan</p>
                    </div>
                </div>
                <button type="button" @click="showReturnModal = false" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-lg cursor-pointer transition-colors">&times;</button>
            </div>

            <!-- Ringkasan Produk Yang Dikomplain (Product Snapshot) -->
            <template x-if="activeOrder && activeOrder.items && activeOrder.items.length > 0">
                <div class="flex items-center gap-3 bg-slate-50 border border-slate-200/80 rounded-xl p-3">
                    <img :src="activeOrder.items[0].image || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600'" class="w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0 bg-white">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-900 truncate text-xs" x-text="activeOrder.items[0].name"></p>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="activeOrder.items.length > 1 ? (activeOrder.items[0].variant + ' • +' + (activeOrder.items.length - 1) + ' barang lainnya') : activeOrder.items[0].variant"></p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[10px] text-slate-400 block font-medium">Estimasi Refund:</span>
                        <span class="font-extrabold text-blue-700 text-xs sm:text-sm" x-text="formatRupiah(activeOrder.total_amount)"></span>
                    </div>
                </div>
            </template>

            <form @submit.prevent="submitReturnForm()" class="space-y-3.5 text-xs">
                <!-- Solution Type -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">Solusi Yang Diinginkan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="border rounded-xl p-3 flex items-center gap-2.5 cursor-pointer transition-all" :class="returnForm.solution_type === 'refund' ? 'border-blue-600 bg-blue-50/50 text-blue-900 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                            <input type="radio" name="solution" value="refund" x-model="returnForm.solution_type" class="text-blue-700">
                            <div>
                                <p class="text-xs">Pengembalian Dana (Refund)</p>
                                <p class="text-[10px] text-slate-500 font-normal">Uang kembali ke rekening/e-wallet</p>
                            </div>
                        </label>
                        <label class="border rounded-xl p-3 flex items-center gap-2.5 cursor-pointer transition-all" :class="returnForm.solution_type === 'replacement' ? 'border-blue-600 bg-blue-50/50 text-blue-900 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                            <input type="radio" name="solution" value="replacement" x-model="returnForm.solution_type" class="text-blue-700">
                            <div>
                                <p class="text-xs">Penukaran Barang (Replacement)</p>
                                <p class="text-[10px] text-slate-500 font-normal">Kirim produk baru pengganti</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Reason -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alasan Pengembalian <span class="text-red-500">*</span></label>
                    <select x-model="returnForm.reason" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 focus:outline-none focus:border-blue-700">
                        <option value="barang_cacat">Produk Cacat / Rusak Fisik / Pecah</option>
                        <option value="salah_kirim">Produk Tidak Sesuai Pesanan (Salah Warna/Ukuran)</option>
                        <option value="kurang_barang">Jumlah Produk Kurang / Aksesoris Hilang</option>
                        <option value="tidak_berfungsi">Produk Tidak Berfungsi Normal / Error</option>
                        <option value="tidak_original">Indikasi Barang Tidak Original</option>
                    </select>
                </div>

                <!-- Description -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Penjelasan Masalah Detail <span class="text-red-500">*</span></label>
                    <textarea 
                        x-model="returnForm.description" 
                        rows="2"
                        placeholder="Jelaskan kondisi kerusakan atau ketidaksesuaian barang secara rinci..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 focus:outline-none focus:border-blue-700"
                    ></textarea>
                </div>

                <!-- Bank Info for Refund -->
                <template x-if="returnForm.solution_type === 'refund'">
                    <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl space-y-2">
                        <p class="font-bold text-blue-900 text-xs">Informasi Rekening Pengembalian Dana (Refund)</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Nama Bank / E-Wallet <span class="text-red-500">*</span></label>
                                <select x-model="returnForm.bank_name" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-xs">
                                    <option value="BCA">BCA (Bank Central Asia)</option>
                                    <option value="Mandiri">Bank Mandiri</option>
                                    <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
                                    <option value="BNI">BNI (Bank Negara Indonesia)</option>
                                    <option value="Gopay">GoPay</option>
                                    <option value="OVO">OVO</option>
                                    <option value="Dana">DANA</option>
                                    <option value="ShopeePay">ShopeePay</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Nomor Rekening / No. HP E-Wallet <span class="text-red-500">*</span></label>
                                <input type="text" x-model="returnForm.bank_account_number" placeholder="Contoh: 1234567890" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-xs font-mono">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Atas Nama Rekening <span class="text-red-500">*</span></label>
                                <input type="text" x-model="returnForm.bank_account_name" placeholder="Nama lengkap pemilik rekening / akun e-wallet" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-xs">
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Modern Media Upload Cards (No Raw Choose File) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Foto Fisik Barang -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Foto Kondisi Fisik Barang <span class="text-red-500">*</span>
                        </label>
                        <label 
                            for="photo_proof" 
                            class="flex flex-col items-center justify-center p-3 border-2 border-dashed rounded-xl transition-all cursor-pointer relative overflow-hidden min-h-[78px]"
                            :class="photoPreview ? 'border-blue-500 bg-blue-50/20' : 'border-slate-300 hover:border-blue-400 bg-slate-50/50 hover:bg-slate-50'"
                        >
                            <input 
                                type="file" 
                                id="photo_proof" 
                                accept="image/*" 
                                class="sr-only"
                                @change="handlePhotoChange($event)"
                            >
                            <template x-if="!photoPreview">
                                <div class="text-center space-y-1">
                                    <svg class="w-6 h-6 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <p class="text-[11px] font-bold text-slate-700">Unggah Foto Bukti Fisik</p>
                                    <p class="text-[10px] text-slate-400">JPG, PNG (Maks 20MB)</p>
                                </div>
                            </template>
                            <template x-if="photoPreview">
                                <div class="flex items-center gap-2.5 w-full">
                                    <img :src="photoPreview" class="w-11 h-11 object-cover rounded-lg border border-blue-200 shrink-0 bg-white">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-bold text-blue-900 truncate">Foto Berhasil Dipilih</p>
                                        <p class="text-[10px] text-blue-600">Klik untuk ganti foto</p>
                                    </div>
                                    <span class="text-xs text-blue-700 font-bold shrink-0">Ganti ↻</span>
                                </div>
                            </template>
                        </label>
                    </div>

                    <!-- Video Unboxing -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Video Unboxing Paket <span class="text-red-500">*</span>
                        </label>
                        <label 
                            for="video_proof" 
                            class="flex flex-col items-center justify-center p-3 border-2 border-dashed rounded-xl transition-all cursor-pointer relative overflow-hidden min-h-[78px]"
                            :class="videoFileName ? 'border-blue-500 bg-blue-50/20' : 'border-slate-300 hover:border-blue-400 bg-slate-50/50 hover:bg-slate-50'"
                        >
                            <input 
                                type="file" 
                                id="video_proof" 
                                accept="video/*" 
                                class="sr-only"
                                @change="handleVideoChange($event)"
                            >
                            <template x-if="!videoFileName">
                                <div class="text-center space-y-1">
                                    <svg class="w-6 h-6 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <p class="text-[11px] font-bold text-slate-700">Unggah Video Unboxing</p>
                                    <p class="text-[10px] text-slate-400">MP4, MOV (Maks 100MB)</p>
                                </div>
                            </template>
                            <template x-if="videoFileName">
                                <div class="flex items-center gap-2.5 w-full">
                                    <div class="w-11 h-11 bg-blue-100 text-blue-700 rounded-lg flex items-center justify-center shrink-0 font-bold">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-bold text-blue-900 truncate" x-text="videoFileName"></p>
                                        <p class="text-[10px] text-blue-600">Klik untuk ganti video</p>
                                    </div>
                                    <span class="text-xs text-blue-700 font-bold shrink-0">Ganti ↻</span>
                                </div>
                            </template>
                        </label>
                    </div>
                </div>

                <!-- SLA Trust & Ketentuan Garansi -->
                <div class="flex items-start gap-2.5 bg-blue-50/70 border border-blue-100 rounded-xl p-3 text-[11px] text-blue-950">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="leading-relaxed">
                        <span class="font-bold">Ketentuan Garansi Toko:</span> Pengajuan Anda akan ditinjau tim admin dalam waktu maksimal <strong>1x24 jam kerja</strong>. Jika disetujui, nomor resi retur otomatis (Biteship Bebas Biaya/Cashless) akan diterbitkan di halaman ini.
                    </div>
                </div>

                <!-- Submit & Cancel Buttons -->
                <div class="flex items-center gap-2 pt-1">
                    <button 
                        type="button" 
                        @click="showReturnModal = false" 
                        class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-xl transition-all cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        :disabled="submittingReturn"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm py-3 rounded-xl transition-all shadow-sm cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <span x-text="submittingReturn ? 'Mengirim Pengajuan...' : 'Kirim Pengajuan Garansi Retur'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL POPUP LACAK STATUS EKSPEDISI -->
    <div x-show="showTrackingModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showTrackingModal = false" class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-4 relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900" x-text="activeOrder && ['retur', 'retur_proses', 'retur_disetujui', 'retur_ditolak'].includes(activeOrder.status) ? 'Lacak Pengembalian Barang' : 'Lacak Pengiriman Paket'"></h3>
                        <template x-if="activeOrder">
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs text-slate-600 font-medium" x-text="['retur', 'retur_proses', 'retur_disetujui', 'retur_ditolak'].includes(activeOrder.status) ? (activeOrder.return_tracking_data ? activeOrder.return_tracking_data.courier_name : activeOrder.courier.name) : activeOrder.courier.name"></span>
                                <span class="text-xs font-mono font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 select-all" x-text="['retur', 'retur_proses', 'retur_disetujui', 'retur_ditolak'].includes(activeOrder.status) ? (activeOrder.return_data && activeOrder.return_data.return_tracking_number ? activeOrder.return_data.return_tracking_number : activeOrder.courier.resi) : activeOrder.courier.resi"></span>
                                <button 
                                    type="button" 
                                    @click="copyTrackingResi(['retur', 'retur_proses', 'retur_disetujui', 'retur_ditolak'].includes(activeOrder.status) ? (activeOrder.return_data && activeOrder.return_data.return_tracking_number ? activeOrder.return_data.return_tracking_number : activeOrder.courier.resi) : activeOrder.courier.resi)"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded transition cursor-pointer border"
                                    :class="copiedResi ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                                    title="Salin Nomor Resi"
                                >
                                    <svg x-show="!copiedResi" class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <svg x-show="copiedResi" class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="copiedResi ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                <button type="button" @click="showTrackingModal = false" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-lg cursor-pointer transition-colors">&times;</button>
            </div>

            <!-- Timeline Checkpoints (Shopee/Tokopedia Clean Style) -->
            <div class="space-y-3 pt-1">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Riwayat Perjalanan Paket</h4>
                    <span class="text-[11px] text-slate-400">Pembaruan resmi ekspedisi</span>
                </div>
                
                <template x-if="loadingTracking">
                    <div class="py-8 text-center text-slate-400 text-xs animate-pulse flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Memuat status dari ekspedisi...</span>
                    </div>
                </template>

                <template x-if="!loadingTracking && trackingHistory.length > 0">
                    <div class="space-y-3.5 relative pl-5 border-l-2 border-blue-200 ml-2.5 max-h-80 overflow-y-auto pr-2">
                        <template x-for="(history, hIdx) in trackingHistory" :key="hIdx">
                            <div class="relative text-xs">
                                <div 
                                    class="w-3 h-3 rounded-full absolute -left-[27px] top-0.5 border-2 border-white"
                                    :class="hIdx === 0 ? 'bg-blue-600 ring-2 ring-blue-200' : 'bg-slate-300'"
                                ></div>
                                <p class="font-bold text-xs" :class="hIdx === 0 ? 'text-blue-900 font-extrabold' : 'text-slate-800'" x-text="history.note || history.status"></p>
                                <p class="text-[11px] text-slate-500 font-mono mt-0.5" x-text="history.updated_at || history.date"></p>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!loadingTracking && trackingHistory.length === 0">
                    <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-xl text-slate-600 text-xs text-center space-y-1">
                        <p class="font-semibold text-slate-800">Paket sedang dalam persiapan penyerahan ke pihak ekspedisi.</p>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Riwayat status perjalanan akan otomatis terbarui di sini setelah paket diterima dan di-scan oleh kurir.</p>
                    </div>
                </template>
            </div>
            
            <div class="pt-2 border-t border-slate-100 flex justify-end">
                <button 
                    type="button" 
                    @click="showTrackingModal = false" 
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL BATALKAN PESANAN -->
    <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCancelModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto font-bold text-xl">⚠️</div>
            <h3 class="text-base font-extrabold text-slate-900">Konfirmasi Pembatalan Pesanan</h3>
            <p class="text-xs text-slate-500">
                <template x-if="activeOrder && (activeOrder.status === 'belum_bayar' || activeOrder.status === 'belum_dibayar')">
                    <span>Pesanan belum dibayar akan otomatis dibatalkan seketika tanpa perlu persetujuan admin.</span>
                </template>
                <template x-if="activeOrder && (activeOrder.status === 'diproses' || activeOrder.status === 'dikemas')">
                    <span>Pesanan sudah dibayar/diproses. Pengajuan pembatalan akan dikirim ke admin untuk dikonfirmasi.</span>
                </template>
            </p>
            <div class="flex items-center gap-2 pt-2">
                <button type="button" @click="showCancelModal = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2.5 rounded-xl cursor-pointer">Batal</button>
                <button type="button" @click="executeCancelOrder()" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold text-xs py-2.5 rounded-xl cursor-pointer">Ya, Batalkan</button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI PESANAN DITERIMA -->
    <div x-show="showCompleteConfirmModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCompleteConfirmModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto font-bold text-xl">✓</div>
            <h3 class="text-base font-extrabold text-slate-900">Selesaikan Pesanan Ini?</h3>
            <p class="text-xs text-slate-500">Pastikan Anda sudah menerima barang dalam kondisi baik. Setelah pesanan dikonfirmasi selesai, tombol pengajuan garansi otomatis akan ditutup.</p>
            <div class="flex items-center gap-2 pt-2">
                <button type="button" @click="showCompleteConfirmModal = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2.5 rounded-xl cursor-pointer">Belum</button>
                <button type="button" @click="executeCompleteOrder()" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 rounded-xl cursor-pointer">Ya, Sudah Diterima</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function orderManagerApp() {
    return {
        orders: @json($orders),
        selectedOrderIndex: 0,
        viewMode: 'list', // 'list' | 'detail'
        activeFilter: 'semua',
        searchQuery: @json(request('search', '')),
        showReviewModal: false,
        showReturnModal: false,
        showTrackingModal: false,
        showCancelModal: false,
        showCompleteConfirmModal: false,
        submittingReturn: false,
        submittingReturnTracking: false,
        photoPreview: null,
        videoFileName: null,
        returnForm: {
            solution_type: 'refund',
            reason: 'barang_cacat',
            description: '',
            bank_name: 'BCA',
            bank_account_number: '',
            bank_account_name: ''
        },
        returnTrackingForm: {
            number: ''
        },

        cancelOrderId: null,
        loadingTracking: false,
        trackingHistory: [],
        liveTrackingLink: '',
        selectedRating: 5,
        reviewComment: '',
        isAnonymous: false,
        dismissedReviews: {},
        nowTimestamp: Math.floor(Date.now() / 1000),
        copiedResi: false,

        copyTrackingResi(resi) {
            if (!resi || resi === 'Belum Diterbitkan' || resi === 'Dalam Proses') return;
            navigator.clipboard.writeText(resi);
            this.copiedResi = true;
            setTimeout(() => {
                this.copiedResi = false;
            }, 2000);
        },

        selectOrder(idx) {
            const targetOrder = this.filteredOrders[idx];
            if (targetOrder) {
                const mainIndex = this.orders.findIndex(o => (o.id && o.id === targetOrder.id) || (o.raw_id && o.raw_id === targetOrder.raw_id));
                this.selectedOrderIndex = mainIndex !== -1 ? mainIndex : idx;
            } else {
                this.selectedOrderIndex = idx;
            }
            this.viewMode = 'detail';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        selectOrderAndShowModal(idx) {
            this.selectOrder(idx);
        },

        goBackToList() {
            this.viewMode = 'list';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        init() {
            setInterval(() => {
                this.nowTimestamp = Math.floor(Date.now() / 1000);
            }, 1000);

            // Check URL parameters or localStorage for persisting tab filter on refresh
            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search');
            const orderIdParam = urlParams.get('order_id') || urlParams.get('id') || searchParam;
            const filterParam = urlParams.get('filter') || urlParams.get('status');
            const savedFilter = localStorage.getItem('user_orders_active_filter');

            if (searchParam) {
                // Jika dari tombol notifikasi pencarian invoice, set default semua agar tidak terhalang filter tab lain
                this.activeFilter = 'semua';
            } else if (filterParam) {
                this.activeFilter = filterParam;
            } else if (savedFilter) {
                this.activeFilter = savedFilter;
            }

            // Keep localStorage and URL query params in sync whenever activeFilter changes
            this.$watch('activeFilter', (val) => {
                if (val) {
                    localStorage.setItem('user_orders_active_filter', val);
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('status', val);
                    window.history.replaceState(null, '', newUrl.toString());
                }
            });

            if (orderIdParam) {
                const cleanParam = String(orderIdParam).toLowerCase().trim().replace(/[\/-]/g, '');
                const foundIdx = this.orders.findIndex(o => {
                    const id1 = String(o.id || '').toLowerCase().trim().replace(/[\/-]/g, '');
                    const id2 = String(o.raw_id || '').toLowerCase().trim().replace(/[\/-]/g, '');
                    return id1 === cleanParam || id2 === cleanParam || String(o.id) === orderIdParam || String(o.raw_id) === orderIdParam || (cleanParam.length > 5 && (id1.includes(cleanParam) || id2.includes(cleanParam)));
                });
                if (foundIdx !== -1) {
                    this.selectedOrderIndex = foundIdx;
                    this.viewMode = 'detail';
                    const targetStatus = this.orders[foundIdx]?.status || '';
                    if (targetStatus.includes('retur') || targetStatus === 'komplain') {
                        this.activeFilter = 'retur';
                    } else if (targetStatus) {
                        this.activeFilter = targetStatus;
                    }
                }
            }

            this.$watch('showTrackingModal', (val) => {
                if (val) {
                    this.fetchLiveTracking();
                }
            });
        },

        fetchLiveTracking() {
            if (!this.activeOrder) return;
            this.loadingTracking = true;
            this.trackingHistory = [];
            const orderId = this.activeOrder.raw_id || this.activeOrder.id;

            fetch('/orders/' + encodeURIComponent(orderId) + '/track', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    this.trackingHistory = data.history || [];
                }
            })
            .catch(err => {
                console.error('Error fetching tracking history:', err);
            })
            .finally(() => {
                this.loadingTracking = false;
            });
        },
        
        openCancelModal(orderId) {
            this.cancelOrderId = orderId;
            this.showCancelModal = true;
        },

        executeCancelOrder() {
            const targetId = this.cancelOrderId || (this.activeOrder ? (this.activeOrder.raw_id || this.activeOrder.id) : null);
            if (!targetId) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch('/orders/' + encodeURIComponent(targetId) + '/cancel', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal membatalkan pesanan.');
                }
                return data;
            })
            .then(data => {
                this.showCancelModal = false;
                alert('✓ ' + (data.message || 'Pesanan berhasil dibatalkan!'));
                window.location.reload();
            })
            .catch(err => {
                this.showCancelModal = false;
                alert('⚠️ ' + err.message);
            });
        },

        openCompleteConfirmModal(order) {
            if (!order) return;
            this.showCompleteConfirmModal = true;
        },

        executeCompleteOrder() {
            this.showCompleteConfirmModal = false;
            if (this.activeOrder) {
                this.confirmCompleteOrder(this.activeOrder);
            }
        },

        confirmCompleteOrder(order) {
            if (!order) return;

            const targetId = order.raw_id || order.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch('/orders/' + encodeURIComponent(targetId) + '/complete', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    alert('⚠️ ' + (data.message || 'Gagal memperbarui status.'));
                    return;
                }
                order.status = 'selesai';
                order.status_label = 'Pesanan Selesai';
                order.status_color = 'green';
                order.step = 4;
                if (order.courier) {
                    order.courier.etd = 'Tiba di Lokasi';
                    order.courier.resi = order.courier.resi || 'Selesai';
                }
                alert('✓ ' + (data.message || 'Status pesanan berhasil diperbarui menjadi Selesai. Terima kasih!'));
            })
            .catch(err => {
                order.status = 'selesai';
                order.status_label = 'Pesanan Selesai';
                order.status_color = 'green';
                order.step = 4;
                alert('✓ Status pesanan berhasil diperbarui menjadi Selesai. Terima kasih!');
            });
        },

        getRemainingTime(createdTs) {
            if (!createdTs) return '23:59:59';
            const expiry = Number(createdTs) + 86400;
            const diff = Math.max(0, expiry - this.nowTimestamp);
            if (diff <= 0) return '00:00:00 (Waktu Habis)';
            const h = String(Math.floor(diff / 3600)).padStart(2, '0');
            const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            const s = String(diff % 60).padStart(2, '0');
            return h + ':' + m + ':' + s;
        },

        addChip(text) {
            if (this.reviewComment.trim()) {
                this.reviewComment += ' ' + text;
            } else {
                this.reviewComment = text;
            }
        },

        selectedReviewItem: null,

        openReviewModal(item = null) {
            if (item) {
                this.selectedReviewItem = item;
            } else if (this.activeOrder && this.activeOrder.items.length > 0) {
                this.selectedReviewItem = this.activeOrder.items[0];
            }
            this.selectedRating = 5;
            this.reviewComment = '';
            this.isAnonymous = false;
            this.showReviewModal = true;
            
            setTimeout(() => {
                const imageInput = document.getElementById('review_image');
                if (imageInput) imageInput.value = '';
            }, 50);
        },

        openReturnModal() {
            if (!this.activeOrder) return;
            if (this.activeOrder.status === 'selesai') {
                alert('Pesanan sudah diselesaikan (Tombol Pesanan Diterima telah diklik). Sesuai garansi toko, pengajuan pengembalian otomatis via aplikasi telah ditutup. Silakan hubungi CS Penjual.');
                return;
            }
            this.returnForm = {
                solution_type: 'refund',
                reason: 'barang_cacat',
                description: '',
                bank_name: 'BCA',
                bank_account_number: '',
                bank_account_name: ''
            };
            this.photoPreview = null;
            this.videoFileName = null;
            setTimeout(() => {
                const photoInput = document.getElementById('photo_proof');
                const videoInput = document.getElementById('video_proof');
                if (photoInput) photoInput.value = '';
                if (videoInput) videoInput.value = '';
            }, 50);
            this.showReturnModal = true;
        },

        handlePhotoChange(e) {
            const file = e.target.files ? e.target.files[0] : null;
            if (file) {
                if (file.size > 20 * 1024 * 1024) {
                    alert('⚠️ Ukuran foto maksimal 20MB.');
                    e.target.value = '';
                    this.photoPreview = null;
                    return;
                }
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.photoPreview = event.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.photoPreview = null;
            }
        },

        handleVideoChange(e) {
            const file = e.target.files ? e.target.files[0] : null;
            if (file) {
                if (file.size > 100 * 1024 * 1024) {
                    alert('⚠️ Ukuran video maksimal 100MB.');
                    e.target.value = '';
                    this.videoFileName = null;
                    return;
                }
                this.videoFileName = file.name;
            } else {
                this.videoFileName = null;
            }
        },

        submitReturnForm() {
            if (!this.activeOrder) return;
            const photoInput = document.getElementById('photo_proof');
            const videoInput = document.getElementById('video_proof');

            if (!photoInput || !photoInput.files || !photoInput.files.length) {
                alert('⚠️ Harap lampirkan Foto Bukti kondisi fisik barang terlebih dahulu.');
                return;
            }
            if (!videoInput || !videoInput.files || !videoInput.files.length) {
                alert('⚠️ Harap lampirkan Video Unboxing paket (Wajib untuk verifikasi garansi toko).');
                return;
            }

            if (this.returnForm.solution_type === 'refund') {
                if (!this.returnForm.bank_account_number || !this.returnForm.bank_account_name) {
                    alert('⚠️ Harap isi Nomor Rekening/HP dan Nama Pemilik Rekening untuk pencairan dana refund.');
                    return;
                }
            }

            this.submittingReturn = true;

            const targetOrderId = this.activeOrder.raw_id || this.activeOrder.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('order_id', targetOrderId);
            formData.append('solution_type', this.returnForm.solution_type || 'refund');
            formData.append('reason', this.returnForm.reason);
            formData.append('description', this.returnForm.description);
            formData.append('bank_name', this.returnForm.bank_name || '');
            formData.append('bank_account_number', this.returnForm.bank_account_number || '');
            formData.append('bank_account_name', this.returnForm.bank_account_name || '');
            formData.append('photo_proof', photoInput.files[0]);
            formData.append('video_proof', videoInput.files[0]);

            fetch("{{ route('orders.return') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(async res => {
                if (res.status === 419) {
                    throw new Error('Sesi halaman Anda telah berakhir (CSRF Token Expired). Silakan refresh/muat ulang halaman web Anda dan coba lagi.');
                }
                const data = await res.json().catch(() => ({}));
                if (!res.ok || data.status === 'error') {
                    const errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Gagal mengirim pengajuan retur.');
                    throw new Error(errorMsg);
                }
                return data;
            })
            .then(data => {
                this.submittingReturn = false;
                this.showReturnModal = false;
                if (this.activeOrder) {
                    this.activeOrder.status = 'retur';
                    this.activeOrder.status_label = 'Dalam Pengajuan Retur / Komplain';
                    this.activeOrder.status_color = 'amber';
                }
                alert('✓ ' + (data.message || 'Pengajuan pengembalian barang berhasil dikirim! Tim layanan pelanggan kami akan meninjau bukti foto & video unboxing Anda maksimal 1x24 jam.'));
                window.location.reload();
            })
            .catch(err => {
                this.submittingReturn = false;
                alert('⚠️ ' + err.message);
            });
        },

        submitReturnTrackingForm() {
            if (!this.activeOrder || !this.returnTrackingForm.number.trim()) {
                alert('⚠️ Harap masukkan Nomor Resi Retur 1 terlebih dahulu.');
                return;
            }

            this.submittingReturnTracking = true;

            const targetOrderId = this.activeOrder.raw_id || this.activeOrder.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('order_id', targetOrderId);
            formData.append('return_tracking_number', this.returnTrackingForm.number.trim());

            const receiptInput = document.getElementById('return_shipping_receipt');
            if (receiptInput && receiptInput.files && receiptInput.files.length > 0) {
                formData.append('return_shipping_receipt', receiptInput.files[0]);
            }

            fetch("{{ route('orders.returnTracking') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok || data.status === 'error') {
                    throw new Error(data.message || 'Gagal menyimpan nomor resi pengembalian.');
                }
                return data;
            })
            .then(data => {
                this.submittingReturnTracking = false;
                alert('✓ ' + (data.message || 'Nomor resi pengembalian berhasil disimpan!'));
                window.location.reload();
            })
            .catch(err => {
                this.submittingReturnTracking = false;
                alert('⚠️ ' + err.message);
            });
        },

        getRatingLabel(rating) {
            const labels = {
                1: 'Sangat Buruk - Tidak Sesuai Harapan',
                2: 'Buruk - Perlu Banyak Perbaikan',
                3: 'Cukup - Standar Produk',
                4: 'Bagus - Memuaskan',
                5: 'Sangat Puas - Sangat Direkomendasikan'
            };
            return labels[rating] || '';
        },

        submitReview() {
            if (!this.activeOrder || !this.selectedReviewItem) return;
            const targetItem = this.selectedReviewItem;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('order_id', this.activeOrder.id);
            formData.append('product_id', targetItem.id || 1);
            formData.append('rating', this.selectedRating);
            formData.append('comment', this.reviewComment);
            formData.append('variant', targetItem.variant || '');
            formData.append('is_anonymous', this.isAnonymous);
            
            const imageInput = document.getElementById('review_image');
            if (imageInput && imageInput.files && imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            }

            fetch("{{ route('orders.review') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            }).then(res => res.json()).then(data => {
                targetItem.is_reviewed = true;
                targetItem.user_rating = this.selectedRating;
                this.showReviewModal = false;
                alert('✓ Ulasan untuk "' + targetItem.name + '" berhasil dikirim!');
            }).catch(err => {
                targetItem.is_reviewed = true;
                targetItem.user_rating = this.selectedRating;
                this.showReviewModal = false;
                alert('✓ Ulasan untuk "' + targetItem.name + '" berhasil dikirim!');
            });
        },

        get filteredOrders() {
            return this.orders.filter(order => {
                let matchesFilter = false;
                if (this.activeFilter === 'semua' || !this.activeFilter) {
                    matchesFilter = true;
                } else if (this.activeFilter === 'belum_bayar' || this.activeFilter === 'belum_dibayar') {
                    matchesFilter = (order.status === 'belum_bayar' || order.status === 'belum_dibayar');
                } else if (this.activeFilter === 'diproses' || this.activeFilter === 'dikemas') {
                    matchesFilter = (order.status === 'diproses' || order.status === 'dikemas');
                } else if (this.activeFilter === 'retur' || this.activeFilter === 'komplain') {
                    matchesFilter = (order.status === 'retur' || order.status === 'retur_proses' || order.status === 'komplain' || order.status === 'retur_disetujui' || order.status === 'retur_ditolak');
                } else if (this.activeFilter === 'batal') {
                    matchesFilter = (order.status === 'batal' || order.status === 'pengajuan_batal');
                } else {
                    matchesFilter = (order.status === this.activeFilter);
                }

                const query = this.searchQuery.toLowerCase().trim();
                const matchesSearch = !query || 
                    String(order.id || '').toLowerCase().includes(query) || 
                    String(order.raw_id || '').toLowerCase().includes(query) || 
                    String(order.items_summary || '').toLowerCase().includes(query);
                return matchesFilter && matchesSearch;
            });
        },

        get activeOrder() {
            if (this.orders && this.orders.length > 0) {
                return this.orders[this.selectedOrderIndex] || this.filteredOrders[0] || this.orders[0];
            }
            return null;
        },

        get hasUnreviewedItems() {
            if (!this.activeOrder || !this.activeOrder.items) return false;
            return this.activeOrder.items.some(item => !item.is_reviewed);
        },

        get countBelumBayar() {
            return (this.orders || []).filter(o => o.status === 'belum_bayar' || o.status === 'belum_dibayar').length;
        },
        get countDiproses() {
            return (this.orders || []).filter(o => o.status === 'diproses' || o.status === 'dikemas').length;
        },
        get countDikirim() {
            return (this.orders || []).filter(o => o.status === 'dikirim').length;
        },
        get countSelesai() {
            return (this.orders || []).filter(o => o.status === 'selesai').length;
        },
        get countRetur() {
            return (this.orders || []).filter(o => o.status === 'retur' || o.status === 'retur_proses' || o.status === 'komplain' || o.status === 'retur_disetujui' || o.status === 'retur_ditolak').length;
        },
        get countBatal() {
            return (this.orders || []).filter(o => o.status === 'batal' || o.status === 'pengajuan_batal').length;
        },

        formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        }
    }
}
</script>
@endpush
@endsection
