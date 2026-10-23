@extends('admin.layout')

@section('title', 'Dashboard Toko')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pantau ringkasan penjualan harian, operasional pesanan, dan inventaris toko.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('admin.products') }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-xl transition-colors shadow-xs cursor-pointer"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Produk</span>
            </a>
        </div>
    </div>

    <!-- LEVEL 1: Action Center / Perlu Diproses Hari Ini (Standar Shopee / Tokopedia Seller Centre) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs">
        <div class="flex items-center justify-between pb-3.5 mb-3.5 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Pusat Tindakan Toko</h2>
                <p class="text-xs text-slate-500 mt-0.5">Operasional pesanan dan inventaris yang membutuhkan penanganan Anda hari ini.</p>
            </div>
            <a href="{{ route('admin.orders') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
                <span>Semua Pesanan</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <!-- 1. Perlu Dikemas -->
            <a 
                href="{{ route('admin.orders') }}?status=diproses" 
                class="p-4 rounded-xl border transition-all text-center group {{ $tasks['to_ship'] > 0 ? 'bg-blue-50/40 border-blue-200 hover:border-blue-400 hover:bg-blue-50/70' : 'bg-slate-50/40 border-slate-200 hover:bg-slate-50' }}"
            >
                <div class="text-2xl font-bold tracking-tight {{ $tasks['to_ship'] > 0 ? 'text-blue-700' : 'text-slate-800' }}">
                    {{ $tasks['to_ship'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Perlu Dikemas</div>
                @if($tasks['to_ship'] > 0)
                    <div class="text-[11px] text-blue-600 font-semibold mt-1">Siap dikirim &rarr;</div>
                @else
                    <div class="text-[11px] text-slate-400 mt-1">Tidak ada antrean</div>
                @endif
            </a>

            <!-- 2. Belum Bayar -->
            <a 
                href="{{ route('admin.orders') }}?status=belum_dibayar" 
                class="p-4 rounded-xl border transition-all text-center group {{ $tasks['unpaid'] > 0 ? 'bg-amber-50/40 border-amber-200 hover:border-amber-400 hover:bg-amber-50/70' : 'bg-slate-50/40 border-slate-200 hover:bg-slate-50' }}"
            >
                <div class="text-2xl font-bold tracking-tight {{ $tasks['unpaid'] > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                    {{ $tasks['unpaid'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Menunggu Bayar</div>
                <div class="text-[11px] text-slate-400 mt-1">Menunggu pembeli</div>
            </a>

            <!-- 3. Sedang Dikirim -->
            <a 
                href="{{ route('admin.orders') }}?status=dikirim" 
                class="p-4 rounded-xl border border-slate-200 bg-slate-50/40 hover:bg-slate-50 hover:border-slate-300 transition-all text-center group"
            >
                <div class="text-2xl font-bold tracking-tight text-slate-800">
                    {{ $tasks['shipping'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Dalam Pengiriman</div>
                <div class="text-[11px] text-slate-400 mt-1">Bersama kurir</div>
            </a>

            <!-- 4. Komplain / Retur -->
            <a 
                href="{{ route('admin.returns') }}" 
                class="p-4 rounded-xl border transition-all text-center group {{ $tasks['returns'] > 0 ? 'bg-rose-50/40 border-rose-300 hover:border-rose-400' : 'bg-slate-50/40 border-slate-200 hover:bg-slate-50' }}"
            >
                <div class="text-2xl font-bold tracking-tight {{ $tasks['returns'] > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                    {{ $tasks['returns'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Komplain / Retur</div>
                @if($tasks['returns'] > 0)
                    <div class="text-[11px] text-rose-600 font-semibold mt-1">Perlu respon!</div>
                @else
                    <div class="text-[11px] text-slate-400 mt-1">Kondisi aman</div>
                @endif
            </a>

            <!-- 5. Stok Kritis -->
            <a 
                href="{{ route('admin.products') }}" 
                class="p-4 rounded-xl border transition-all text-center group {{ $tasks['low_stock'] > 0 ? 'bg-orange-50/40 border-orange-200 hover:border-orange-400' : 'bg-slate-50/40 border-slate-200 hover:bg-slate-50' }}"
            >
                <div class="text-2xl font-bold tracking-tight {{ $tasks['low_stock'] > 0 ? 'text-orange-600' : 'text-slate-800' }}">
                    {{ $tasks['low_stock'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Stok Menipis</div>
                <div class="text-[11px] text-slate-400 mt-1">&le; 5 unit tersisa</div>
            </a>

            <!-- 6. Pesanan Selesai -->
            <a 
                href="{{ route('admin.orders') }}?status=selesai" 
                class="p-4 rounded-xl border border-slate-200 bg-slate-50/40 hover:bg-slate-50 hover:border-slate-300 transition-all text-center group"
            >
                <div class="text-2xl font-bold tracking-tight text-emerald-700">
                    {{ $tasks['completed'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Pesanan Selesai</div>
                <div class="text-[11px] text-slate-400 mt-1">Transaksi sukses</div>
            </a>
        </div>
    </div>

    <!-- LEVEL 2: Metrik Finansial Utama (KPI) + Filter Waktu Real-Time -->
    <div class="space-y-3.5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Performa Penjualan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Analisis pendapatan kotor, volume transaksi, dan rata-rata belanja.</p>
            </div>

            <!-- Filter Periode Waktu -->
            <div class="inline-flex bg-slate-100 p-1 rounded-xl text-xs font-medium shrink-0">
                <a 
                    href="{{ route('admin.dashboard', ['period' => 'all']) }}" 
                    class="px-3 py-1.5 rounded-lg transition-colors {{ $selectedPeriod === 'all' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Semua
                </a>
                <a 
                    href="{{ route('admin.dashboard', ['period' => 'today']) }}" 
                    class="px-3 py-1.5 rounded-lg transition-colors {{ $selectedPeriod === 'today' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Hari Ini
                </a>
                <a 
                    href="{{ route('admin.dashboard', ['period' => '7days']) }}" 
                    class="px-3 py-1.5 rounded-lg transition-colors {{ $selectedPeriod === '7days' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    7 Hari Terakhir
                </a>
                <a 
                    href="{{ route('admin.dashboard', ['period' => 'this_month']) }}" 
                    class="px-3 py-1.5 rounded-lg transition-colors {{ $selectedPeriod === 'this_month' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Bulan Ini
                </a>
            </div>
        </div>

        <!-- 4 KPI Cards (Bersih, Rapi, Tanpa Kotak Ganda di Dalam Kotak) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Penjualan Kotor -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Total Penjualan</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        Rp {{ number_format($stats['gross_revenue'], 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-400 mt-1">
                        @if($stats['gross_revenue'] > 0)
                            Subtotal produk: Rp {{ number_format($stats['subtotal_revenue'] ?? 0, 0, ',', '.') }}
                        @else
                            Belum ada transaksi
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Pesanan Berhasil -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Pesanan Berhasil</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        {{ number_format($stats['total_orders'], 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">pesanan</span>
                    </div>
                    <div class="text-xs text-slate-400 mt-1">
                        @if($stats['total_orders'] > 0)
                            Rata-rata: Rp {{ number_format($stats['avg_order_val'], 0, ',', '.') }}
                        @else
                            Belum ada pesanan
                        @endif
                    </div>
                </div>
            </div>

            <!-- 3. Unit Produk Terjual -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Produk Terjual</span>
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        {{ number_format($stats['items_sold'], 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">pcs</span>
                    </div>
                    <div class="text-xs text-slate-400 mt-1">
                        Dari {{ $stats['total_products'] }} produk aktif
                    </div>
                </div>
            </div>

            <!-- 4. Pelanggan Terdaftar -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Pelanggan Terdaftar</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        {{ number_format($stats['total_customers'], 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">akun</span>
                    </div>
                    <div class="text-xs text-slate-400 mt-1">
                        Total pembeli terdaftar
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LEVEL 3: Grafik Tren Penjualan & Distribusi Kategori -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kiri: Grafik Omset Bulanan dengan Sumbu Nominal (7 Kolom) -->
        @php
            $maxMonthlyGross = max(array_column($monthlyTrend, 'gross') ?: [1]);
            if ($maxMonthlyGross <= 0) $maxMonthlyGross = 1000000;
            // Buat tick sumbu Y yang proporsional
            $stepY = ceil($maxMonthlyGross / 4 / 100000) * 100000;
            if ($stepY <= 0) $stepY = 250000;
            $scaleMax = $stepY * 4;
            $currentMonthNum = (int)date('n');
        @endphp
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-semibold text-slate-900">Grafik Omset Bulanan ({{ date('Y') }})</h3>
                    <span class="text-xs font-medium text-slate-500">Total Tahun Ini: <strong class="text-slate-900">Rp {{ number_format(array_sum(array_column($monthlyTrend, 'gross')), 0, ',', '.') }}</strong></span>
                </div>
                <p class="text-xs text-slate-500 mb-6">Dinamika perputaran omset toko dari Januari hingga Desember.</p>

                <!-- Chart Container with Y-Axis Gridlines -->
                <div class="relative pt-2">
                    <!-- Gridlines Nominal Sumbu Y -->
                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-7">
                        <div class="border-b border-dashed border-slate-200/80 flex items-center justify-between text-[10px] text-slate-400 font-mono">
                            <span>Rp {{ number_format($scaleMax, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-b border-dashed border-slate-200/80 flex items-center justify-between text-[10px] text-slate-400 font-mono">
                            <span>Rp {{ number_format($scaleMax * 0.75, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-b border-dashed border-slate-200/80 flex items-center justify-between text-[10px] text-slate-400 font-mono">
                            <span>Rp {{ number_format($scaleMax * 0.5, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-b border-dashed border-slate-200/80 flex items-center justify-between text-[10px] text-slate-400 font-mono">
                            <span>Rp {{ number_format($scaleMax * 0.25, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-b border-slate-200 flex items-center justify-between text-[10px] text-slate-400 font-mono">
                            <span>Rp 0</span>
                        </div>
                    </div>

                    <!-- Bars Container -->
                    <div class="h-44 flex items-end justify-between gap-1.5 sm:gap-2.5 pb-7 relative z-10 pl-1">
                        @foreach($monthlyTrend as $mTrend)
                            @php
                                $ratio = $mTrend['gross'] / $scaleMax;
                                $barHeight = ($mTrend['gross'] > 0) ? max(10, min(100, round($ratio * 100))) : 4;
                                $shortMonth = substr($mTrend['month_name'], 0, 3);
                                $isCurrentMonth = ($mTrend['month_num'] === $currentMonthNum);
                            @endphp
                            <div class="flex-1 h-full flex flex-col justify-end items-center group relative">
                                <!-- Tooltip Hover -->
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-9 bg-slate-900 text-white text-[11px] py-1 px-2.5 rounded-lg pointer-events-none whitespace-nowrap z-30 shadow-md">
                                    <span class="font-bold">{{ $mTrend['month_name'] }}:</span> Rp {{ number_format($mTrend['gross'], 0, ',', '.') }} ({{ $mTrend['orders'] }} order)
                                </div>

                                <!-- The Bar -->
                                <div class="w-full flex-1 flex items-end justify-center">
                                    <div 
                                        style="height: {{ $barHeight }}%" 
                                        class="w-full max-w-[20px] rounded-t-md transition-all {{ $mTrend['gross'] > 0 ? 'bg-blue-600 group-hover:bg-blue-700' : ($isCurrentMonth ? 'bg-blue-200' : 'bg-slate-200/80') }}"
                                    ></div>
                                </div>

                                <!-- Month Label -->
                                <span class="absolute -bottom-1 text-[11px] {{ $isCurrentMonth ? 'text-blue-600 font-bold' : 'text-slate-400 font-medium' }}">
                                    {{ $shortMonth }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-blue-600"></span>
                    <span>Bulan dengan penjualan</span>
                </span>
                <span class="text-slate-400">Data otomatis terupdate dari order selesai</span>
            </div>
        </div>

        <!-- Kanan: Distribusi Kategori Produk (5 Kolom) -->
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 mb-1">Performa Kategori Produk</h3>
                <p class="text-xs text-slate-500 mb-4">Pangsa pasar dan kontribusi omset per kategori.</p>

                <div class="space-y-4">
                    @forelse($categoryBreakdown as $catData)
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <div class="font-medium text-slate-900 flex items-center gap-2">
                                    <span>{{ $catData['category'] }}</span>
                                    <span class="text-slate-400 font-normal">({{ $catData['quantity'] }} pcs)</span>
                                </div>
                                <div class="font-semibold text-slate-900">
                                    Rp {{ number_format($catData['gross'], 0, ',', '.') }}
                                    <span class="text-slate-400 font-normal ml-1">({{ $catData['pct'] }}%)</span>
                                </div>
                            </div>
                            <!-- Progress Bar -->
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-blue-600 rounded-full transition-all duration-500" 
                                    style="width: {{ $catData['pct'] }}%"
                                ></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            Belum ada transaksi kategori pada periode ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 mt-4 text-xs text-slate-500 flex items-center justify-between">
                <span>Total {{ count($categoryBreakdown) }} Kategori</span>
                <a href="{{ route('admin.products') }}" class="text-blue-600 font-medium hover:underline">Kelola Katalog &rarr;</a>
            </div>
        </div>

    </div>

    <!-- LEVEL 4: Operasional Inventaris (Produk Terlaris & Peringatan Stok) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Produk Terlaris -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Produk Terlaris</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Produk dengan kuantitas penjualan tertinggi.</p>
                    </div>
                    <a href="{{ route('admin.products') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                        Lihat Produk &rarr;
                    </a>
                </div>

                <div class="space-y-3.5">
                    @forelse($topProducts as $index => $tp)
                        <div class="flex items-center gap-3 py-1">
                            <!-- Ranking Number -->
                            <span class="w-5 text-center text-xs font-bold {{ $index === 0 ? 'text-amber-500' : 'text-slate-400' }}">
                                #{{ $index + 1 }}
                            </span>
                            <img src="{{ $tp->image }}" alt="{{ $tp->name }}" class="w-11 h-11 rounded-xl object-cover border border-slate-200 shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs sm:text-sm font-medium text-slate-900 truncate">{{ $tp->name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $tp->category }} &bull; {{ $tp->formatted_price }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs font-bold text-slate-900">{{ $tp->period_sold ?? $tp->sold }} pcs terjual</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Sisa stok: {{ $tp->stock }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            Belum ada transaksi penjualan pada periode ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Peringatan Stok Menipis (Stok <= 5 pcs) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Peringatan Stok (&le; 5 pcs)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Inventaris kritis yang perlu segera di-restock.</p>
                    </div>
                    <a href="{{ route('admin.products') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                        Kelola Stok &rarr;
                    </a>
                </div>

                <div class="space-y-3.5">
                    @forelse($lowStockProducts as $lp)
                        <div class="flex items-start gap-3 py-1 border-b border-slate-100 last:border-b-0 pb-3">
                            <img src="{{ $lp->image }}" alt="{{ $lp->name }}" class="w-11 h-11 rounded-xl object-cover border border-slate-200 shrink-0 mt-0.5">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs sm:text-sm font-medium text-slate-900 truncate">{{ $lp->name }}</div>
                                @if(!empty($lp->low_variants_list))
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($lp->low_variants_list as $lv)
                                            <span class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.5 rounded font-medium {{ $lv['stock'] === 0 ? 'text-rose-700 bg-rose-50' : 'text-amber-800 bg-amber-50' }}">
                                                <span>{{ $lv['name'] }}:</span>
                                                <span class="font-bold">{{ $lv['stock'] === 0 ? 'Habis' : $lv['stock'] . ' pcs' }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $lp->category }} &bull; {{ $lp->formatted_price }}</div>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                @if(empty($lp->low_variants_list))
                                    <div class="text-xs font-bold {{ $lp->stock === 0 ? 'text-rose-600' : 'text-orange-600' }}">
                                        {{ $lp->stock === 0 ? 'Habis' : $lp->stock . ' pcs' }}
                                    </div>
                                @endif
                                <a href="{{ route('admin.products') }}" class="text-[11px] text-blue-600 hover:underline block mt-0.5 font-medium">
                                    Restock &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            Semua stok produk saat ini dalam kondisi aman (&gt; 5 unit).
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- LEVEL 5: Live Feed - Pesanan Masuk Terbaru (Tipografi Bersih Tanpa Bubble Pill) -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Pesanan Masuk Terbaru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Transaksi belanja pelanggan yang baru saja masuk.</p>
            </div>
            <a href="{{ route('admin.orders') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                <span>Lihat Semua Pesanan</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap">No. Invoice</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Pelanggan</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Total Belanja</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Status</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Waktu</th>
                        <th class="px-5 py-3.5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- No Invoice -->
                            <td class="px-5 py-4 whitespace-nowrap font-mono font-bold text-slate-900">
                                {{ $order->invoice_number }}
                            </td>

                            <!-- Pelanggan -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900">{{ $order->user->name ?? $order->recipient_name }}</div>
                                <div class="text-xs text-slate-400 font-normal">{{ $order->user->email ?? $order->recipient_phone }}</div>
                            </td>

                            <!-- Total Belanja -->
                            <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-900">
                                {{ $order->formatted_total }}
                            </td>

                            <!-- Status (Indikator Titik Bersih Standar Industri, Bukan Kapsul Tombol) -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($order->status === 'dikemas' || $order->status === 'diproses')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-sky-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                        <span>Perlu Dikemas</span>
                                    </span>
                                @elseif($order->status === 'dikirim')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        <span>Sedang Dikirim</span>
                                    </span>
                                @elseif($order->status === 'selesai')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Selesai</span>
                                    </span>
                                @elseif($order->status === 'batal')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Dibatalkan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Belum Bayar</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Waktu -->
                            <td class="px-5 py-4 whitespace-nowrap text-slate-500 text-xs font-normal">
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a 
                                    href="{{ route('admin.orders') }}" 
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors"
                                >
                                    Kelola Pesanan &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-xs text-slate-400">
                                Belum ada pesanan terbaru di toko Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
