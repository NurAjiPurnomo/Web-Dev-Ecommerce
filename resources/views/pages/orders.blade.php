@extends('layouts.app')

@section('content')
<div x-data="orderManagerApp()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    <!-- Page Header & Breadcrumb -->
    <div class="mb-6 space-y-1">
        <nav class="flex text-xs text-slate-500 gap-2 items-center">
            <a href="{{ route('home') }}" class="hover:text-blue-700">Beranda</a>
            <span>/</span>
            <a href="{{ route('profile') }}" class="hover:text-blue-700">Profil Saya</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Manajer Pesanan</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Manajer Pesanan</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500">Kelola, lacak status, dan lihat rincian transaksi belanja Anda</p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium">Total Pesanan:</span>
                <span class="bg-blue-50 text-blue-700 font-bold text-xs px-3 py-1 rounded-full border border-blue-200" x-text="orders.length + ' Transaksi'"></span>
            </div>
        </div>
    </div>

    <!-- MAIN 2-COLUMN SPLIT DASHBOARD LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT PANEL (40% Width / 5 Columns): Order History List -->
        <div class="lg:col-span-5 space-y-4">

            <!-- Filter Status Tabs -->
            <div class="bg-white border border-slate-200 rounded-2xl p-2 shadow-xs">
                <div class="flex items-center overflow-x-auto gap-1 no-scrollbar text-xs">
                    <button 
                        type="button" 
                        @click="activeFilter = 'belum_bayar'; selectedOrderIndex = 0" 
                        :class="activeFilter === 'belum_bayar' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                    >
                        Belum Bayar
                    </button>
                    <button 
                        type="button" 
                        @click="activeFilter = 'diproses'; selectedOrderIndex = 0" 
                        :class="activeFilter === 'diproses' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                    >
                        Diproses
                    </button>
                    <button 
                        type="button" 
                        @click="activeFilter = 'dikirim'; selectedOrderIndex = 0" 
                        :class="activeFilter === 'dikirim' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                    >
                        Dikirim
                    </button>
                    <button 
                        type="button" 
                        @click="activeFilter = 'selesai'; selectedOrderIndex = 0" 
                        :class="activeFilter === 'selesai' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                    >
                        Selesai
                    </button>
                    <button 
                        type="button" 
                        @click="activeFilter = 'batal'; selectedOrderIndex = 0" 
                        :class="activeFilter === 'batal' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                    >
                        Dibatalkan
                    </button>
                </div>
            </div>

            <!-- Search Input inside Left Panel -->
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Cari No. Invoice / Nama Barang..." 
                    class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 shadow-2xs"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Vertically Scrollable List of Order Cards -->
            <div class="space-y-3 max-h-[calc(100vh-220px)] overflow-y-auto pr-1">
                <template x-for="(order, idx) in filteredOrders" :key="idx + '_' + (order.id || order.raw_id || idx)">
                    <div 
                        @click="selectedOrderIndex = idx"
                        :class="selectedOrderIndex === idx ? 'border-blue-600 ring-2 ring-blue-600/20 bg-blue-50/40 shadow-sm' : 'border-slate-200 bg-white hover:bg-slate-50/80 shadow-2xs'"
                        class="border rounded-2xl p-4 transition-all cursor-pointer space-y-3 relative group"
                    >
                        <!-- Card Header: ID & Status Badge -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-xs sm:text-sm" x-text="order.id"></span>
                                <span class="text-[11px] text-slate-400">•</span>
                                <span class="text-[11px] text-slate-500" x-text="order.date"></span>
                            </div>
                            
                            <!-- Status Badges -->
                            <template x-if="order.status === 'belum_bayar' || order.status === 'belum_dibayar'">
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border border-amber-200">
                                    Belum Bayar
                                </span>
                            </template>
                            <template x-if="order.status === 'diproses'">
                                <span class="bg-yellow-100 text-yellow-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border border-yellow-200">
                                    Diproses
                                </span>
                            </template>
                            <template x-if="order.status === 'dikirim'">
                                <span class="bg-blue-100 text-blue-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border border-blue-200 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                    <span>Dikirim</span>
                                </span>
                            </template>
                            <template x-if="order.status === 'selesai'">
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border border-emerald-200">
                                    Selesai
                                </span>
                            </template>
                            <template x-if="order.status === 'batal'">
                                <span class="bg-red-100 text-red-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border border-red-200">
                                    Dibatalkan
                                </span>
                            </template>
                        </div>

                        <!-- Card Body: Summary -->
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-slate-800 line-clamp-1" x-text="order.items_summary"></p>
                            <div class="flex items-center justify-between text-xs pt-1">
                                <span class="text-slate-500">Total Tagihan:</span>
                                <span class="font-extrabold text-slate-900 text-sm text-blue-700" x-text="formatRupiah(order.total_amount)"></span>
                            </div>
                        </div>

                        <!-- Chevron Indicator -->
                        <div class="flex justify-end pt-1">
                            <span class="text-[11px] font-bold text-blue-700 group-hover:underline flex items-center gap-1">
                                <span>Lihat Rincian</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="filteredOrders.length === 0">
                    <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-slate-700">Tidak ada pesanan ditemukan</p>
                        <p class="text-xs text-slate-400">Coba pilih tab filter atau kata kunci pencarian lainnya.</p>
                    </div>
                </template>
            </div>

        </div>

        <!-- RIGHT PANEL (60% Width / 7 Columns): Expanded Order Details (Sticky on Scroll) -->
        <div class="lg:col-span-7 sticky top-20">
            <template x-if="activeOrder">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm space-y-6">

                    <!-- 1. Header: Order ID, Timestamp & Action Menu -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base sm:text-lg font-extrabold text-slate-900" x-text="activeOrder.id"></h2>
                                <span class="text-xs text-slate-400">|</span>
                                <span class="text-xs text-slate-500 font-mono" x-text="activeOrder.raw_id"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Waktu Transaksi: <span x-text="activeOrder.date"></span></p>
                        </div>

                        <!-- Action Menu (Three Dots) -->
                        <div class="relative" x-data="{ openMenu: false }">
                            <button 
                                type="button" 
                                @click="openMenu = !openMenu" 
                                @click.outside="openMenu = false"
                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors cursor-pointer"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>

                            <div 
                                x-show="openMenu" 
                                x-transition 
                                class="absolute right-0 mt-2 w-44 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-50 text-xs space-y-0.5" 
                                style="display: none;"
                            >
                                <a href="#" @click.prevent="alert('Salin No. Resi: ' + activeOrder.courier.resi)" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Salin Nomor Resi</a>
                                <a href="#" @click.prevent="window.print()" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Cetak Halaman ini</a>
                                <a href="#" @click.prevent="alert('Pusat Bantuan Toko Online aktif 24 jam')" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Bantuan Pesanan</a>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Visual Tracking: Horizontal Step-by-step Progress Bar -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                            <span>Status Lacak Pengiriman</span>
                            <span class="text-blue-700" x-text="activeOrder.status_label"></span>
                        </div>

                        <!-- Progress Steps -->
                        <div class="grid grid-cols-4 gap-2 relative text-center pt-2">
                            
                            <!-- Step 1: Menunggu Konfirmasi -->
                            <div class="space-y-1.5 relative z-10">
                                <div 
                                    :class="activeOrder.step >= 1 ? 'text-blue-700' : 'text-slate-400'"
                                    class="w-10 h-10 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-2"
                                    :class="activeOrder.step >= 1 ? 'border-blue-700' : 'border-slate-300'"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-[11px] font-semibold leading-tight" :class="activeOrder.step >= 1 ? 'text-slate-900' : 'text-slate-400'">Menunggu<br>Konfirmasi</p>
                            </div>

                            <!-- Step 2: Pesanan Diproses -->
                            <div class="space-y-1.5 relative z-10">
                                <div 
                                    :class="activeOrder.step >= 2 ? 'text-blue-700' : 'text-slate-400'"
                                    class="w-10 h-10 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-2"
                                    :class="activeOrder.step >= 2 ? 'border-blue-700' : 'border-slate-300'"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </div>
                                <p class="text-[11px] font-semibold leading-tight" :class="activeOrder.step >= 2 ? 'text-slate-900' : 'text-slate-400'">Pesanan<br>Diproses</p>
                            </div>

                            <!-- Step 3: Sedang Dikirim -->
                            <div class="space-y-1.5 relative z-10">
                                <div 
                                    :class="activeOrder.step >= 3 ? 'text-blue-700' : 'text-slate-400'"
                                    class="w-10 h-10 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-2"
                                    :class="activeOrder.step >= 3 ? 'border-blue-700' : 'border-slate-300'"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                </div>
                                <p class="text-[11px] font-semibold leading-tight" :class="activeOrder.step >= 3 ? 'text-slate-900' : 'text-slate-400'">Sedang<br>Dikirim</p>
                            </div>

                            <!-- Step 4: Sampai Tujuan -->
                            <div class="space-y-1.5 relative z-10">
                                <div 
                                    :class="activeOrder.step >= 4 ? 'text-blue-700' : 'text-slate-400'"
                                    class="w-10 h-10 mx-auto flex items-center justify-center transition-all bg-white rounded-full border-2"
                                    :class="activeOrder.step >= 4 ? 'border-blue-700' : 'border-slate-300'"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-[11px] font-semibold leading-tight" :class="activeOrder.step >= 4 ? 'text-blue-700 font-bold' : 'text-slate-400'">Sampai<br>Tujuan</p>
                            </div>

                        </div>
                    </div>

                    <!-- SHOPEE-STYLE GRANULAR TRACKING TIMELINE CARD -->
                    <template x-if="activeOrder.tracking_data">
                        <div class="bg-blue-50/60 border border-blue-200/80 rounded-2xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-blue-700 animate-pulse"></div>
                                    <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider" x-text="activeOrder.tracking_data.status_title"></h4>
                                </div>
                                <button 
                                    type="button" 
                                    @click="showTrackingModal = true" 
                                    class="text-[11px] font-bold text-blue-700 hover:underline flex items-center gap-1 cursor-pointer"
                                >
                                    <span>Lihat Rincian Lacak →</span>
                                </button>
                            </div>
                            <p class="text-xs text-slate-600 leading-snug" x-text="activeOrder.tracking_data.status_desc"></p>

                            <!-- Vertical Micro Timeline -->
                            <div class="pt-2 border-t border-blue-200/60 space-y-3 relative pl-4 before:content-[''] before:absolute before:left-1.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-blue-200">
                                <template x-for="(step, sIdx) in activeOrder.tracking_data.timeline" :key="sIdx">
                                    <div class="relative flex items-start justify-between text-xs gap-3">
                                        <div 
                                            :class="step.current ? 'bg-blue-700 ring-4 ring-blue-100' : (step.completed ? 'bg-blue-500' : 'bg-slate-300')" 
                                            class="w-3 h-3 rounded-full absolute -left-4 top-1 border-2 border-white transition-all shrink-0"
                                        ></div>
                                        <div class="space-y-0.5 min-w-0 flex-1">
                                            <h5 class="font-extrabold text-xs" :class="step.current ? 'text-blue-900' : (step.completed ? 'text-slate-800' : 'text-slate-400')" x-text="step.title"></h5>
                                            <p class="text-[11px] text-slate-500 leading-tight" x-text="step.desc"></p>
                                        </div>
                                        <span class="text-[10px] font-mono text-slate-400 whitespace-nowrap shrink-0" x-text="step.time"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- 3. Ordered Items List (Ulasan Per Masing-Masing Produk) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Produk Yang Dibeli</h3>
                            <span class="text-[11px] text-slate-500" x-text="activeOrder.items.length + ' Barang'"></span>
                        </div>

                        <div class="divide-y divide-slate-100 border border-slate-200/80 rounded-2xl p-2 bg-slate-50/50 space-y-1">
                            <template x-for="item in activeOrder.items" :key="item.name">
                                <div class="p-3 bg-white rounded-xl border border-slate-100 space-y-3">
                                    <div class="flex items-start gap-3">
                                        <img :src="item.image" :alt="item.name" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0">
                                        <div class="flex-1 space-y-0.5">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug" x-text="item.name"></h4>
                                            <p class="text-[11px] text-slate-500" x-text="item.variant"></p>
                                            <div class="flex items-center justify-between text-xs pt-1">
                                                <span class="text-slate-600" x-text="item.qty + ' x ' + formatRupiah(item.price)"></span>
                                                <span class="font-extrabold text-slate-900" x-text="formatRupiah(item.subtotal)"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Penilaian Per Produk Khusus Status Selesai -->
                                    <template x-if="activeOrder.status === 'selesai'">
                                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                            <template x-if="!item.is_reviewed">
                                                <div class="flex items-center justify-between w-full">
                                                    <span class="text-[11px] text-slate-500">Belum diulas</span>
                                                    <button 
                                                        type="button" 
                                                        @click="openReviewModal(item)"
                                                        class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs px-3.5 py-1.5 rounded-lg border border-blue-200 transition-colors cursor-pointer flex items-center gap-1.5"
                                                    >
                                                        <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        <span>Beri Ulasan Produk ini</span>
                                                    </button>
                                                </div>
                                            </template>

                                            <template x-if="item.is_reviewed">
                                                <div class="flex items-center justify-between w-full bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg text-emerald-800 font-bold text-[11px]">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        <span>Sudah Diulas</span>
                                                    </span>
                                                    <span class="text-amber-500 font-bold" x-text="'★ ' + (item.user_rating || 5) + '.0'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 4. Delivery & Address Information Section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 text-xs">
                        
                        <!-- Customer Address -->
                        <div class="space-y-1 bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                            <p class="font-bold text-slate-900 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                <span>Alamat Pengiriman</span>
                            </p>
                            <p class="font-bold text-slate-800" x-text="activeOrder.recipient.name + ' (' + activeOrder.recipient.phone + ')'"></p>
                            <p class="text-slate-600 text-[11px] leading-relaxed" x-text="activeOrder.recipient.address + ', ' + activeOrder.recipient.city"></p>
                        </div>

                        <!-- Courier Info (Shopee Style Status Flow) -->
                        <div class="space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-200/60 flex flex-col justify-between">
                            <div>
                                <p class="font-bold text-slate-900 flex items-center justify-between border-b border-slate-200/60 pb-2">
                                    <span class="flex items-center gap-1.5 text-xs">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        <span>Jasa Kurir Pengiriman</span>
                                    </span>
                                    <span class="text-xs font-extrabold text-blue-700" x-text="activeOrder.courier.name"></span>
                                </p>

                                <!-- STATUS 1: BELUM BAYAR -->
                                <template x-if="activeOrder.status === 'belum_bayar' || activeOrder.status === 'belum_dibayar'">
                                    <div class="mt-2.5 p-3.5 bg-amber-50/90 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-1.5">
                                        <div class="flex items-center justify-between border-b border-amber-200/60 pb-1.5">
                                            <div class="font-bold flex items-center gap-1.5 text-amber-800">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Batas Waktu Pembayaran</span>
                                            </div>
                                            <div class="font-mono text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 px-2 py-0.5 rounded-md" x-text="getRemainingTime(activeOrder.created_at_timestamp)"></div>
                                        </div>
                                        <p class="text-[11px] text-amber-700 leading-relaxed">Selesaikan pembayaran sebelum batas waktu di atas berakhir agar pesanan tidak dibatalkan secara otomatis.</p>
                                    </div>
                                </template>

                                <!-- STATUS 2: DIPROSES / DIKEMAS -->
                                <template x-if="activeOrder.status === 'diproses' || activeOrder.status === 'dikemas'">
                                    <div class="mt-2.5 p-3 bg-blue-50/80 border border-blue-200 rounded-lg text-xs text-blue-900 space-y-1">
                                        <div class="font-bold flex items-center gap-1 text-blue-800">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span>Sedang Dikemas Penjual</span>
                                        </div>
                                        <p class="text-[11px] text-blue-700 leading-relaxed">Penjual sedang menyiapkan barang Anda. Nomor resi akan terbit otomatis saat paket diserahkan ke pihak ekspedisi.</p>
                                    </div>
                                </template>

                                <!-- STATUS 3 & 4: DIKIRIM ATAU SELESAI -->
                                <template x-if="activeOrder.status === 'dikirim' || activeOrder.status === 'selesai'">
                                    <div class="mt-2 space-y-1.5 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-500">Nomor Resi:</span>
                                            <span class="font-mono font-bold text-slate-900 bg-white border border-slate-200 px-2.5 py-0.5 rounded text-xs" x-text="activeOrder.courier.resi"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-slate-500">Estimasi Pengiriman:</span>
                                            <span class="font-medium text-emerald-700" x-text="activeOrder.courier.etd"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- TOMBOL LACAK HANYA MUNCUL JIKA STATUS DIKIRIM ATAU SELESAI -->
                            <template x-if="activeOrder.status === 'dikirim' || activeOrder.status === 'selesai'">
                                <button 
                                    type="button" 
                                    @click="showTrackingModal = true"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2.5 rounded-xl transition-colors cursor-pointer text-center mt-3 flex items-center justify-center gap-1.5 shadow-2xs"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    <span>Lacak Paket Pengiriman</span>
                                </button>
                            </template>
                        </div>

                    </div>

                    <!-- 5. Price Breakdown -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 space-y-2 text-xs">
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
                            <span>Biaya Layanan</span>
                            <span class="font-bold text-emerald-600">GRATIS</span>
                        </div>

                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-extrabold text-slate-900">Grand Total Tagihan</span>
                            <span class="text-base sm:text-xl font-black text-blue-700" x-text="formatRupiah(activeOrder.total_amount)"></span>
                        </div>
                    </div>


                    <!-- 6. Action Buttons at Bottom (SHOPEE STYLE STATUS FLOW) -->
                    <div class="flex flex-col sm:flex-row items-center gap-2 pt-2">
                        <!-- STATUS 1: BELUM BAYAR -->
                        <template x-if="activeOrder.status === 'belum_bayar' || activeOrder.status === 'belum_dibayar'">
                            <div class="flex flex-col sm:flex-row items-center gap-2 w-full">
                                <a 
                                    :href="'{{ route('checkout.success') }}?order_id=' + activeOrder.id"
                                    class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3.5 rounded-xl transition-all shadow-xs text-center flex items-center justify-center gap-1.5"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <span>Selesaikan Pembayaran</span>
                                </a>

                                <button 
                                    type="button" 
                                    @click="window.print()"
                                    class="w-full sm:w-auto bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold text-xs px-4 py-3.5 rounded-xl transition-colors cursor-pointer text-center"
                                >
                                    Unduh Invoice
                                </button>
                            </div>
                        </template>

                        <!-- STATUS 2: DIPROSES / DIKEMAS -->
                        <template x-if="activeOrder.status === 'diproses' || activeOrder.status === 'dikemas'">
                            <div class="flex flex-col sm:flex-row items-center gap-2 w-full">
                                <button 
                                    type="button" 
                                    @click="alert('Menghubungi Penjual untuk Pesanan: ' + activeOrder.id)"
                                    class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3.5 rounded-xl transition-all shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>Hubungi Penjual</span>
                                </button>

                                <button 
                                    type="button" 
                                    @click="window.print()"
                                    class="w-full sm:w-auto bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold text-xs px-4 py-3.5 rounded-xl transition-colors cursor-pointer text-center"
                                >
                                    Unduh Invoice
                                </button>
                            </div>
                        </template>

                        <!-- STATUS 3: DIKIRIM -->
                        <template x-if="activeOrder.status === 'dikirim'">
                            <div class="flex flex-col sm:flex-row items-center gap-2 w-full">
                                <button 
                                    type="button" 
                                    @click="showTrackingModal = true"
                                    class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3.5 rounded-xl transition-all shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    <span>Lacak Pengiriman</span>
                                </button>

                                <button 
                                    type="button" 
                                    @click="alert('Terima kasih! Status pesanan diperbarui menjadi Selesai.')"
                                    class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-3.5 rounded-xl transition-colors cursor-pointer text-center"
                                >
                                    Pesanan Diterima
                                </button>
                            </div>
                        </template>

                        <!-- STATUS 4: SELESAI -->
                        <template x-if="activeOrder.status === 'selesai'">
                            <div class="flex flex-col sm:flex-row items-center gap-2 w-full">
                                <template x-if="hasUnreviewedItems">
                                    <button 
                                        type="button" 
                                        @click="openReviewModal()"
                                        class="w-full sm:flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs py-3.5 rounded-xl transition-all shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Beri Penilaian</span>
                                    </button>
                                </template>

                                <a 
                                    href="{{ route('catalog') }}"
                                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-3.5 rounded-xl transition-colors text-center cursor-pointer"
                                >
                                    Beli Lagi
                                </a>
                            </div>
                        </template>

                        <!-- STATUS 5: BATAL -->
                        <template x-if="activeOrder.status === 'batal'">
                            <div class="flex flex-col sm:flex-row items-center gap-2 w-full">
                                <div class="flex-1 bg-red-50 text-red-800 text-xs font-medium px-4 py-3 rounded-xl border border-red-200 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Dibatalkan oleh sistem (Batas waktu pembayaran habis).</span>
                                </div>
                                <a 
                                    href="{{ route('catalog') }}"
                                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-3 rounded-xl transition-colors text-center cursor-pointer flex items-center justify-center gap-1.5"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Beli Lagi
                                </a>
                            </div>
                        </template>
                    </div>

                </div>
            </template>
        </div>

    </div>

    <!-- MODAL RATING & ULASAN PRODUK (ENTERPRISE GRADE) -->
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
                <div class="text-center space-y-2 py-3 bg-amber-50/40 rounded-xl border border-amber-200/60 p-4">
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Kualitas Produk</label>
                    <div class="flex items-center justify-center gap-2">
                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                            <button 
                                type="button" 
                                @click="selectedRating = star"
                                class="p-1 transition-transform hover:scale-125 focus:outline-none cursor-pointer"
                            >
                                <svg class="w-8 h-8 sm:w-9 sm:h-9" :class="star <= selectedRating ? 'text-amber-400 fill-amber-400' : 'text-slate-200 fill-slate-200'" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                    <p class="text-xs font-bold text-amber-700" x-text="getRatingLabel(selectedRating)"></p>
                </div>

                <!-- Quick Chips Recommendations -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500">Pilih Catatan Ulasan Cepat (Opsional):</label>
                    <div class="flex flex-wrap gap-1.5 text-xs">
                        <button type="button" @click="addChip('Kualitas produk sangat bagus.')" class="bg-slate-50 hover:bg-amber-50 hover:text-amber-800 text-slate-700 font-medium px-2.5 py-1 rounded-lg transition-colors border border-slate-200 text-[11px] cursor-pointer">+ Kualitas Sangat Bagus</button>
                        <button type="button" @click="addChip('Pengiriman sangat cepat.')" class="bg-slate-50 hover:bg-amber-50 hover:text-amber-800 text-slate-700 font-medium px-2.5 py-1 rounded-lg transition-colors border border-slate-200 text-[11px] cursor-pointer">+ Pengiriman Cepat</button>
                        <button type="button" @click="addChip('Respon toko cepat & ramah.')" class="bg-slate-50 hover:bg-amber-50 hover:text-amber-800 text-slate-700 font-medium px-2.5 py-1 rounded-lg transition-colors border border-slate-200 text-[11px] cursor-pointer">+ Pelayanan Ramah</button>
                        <button type="button" @click="addChip('Produk 100% sesuai deskripsi.')" class="bg-slate-50 hover:bg-amber-50 hover:text-amber-800 text-slate-700 font-medium px-2.5 py-1 rounded-lg transition-colors border border-slate-200 text-[11px] cursor-pointer">+ Sesuai Deskripsi</button>
                    </div>
                </div>

                <!-- Comment Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tuliskan Ulasan Detail (Opsional)</label>
                    <textarea 
                        x-model="reviewComment" 
                        rows="3" 
                        placeholder="Bagikan ulasan mengenai kualitas bahan, kesesuaian ukuran, atau kecepatan pengiriman..." 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-medium text-slate-900 focus:bg-white focus:border-amber-500 focus:outline-none"
                    ></textarea>
                </div>

                <!-- Photo Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unggah Foto Produk (Opsional)</label>
                    <input 
                        type="file" 
                        id="review_image"
                        accept="image/*"
                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                    >
                </div>

                <!-- Anonymous Checkbox -->
                <div class="flex items-center gap-2 text-xs text-slate-700">
                    <input type="checkbox" id="anon" x-model="isAnonymous" class="rounded text-amber-500 focus:ring-amber-500 cursor-pointer">
                    <label for="anon" class="cursor-pointer">Kirim ulasan secara anonim</label>
                </div>

                <!-- Action Buttons -->
                <div class="pt-2 flex gap-2">
                    <button type="button" @click="showReviewModal = false" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-3 rounded-xl transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="w-2/3 bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs py-3 rounded-xl transition-all shadow-md cursor-pointer flex items-center justify-center gap-1.5">
                        <span>Kirim Ulasan Produk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL LACAK DETAIL PENGIRIMAN SHOPEE-STYLE -->
    <div 
        x-show="showTrackingModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <template x-if="activeOrder">
            <div @click.outside="showTrackingModal = false" class="bg-white border border-slate-200 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl space-y-0">
                <!-- Modal Header -->
                <div class="bg-blue-700 p-5 text-white flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="bg-white/20 text-white font-extrabold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider">LACAK EXPEDISI LIVE</span>
                            <span class="text-xs text-blue-200" x-text="activeOrder.courier.name"></span>
                        </div>
                        <h3 class="font-extrabold text-base mt-1" x-text="'No. Resi: ' + activeOrder.courier.resi"></h3>
                    </div>
                    <button type="button" @click="showTrackingModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white font-bold flex items-center justify-center cursor-pointer transition-colors">✕</button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    <!-- Status Header Info -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Terkini:</span>
                            <span class="text-xs font-bold text-blue-700" x-text="activeOrder.courier.etd"></span>
                        </div>
                        <h4 class="font-black text-slate-900 text-sm" x-text="activeOrder.tracking_data ? activeOrder.tracking_data.status_title : activeOrder.status_label"></h4>
                        <p class="text-xs text-slate-600 leading-relaxed" x-text="activeOrder.tracking_data ? activeOrder.tracking_data.status_desc : ''"></p>
                    </div>

                    <!-- Courier & Recipient Details -->
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-xl space-y-0.5">
                            <span class="text-slate-400 font-bold block text-[10px] uppercase">Kurir Pengirim:</span>
                            <span class="font-extrabold text-slate-900 block" x-text="activeOrder.courier.driver"></span>
                        </div>
                        <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-xl space-y-0.5">
                            <span class="text-slate-400 font-bold block text-[10px] uppercase">Tujuan Pengiriman:</span>
                            <span class="font-extrabold text-slate-900 block truncate" x-text="activeOrder.recipient.name"></span>
                        </div>
                    </div>

                    <!-- Granular Timeline Track -->
                    <div class="space-y-3 pt-2">
                        <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <div class="w-1.5 h-4 bg-blue-700 rounded-full"></div>
                            <span>Linimasa Perjalanan Paket</span>
                        </h4>

                        <template x-if="activeOrder.tracking_data">
                            <div class="relative pl-6 space-y-5 before:content-[''] before:absolute before:left-2.5 before:top-2.5 before:bottom-2.5 before:w-0.5 before:bg-slate-200">
                                <template x-for="(step, sIdx) in activeOrder.tracking_data.timeline" :key="sIdx">
                                    <div class="relative space-y-0.5 text-xs">
                                        <div 
                                            :class="step.current ? 'bg-blue-700 ring-4 ring-blue-100' : (step.completed ? 'bg-blue-600' : 'bg-slate-300')" 
                                            class="w-3.5 h-3.5 rounded-full absolute -left-6 top-0.5 border-2 border-white transition-all shrink-0"
                                        ></div>
                                        <div class="flex items-center justify-between">
                                            <h5 class="font-extrabold text-xs" :class="step.current ? 'text-blue-700 font-black' : (step.completed ? 'text-slate-900' : 'text-slate-400')" x-text="step.title"></h5>
                                            <span class="text-[10px] font-mono text-slate-400" x-text="step.time"></span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-snug" x-text="step.desc"></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <button 
                        type="button"
                        @click="navigator.clipboard.writeText(activeOrder.courier.resi); alert('✓ Nomor Resi ' + activeOrder.courier.resi + ' berhasil disalin!')"
                        class="text-xs font-bold text-blue-700 hover:text-blue-800 bg-blue-50 border border-blue-200 px-3.5 py-2 rounded-xl transition-colors cursor-pointer flex items-center gap-1.5"
                    >
                        📋 Salin Resi
                    </button>
                    <button 
                        type="button" 
                        @click="showTrackingModal = false"
                        class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl cursor-pointer transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </template>
    </div>

