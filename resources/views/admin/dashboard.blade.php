@extends('admin.layout')

@section('title', 'Dashboard Overview & Analisis Keuangan')

@section('content')
<div class="space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Dashboard Overview &amp; Keuangan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Laporan otomatis Pendapatan Kotor, Pendapatan Bersih, dan Analisis Performa per Kategori &amp; Bulan.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-2xs">
                <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Real-time Sync: {{ date('d M Y') }}
            </span>
        </div>
    </div>

    <!-- FILTER BAR (BULAN & KATEGORI PRODUK) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Laporan Keuangan Dinamis
            </h3>
            @if($selectedMonth !== 'all' || $selectedCategory !== 'all')
                <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-200">Filter Aktif</span>
            @endif
        </div>

        <form action="{{ route('admin.dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <!-- Select Month -->
            <div class="sm:col-span-5">
                <label class="block text-xs font-bold text-slate-700 mb-1">Periode Bulan</label>
                <select name="month" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
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
                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Produk</label>
                <select name="category" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
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
                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs py-2 px-3 rounded-xl shadow-2xs transition-colors flex items-center justify-center gap-1 cursor-pointer">
                    Terapkan
                </button>
                @if($selectedMonth !== 'all' || $selectedCategory !== 'all')
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-300 transition-colors" title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4 EXECUTIVE KPI ANALYTICS CARDS (CLEAN SVG ICONS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- CARD 1: PENDAPATAN KOTOR (GROSS REVENUE) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-3 relative overflow-hidden group hover:border-emerald-500 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pendapatan Kotor (Bruto)</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Rp {{ number_format($stats['gross_revenue'], 0, ',', '.') }}</h3>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Omset Total</span>
                    <span class="text-[11px] text-slate-400 font-medium">Sebelum Biaya</span>
                </div>
            </div>
            <div class="absolute right-0 bottom-0 w-24 h-24 bg-emerald-500/5 rounded-full blur-xl pointer-events-none"></div>
        </div>

        <!-- CARD 2: PENDAPATAN BERSIH (NET PROFIT 85%) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-3 relative overflow-hidden group hover:border-blue-500 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pendapatan Bersih (Net Profit)</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center border border-blue-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Rp {{ number_format($stats['net_revenue'], 0, ',', '.') }}</h3>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">Margin Clean 85%</span>
                    <span class="text-[11px] text-slate-400 font-medium">Laba Bersih</span>
                </div>
            </div>
            <div class="absolute right-0 bottom-0 w-24 h-24 bg-blue-500/5 rounded-full blur-xl pointer-events-none"></div>
        </div>

        <!-- CARD 3: TOTAL PESANAN & AOV -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-3 relative overflow-hidden group hover:border-indigo-500 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Volume Transaksi</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center border border-indigo-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight">{{ number_format($stats['total_orders'], 0, ',', '.') }} Pesanan</h3>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-[11px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">AOV: Rp {{ number_format($stats['avg_order_val'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="absolute right-0 bottom-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-xl pointer-events-none"></div>
        </div>

        <!-- CARD 4: TOTAL PELANGGAN & PRODUK -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-3 relative overflow-hidden group hover:border-purple-500 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pelanggan &amp; Produk</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center border border-purple-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight">{{ number_format($stats['total_customers'], 0, ',', '.') }} Pengguna</h3>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-[11px] font-semibold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">{{ $stats['total_products'] }} SKU Aktif</span>
                </div>
            </div>
            <div class="absolute right-0 bottom-0 w-24 h-24 bg-purple-500/5 rounded-full blur-xl pointer-events-none"></div>
        </div>

    </div>

    <!-- TWO COLUMNS FINANCIAL ANALYTICS GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT (7 COLS): ANALISIS PENDAPATAN PER KATEGORI PRODUK -->
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden space-y-4">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Performa Pendapatan Per Kategori Produk
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rincian pendapatan kotor, estimasi laba bersih, dan proporsi omset</p>
                </div>
            </div>

            <div class="overflow-x-auto px-5 pb-5">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                        <tr>
                            <th class="py-3 px-3">Kategori</th>
                            <th class="py-3 px-3 text-center">Unit Terjual</th>
                            <th class="py-3 px-3 text-right">Pendapatan Kotor</th>
                            <th class="py-3 px-3 text-right">Pendapatan Bersih</th>
                            <th class="py-3 px-3 text-right">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @foreach($categoryBreakdown as $catData)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-3 font-bold text-slate-900">{{ $catData['category'] }}</td>
                                <td class="py-3 px-3 text-center font-mono font-bold">{{ number_format($catData['quantity'], 0, ',', '.') }} pcs</td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($catData['gross'], 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-700">Rp {{ number_format($catData['net'], 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="font-semibold text-blue-700 text-xs">{{ $catData['pct'] }}%</span>
                                        <div class="w-12 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-blue-700 h-1.5 rounded-full" style="width: {{ min(100, $catData['pct']) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT (5 COLS): LAPORAN TREN BULANAN -->
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden space-y-4">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Ringkasan Per Bulan (2026)
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tren omset dan laba bersih 12 bulan</p>
                </div>
            </div>

            <div class="overflow-x-auto px-5 pb-5 max-h-[380px] overflow-y-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-bold uppercase">
                        <tr>
                            <th class="py-2.5 px-2">Bulan</th>
                            <th class="py-2.5 px-2 text-center">Pesanan</th>
                            <th class="py-2.5 px-2 text-right">Pendapatan Kotor</th>
                            <th class="py-2.5 px-2 text-right">Pendapatan Bersih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($monthlyTrend as $mTrend)
                            <tr class="{{ (string)$selectedMonth === (string)$mTrend['month_num'] ? 'bg-blue-50/60 font-bold' : 'hover:bg-slate-50' }} transition-colors">
                                <td class="py-2.5 px-2 font-bold text-slate-900">{{ $mTrend['month_name'] }}</td>
                                <td class="py-2.5 px-2 text-center font-mono">{{ $mTrend['orders'] }}</td>
                                <td class="py-2.5 px-2 text-right font-mono font-bold text-slate-900">Rp {{ number_format($mTrend['gross'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-2 text-right font-mono font-bold text-emerald-700">Rp {{ number_format($mTrend['net'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- RECENT TRANSACTIONS TABLE -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden space-y-4">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h2 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2 2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Transaksi Terakhir Masuk
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar transaksi pelanggan terbaru di toko Anda</p>
            </div>
            <a href="{{ route('admin.orders') }}" class="text-xs font-semibold text-blue-700 hover:underline">Lihat Semua Pesanan →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3.5">No. Invoice</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Total Bayar</th>
                        <th class="px-5 py-3.5">Status Pesanan</th>
                        <th class="px-5 py-3.5">Waktu Transaksi</th>
                        <th class="px-5 py-3.5 text-right">Kelola</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">{{ $order->invoice_number }}</td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $order->user->name ?? $order->recipient_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $order->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900">{{ $order->formatted_total }}</td>
                            <td class="px-5 py-4">
                                @if($order->status === 'dikemas' || $order->status === 'diproses')
                                    <span class="bg-blue-50 text-blue-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-blue-200">SEDANG DIKEMAS</span>
                                @elseif($order->status === 'dikirim')
                                    <span class="bg-purple-50 text-purple-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-purple-200">DALAM PENGIRIMAN</span>
                                @elseif($order->status === 'selesai')
                                    <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-emerald-200">SELESAI</span>
                                @else
                                    <span class="bg-amber-50 text-amber-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-amber-200">BELUM BAYAR</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-xs">{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.orders') }}" class="text-xs font-bold text-blue-700 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 transition-colors">
                                    Kelola Pesanan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada transaksi masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
