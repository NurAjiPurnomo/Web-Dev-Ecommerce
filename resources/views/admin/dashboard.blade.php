@extends('admin.layout')

@section('title', 'Dashboard Toko')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Toko</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Ringkasan performa penjualan, statistik transaksi, dan analisis produk.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-2xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Pembaruan: {{ date('d M Y') }}
            </span>
        </div>
    </div>

    <!-- FILTER BAR (PERIODE BULAN & KATEGORI) -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-2xs space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Periode &amp; Kategori
            </h3>
            @if($selectedMonth !== 'all' || $selectedCategory !== 'all')
                <span class="text-[11px] font-medium text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">Filter Aktif</span>
            @endif
        </div>

        <form action="{{ route('admin.dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <!-- Select Month -->
            <div class="sm:col-span-5">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Periode Bulan</label>
                <select name="month" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:border-blue-600 focus:outline-none transition-colors">
                    <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Semua Bulan (Tahun 2026)</option>
                    @foreach($indonesianMonths as $num => $mName)
                        <option value="{{ $num }}" {{ (string)$selectedMonth === (string)$num ? 'selected' : '' }}>
                            Bulan {{ $mName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Select Category -->
            <div class="sm:col-span-5">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Produk</label>
                <select name="category" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:border-blue-600 focus:outline-none transition-colors">
                    <option value="all" {{ $selectedCategory === 'all' ? 'selected' : '' }}>Semua Kategori Produk</option>
                    <option value="Pakaian" {{ $selectedCategory === 'Pakaian' ? 'selected' : '' }}>Pakaian</option>
                    <option value="Sepatu" {{ $selectedCategory === 'Sepatu' ? 'selected' : '' }}>Sepatu</option>
                    <option value="Aksesoris" {{ $selectedCategory === 'Aksesoris' ? 'selected' : '' }}>Aksesoris</option>
                    <option value="Gadget" {{ $selectedCategory === 'Gadget' ? 'selected' : '' }}>Gadget</option>
                    <option value="Rumah Tangga" {{ $selectedCategory === 'Rumah Tangga' ? 'selected' : '' }}>Rumah Tangga</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs py-2 px-3 rounded-lg shadow-2xs transition-colors flex items-center justify-center gap-1 cursor-pointer">
                    Terapkan
                </button>
                @if($selectedMonth !== 'all' || $selectedCategory !== 'all')
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-lg border border-slate-300 transition-colors" title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4 EXECUTIVE KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- CARD 1: PENDAPATAN KOTOR -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs space-y-2 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Pendapatan Kotor</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Rp {{ number_format($stats['gross_revenue'], 0, ',', '.') }}</h3>
                <p class="text-[11px] text-slate-500 mt-1">Total omset kotor sebelum biaya</p>
            </div>
        </div>

        <!-- CARD 2: PENDAPATAN BERSIH -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs space-y-2 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Estimasi Laba Bersih</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Rp {{ number_format($stats['net_revenue'], 0, ',', '.') }}</h3>
                <p class="text-[11px] text-slate-500 mt-1">Estimasi margin laba ~85%</p>
            </div>
        </div>

        <!-- CARD 3: TOTAL PESANAN -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs space-y-2 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Total Pesanan</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ number_format($stats['total_orders'], 0, ',', '.') }} Transaksi</h3>
                <p class="text-[11px] text-slate-500 mt-1">Rata-rata: Rp {{ number_format($stats['avg_order_val'], 0, ',', '.') }}/pesanan</p>
            </div>
        </div>

        <!-- CARD 4: PELANGGAN & PRODUK -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs space-y-2 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Pelanggan &amp; Produk</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ number_format($stats['total_customers'], 0, ',', '.') }} Pelanggan</h3>
                <p class="text-[11px] text-slate-500 mt-1">{{ $stats['total_products'] }} Produk aktif di katalog</p>
            </div>
        </div>

    </div>

    <!-- TWO COLUMNS FINANCIAL ANALYTICS GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT (7 COLS): PERFORMA KATEGORI PRODUK -->
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-xl shadow-2xs overflow-hidden space-y-4">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Performa Penjualan per Kategori
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pendapatan kotor, estimasi laba, dan porsi kontribusi</p>
                </div>
            </div>

            <div class="overflow-x-auto px-4 pb-4">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-xs">
                        <tr>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-3 text-center">Unit Terjual</th>
                            <th class="py-2.5 px-3 text-right">Pendapatan Kotor</th>
                            <th class="py-2.5 px-3 text-right">Est. Laba Bersih</th>
                            <th class="py-2.5 px-3 text-right">Porsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($categoryBreakdown as $catData)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $catData['category'] }}</td>
                                <td class="py-2.5 px-3 text-center font-mono font-medium">{{ number_format($catData['quantity'], 0, ',', '.') }} pcs</td>
                                <td class="py-2.5 px-3 text-right font-mono font-semibold text-slate-900">Rp {{ number_format($catData['gross'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-semibold text-emerald-600">Rp {{ number_format($catData['net'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $catData['pct'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT (5 COLS): VISUAL TREN PENJUALAN BULANAN -->
        @php
            $maxMonthlyGross = max(array_column($monthlyTrend, 'gross') ?: [1]);
            if ($maxMonthlyGross <= 0) $maxMonthlyGross = 1;
        @endphp
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-xl shadow-2xs overflow-hidden flex flex-col justify-between">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Grafik Tren Bulanan (2026)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Visual pergerakan omset selama 12 bulan</p>
            </div>

            <!-- Mini Visual Bar Chart -->
            <div class="p-4 flex-1 flex flex-col justify-end">
                <div class="h-44 flex items-end justify-between gap-1.5 pt-6 pb-2 border-b border-slate-100">
                    @foreach($monthlyTrend as $mTrend)
                        @php
                            $heightPct = ($mTrend['gross'] > 0) ? max(10, round(($mTrend['gross'] / $maxMonthlyGross) * 100)) : 4;
                            $shortName = substr($mTrend['month_name'], 0, 3);
                            $isSelected = (string)$selectedMonth === (string)$mTrend['month_num'];
                        @endphp
                        <div class="flex-1 h-full flex flex-col justify-end items-center gap-1 group relative">
                            <!-- Tooltip Hover -->
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-8 bg-slate-900 text-white text-[10px] font-semibold py-1 px-2 rounded shadow pointer-events-none whitespace-nowrap z-20">
                                {{ $mTrend['month_name'] }}: Rp {{ number_format($mTrend['gross'], 0, ',', '.') }} ({{ $mTrend['orders'] }} pesanan)
                            </div>
                            <!-- Visual Bar Container -->
                            <div class="w-full flex-1 flex items-end justify-center">
                                <div 
                                    style="height: {{ $heightPct }}%" 
                                    class="w-full max-w-[20px] rounded-t-sm transition-all duration-300 {{ $isSelected ? 'bg-blue-600' : ($mTrend['gross'] > 0 ? 'bg-blue-500 group-hover:bg-blue-600' : 'bg-slate-200') }}"
                                ></div>
                            </div>
                            <span class="text-[10px] font-semibold {{ $isSelected ? 'text-blue-600 font-bold' : 'text-slate-400' }} uppercase">
                                {{ $shortName }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-2 font-medium">
                    <span>Jan (Mulai)</span>
                    <span class="text-blue-600 font-semibold">Tinggi: Rp {{ number_format($maxMonthlyGross, 0, ',', '.') }}</span>
                    <span>Des (Akhir)</span>
                </div>
            </div>
        </div>

    </div>

    <!-- RECENT TRANSACTIONS TABLE -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-2xs overflow-hidden space-y-4">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h2 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2 2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Pesanan Terbaru
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar transaksi pelanggan terbaru</p>
            </div>
            <a href="{{ route('admin.orders') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">Lihat Semua Pesanan &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-xs">
                    <tr>
                        <th class="px-4 py-3">No. Invoice</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-mono font-semibold text-slate-900">{{ $order->invoice_number }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $order->user->name ?? $order->recipient_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $order->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $order->formatted_total }}</td>
                            <td class="px-4 py-3">
                                @if($order->status === 'dikemas' || $order->status === 'diproses')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                        Dikemas
                                    </span>
                                @elseif($order->status === 'dikirim')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Pengiriman
                                    </span>
                                @elseif($order->status === 'selesai')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.orders') }}" class="text-xs font-medium text-slate-700 hover:text-blue-600 bg-white hover:bg-slate-50 px-2.5 py-1 rounded border border-slate-200 transition-colors inline-block">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada transaksi masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