</div>

@push('scripts')
<script>
function orderManagerApp() {
    return {
        orders: @json($orders),
        selectedOrderIndex: 0,
        activeFilter: 'belum_bayar',
        searchQuery: '',
        showReviewModal: false,
        showTrackingModal: false,
        selectedRating: 5,
        reviewComment: '',
        isAnonymous: false,
        dismissedReviews: {},
        nowTimestamp: Math.floor(Date.now() / 1000),

        init() {
            setInterval(() => {
                this.nowTimestamp = Math.floor(Date.now() / 1000);
            }, 1000);
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
            
            const formData = new FormData();
            formData.append('order_id', this.activeOrder.id);
            formData.append('product_id', targetItem.id || 1);
            formData.append('rating', this.selectedRating);
            formData.append('comment', this.reviewComment);
            formData.append('is_anonymous', this.isAnonymous);
            
            const imageInput = document.getElementById('review_image');
            if (imageInput && imageInput.files && imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            }

            fetch("{{ route('orders.review') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                if (this.activeFilter === 'belum_bayar' || this.activeFilter === 'belum_dibayar') {
                    matchesFilter = (order.status === 'belum_bayar' || order.status === 'belum_dibayar');
                } else if (this.activeFilter === 'diproses' || this.activeFilter === 'dikemas') {
                    matchesFilter = (order.status === 'diproses' || order.status === 'dikemas');
                } else if (this.activeFilter === 'batal') {
                    matchesFilter = (order.status === 'batal');
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
            if (this.filteredOrders.length > 0) {
                return this.filteredOrders[this.selectedOrderIndex] || this.filteredOrders[0];
            }
            return null;
        },

        get hasUnreviewedItems() {
            if (!this.activeOrder || !this.activeOrder.items) return false;
            return this.activeOrder.items.some(item => !item.is_reviewed);
        },

        formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        }
    }
}
</script>
@endpush
@endsection
