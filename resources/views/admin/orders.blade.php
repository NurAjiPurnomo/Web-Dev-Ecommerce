@extends('admin.layout')

@section('title', 'Pesanan')

@section('content')
@php
    $ordersMap = [];
    foreach ($orders as $o) {
        $ordersMap[$o->id] = [
            'id'               => $o->id,
            'invoice_number'   => $o->invoice_number,
            'status'           => $o->status,
            'tracking_number'  => $o->tracking_number ?: '',
            'recipient_name'   => $o->recipient_name ?: ($o->user->name ?? 'Pelanggan'),
            'recipient_phone'  => $o->recipient_phone ?: ($o->user->phone ?? '-'),
            'shipping_address' => $o->shipping_address ?: 'Alamat tidak tersedia',
            'courier'          => $o->courier ?: '-',
            'payment_method'   => $o->payment_method ?: '-',
            'formatted_total'  => $o->formatted_total,
            'subtotal'         => $o->subtotal,
            'shipping_cost'    => $o->shipping_cost,
            'discount_amount'  => $o->discount_amount,
            'created_at_fmt'   => $o->created_at ? $o->created_at->format('d M Y, H:i') : '-',
            'items'            => $o->items->map(fn($i) => [
                'id'           => $i->id,
                'product_name' => $i->product_name,
                'quantity'     => $i->quantity,
                'price'        => (float)$i->price,
                'image'        => $i->image,
            ])->values()->toArray()
        ];
    }
@endphp

<style>
@keyframes truckDrive {
    0%, 100% { transform: translateY(0px) translateX(0px); }
    25% { transform: translateY(-1.5px) translateX(1px); }
    50% { transform: translateY(0.5px) translateX(0px); }
    75% { transform: translateY(-1px) translateX(1.5px); }
}
@keyframes speedWind {
    0% { transform: translateX(3px); opacity: 0.15; }
    50% { opacity: 1; }
    100% { transform: translateX(-5px); opacity: 0.05; }
}
.animate-truck-drive {
    animation: truckDrive 0.4s ease-in-out infinite;
}
.animate-speed-1 {
    animation: speedWind 0.45s linear infinite;
}
.animate-speed-2 {
    animation: speedWind 0.3s linear infinite reverse;
}
.animate-speed-3 {
    animation: speedWind 0.5s linear infinite;
}
</style>

<div x-data="{ ordersMap: @js($ordersMap), selectedOrder: null, showDetailModal: false, showAwbPrintModal: false, showInputResiModal: false, isSubmittingPickup: false }" class="space-y-6">

    <!-- Header Section (Konsisten 100% dengan Katalog Produk & Dashboard) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Pesanan Pelanggan</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola transaksi toko, pemrosesan pesanan, pengiriman kurir, dan pencetakan label resi.</p>
        </div>
        <div>
            <a 
                href="{{ route('admin.orders.export', request()->query()) }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                title="Unduh Laporan Transaksi Format CSV"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Transaksi (4 KPI Cards - Persis Grid Katalog Produk) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Transaksi -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Total Transaksi</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($totalOrdersCount, 0, ',', '.') }}</div>
            <p class="text-xs text-slate-500 mt-0.5">Item pesanan terdaftar</p>
        </div>

        <!-- Perlu Dikemas -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Perlu Dikemas</span>
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($processingOrdersCount, 0, ',', '.') }}</div>
            <p class="text-xs text-sky-600 font-medium mt-0.5">Siap dipacking &amp; dikirim</p>
        </div>

        <!-- Dalam Pengiriman -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Dalam Pengiriman</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($shippingOrdersCount, 0, ',', '.') }}</div>
            <p class="text-xs text-purple-600 font-medium mt-0.5">Sedang diantar kurir</p>
        </div>

        <!-- Pesanan Selesai -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Pesanan Selesai</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($completedOrdersCount, 0, ',', '.') }}</div>
            <p class="text-xs text-emerald-600 font-medium mt-0.5">Transaksi sukses</p>
        </div>
    </div>

    <!-- Main Content Container: Status Tabs, Filters & Orders Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <!-- Status Tabs (Shopee / Tokopedia Seller Centre Pattern) -->
        <div class="border-b border-slate-200 px-4 sm:px-6 pt-2 bg-white">
            <nav class="flex items-center gap-6 overflow-x-auto no-scrollbar -mb-px text-xs sm:text-sm font-medium">
                @php
                    $tabs = [
                        'semua'           => ['label' => 'Semua', 'count' => $tabCounts['semua']],
                        'belum_bayar'     => ['label' => 'Belum Bayar', 'count' => $tabCounts['belum_bayar']],
                        'dikemas'         => ['label' => 'Perlu Dikemas', 'count' => $tabCounts['dikemas'], 'highlight' => 'sky'],
                        'dikirim'         => ['label' => 'Dikirim', 'count' => $tabCounts['dikirim']],
                        'selesai'         => ['label' => 'Selesai', 'count' => $tabCounts['selesai']],
                        'pengajuan_batal' => ['label' => 'Pengajuan Batal', 'count' => $tabCounts['pengajuan_batal'], 'highlight' => 'amber'],
                        'batal'           => ['label' => 'Dibatalkan', 'count' => $tabCounts['batal']],
                    ];
                @endphp

                @foreach($tabs as $tabKey => $tabData)
                    @php
                        $isActive = ($status === $tabKey);
                        $queryParams = request()->query();
                        $queryParams['status'] = $tabKey;
                        $tabUrl = route('admin.orders', $queryParams);
                    @endphp
                    <a 
                        href="{{ $tabUrl }}" 
                        class="py-3 border-b-2 whitespace-nowrap transition-colors flex items-center gap-1.5 {{ $isActive ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}"
                    >
                        <span>{{ $tabData['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[11px] {{ $isActive ? 'bg-blue-100 text-blue-700 font-semibold' : 'bg-slate-100 text-slate-600' }} {{ isset($tabData['highlight']) && $tabData['count'] > 0 ? ($tabData['highlight'] === 'amber' ? 'bg-amber-100 text-amber-800 font-semibold' : 'bg-sky-100 text-sky-800 font-semibold') : '' }}">
                            {{ $tabData['count'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Search & Filter Toolbar (Persis Desain Katalog Produk) -->
        <div class="p-4 sm:p-5 bg-slate-50/50 border-b border-slate-200">
            <form action="{{ route('admin.orders') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                @if($status !== 'semua')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 max-w-2xl">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search }}" 
                            placeholder="Cari no. invoice, resi, pembeli, no. HP..." 
                            class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all shadow-xs"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Filter Kurir Dropdown -->
                    <div class="sm:w-44">
                        <select 
                            name="courier" 
                            onchange="this.form.submit()" 
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all shadow-xs cursor-pointer"
                        >
                            <option value="all" {{ $courier === 'all' ? 'selected' : '' }}>Semua Kurir</option>
                            @foreach($courierList as $c)
                                <option value="{{ $c }}" {{ $courier === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Rentang Waktu Dropdown -->
                    <div class="sm:w-36">
                        <select 
                            name="period" 
                            onchange="this.form.submit()" 
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all shadow-xs cursor-pointer"
                        >
                            <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                            <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
                            <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>7 Hari</option>
                            <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>30 Hari</option>
                            <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
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
                @if($search || $courier !== 'all' || $period !== 'all' || $status !== 'semua')
                    <a 
                        href="{{ route('admin.orders') }}" 
                        class="text-xs text-slate-500 hover:text-slate-800 flex items-center gap-1 font-medium self-center md:self-auto shrink-0"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reset Filter</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Desktop Orders Table View (md+) - Format Standar Profesional (Fit Screen Tanpa Geser) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-medium text-xs">
                    <tr>
                        <th class="px-3 py-2.5 font-medium">No. Invoice &amp; Waktu</th>
                        <th class="px-2 py-2.5 font-medium">Pembeli</th>
                        <th class="px-2 py-2.5 font-medium">Produk Dipesan</th>
                        <th class="px-2 py-2.5 font-medium">Total Bayar</th>
                        <th class="px-2 py-2.5 font-medium">Kurir &amp; Resi</th>
                        <th class="px-2 py-2.5 font-medium">Status</th>
                        <th class="px-3 py-2.5 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($orders as $o)
                        @php
                            $isPendingCancel = ($o->status === 'pengajuan_batal');
                            $isCancelled     = ($o->status === 'batal');
                            $isCompleted     = ($o->status === 'selesai');
                            $isShipping      = ($o->status === 'dikirim');
                            $isPacking       = in_array($o->status, ['dikemas', 'diproses']);
                            $isUnpaid        = in_array($o->status, ['belum_bayar', 'belum_dibayar']);
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <!-- Invoice & Waktu -->
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <span class="font-mono font-semibold text-slate-900 text-[11px] leading-tight">#{{ $o->invoice_number }}</span>
                                    <button 
                                        type="button" 
                                        @click="navigator.clipboard.writeText('{{ $o->invoice_number }}'); alert('Nomor invoice disalin!')" 
                                        class="text-slate-400 hover:text-slate-700 cursor-pointer shrink-0" 
                                        title="Salin No. Invoice"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5 leading-none">
                                    {{ $o->created_at ? $o->created_at->format('d M Y, H:i') : '-' }}
                                </div>
                            </td>

                            <!-- Pembeli -->
                            <td class="px-2 py-2.5">
                                <div class="font-medium text-slate-900 truncate max-w-[100px] lg:max-w-[125px] text-xs leading-tight" title="{{ $o->recipient_name ?: ($o->user->name ?? 'Pelanggan') }}">
                                    {{ $o->recipient_name ?: ($o->user->name ?? 'Pelanggan') }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5 leading-none truncate max-w-[100px] lg:max-w-[125px]">
                                    {{ $o->recipient_phone ?: ($o->user->phone ?? '-') }}
                                </div>
                            </td>

                            <!-- Produk Dipesan -->
                            <td class="px-2 py-2.5">
                                @if($o->items->isNotEmpty())
                                    @php $firstItem = $o->items->first(); @endphp
                                    <div class="flex items-center gap-2">
                                        <img 
                                            src="{{ $firstItem->image }}" 
                                            alt="{{ $firstItem->product_name }}" 
                                            class="w-8 h-8 rounded-lg object-cover border border-slate-200 bg-slate-100 shrink-0"
                                        >
                                        <div class="min-w-0 max-w-[120px] lg:max-w-[160px] xl:max-w-[210px]">
                                            <h5 class="font-medium text-slate-900 truncate text-xs leading-tight" title="{{ $firstItem->product_name }}">
                                                {{ $firstItem->product_name }}
                                            </h5>
                                            <div class="text-[10px] text-slate-400 mt-0.5 leading-none truncate">
                                                <span>Qty: {{ $firstItem->quantity }}x</span>
                                                @if($o->items->count() > 1)
                                                    <span class="text-blue-600 font-medium ml-1">+{{ $o->items->count() - 1 }} lainnya</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">Tanpa item</span>
                                @endif
                            </td>

                            <!-- Total Bayar -->
                            <td class="px-2 py-2.5 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 text-xs leading-tight">{{ $o->formatted_total }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5 leading-none">
                                    <span class="bg-slate-100 text-slate-600 px-1 py-0.5 rounded font-mono text-[9px] uppercase">
                                        {{ str_replace('_', ' ', $o->payment_method ?: '-') }}
                                    </span>
                                </div>
                            </td>

                            <!-- Kurir & Resi -->
                            <td class="px-2 py-2.5 whitespace-nowrap">
                                <div class="font-medium text-slate-800 text-xs leading-tight truncate max-w-[95px] lg:max-w-[115px]">{{ $o->courier ?: '-' }}</div>
                                <div class="mt-0.5 leading-none">
                                    @if(!empty($o->tracking_number))
                                        <div class="inline-flex items-center gap-1 font-mono text-[10px] text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                            <span class="truncate max-w-[70px]" title="{{ $o->tracking_number }}">{{ $o->tracking_number }}</span>
                                            <button 
                                                type="button" 
                                                @click="navigator.clipboard.writeText('{{ $o->tracking_number }}'); alert('Nomor resi {{ $o->tracking_number }} berhasil disalin!')" 
                                                class="text-slate-400 hover:text-slate-700 cursor-pointer shrink-0" 
                                                title="Salin Resi"
                                            >
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Belum ada resi</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-2 py-2.5 whitespace-nowrap">
                                @if($isPendingCancel)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                        <span>Pengajuan Batal</span>
                                    </span>
                                @elseif($isCancelled)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Dibatalkan</span>
                                    </span>
                                @elseif($isCompleted)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Selesai</span>
                                    </span>
                                @elseif($isShipping)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                        <span>Dikirim</span>
                                    </span>
                                @elseif($isPacking)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                        <span>Perlu Dikemas</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Belum Bayar</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Tombol Detail -->
                                    <button 
                                        type="button" 
                                        @click="selectedOrder = ordersMap[{{ $o->id }}]; showDetailModal = true"
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors border border-slate-200 cursor-pointer"
                                        title="Rincian Lengkap Pesanan"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Detail</span>
                                    </button>

                                    <!-- Tombol Kontekstual -->
                                    @if($isPendingCancel)
                                        <form action="{{ route('admin.orders.update', $o->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="batal">
                                            <button 
                                                type="submit" 
                                                onclick="return confirm('Setujui pembatalan pesanan #{{ $o->invoice_number }}? Stok barang akan otomatis dikembalikan ke etalase.')" 
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100 bg-emerald-50 rounded-lg transition-colors border border-emerald-200 cursor-pointer"
                                                title="Setujui Pembatalan"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Setujui</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.orders.update', $o->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="dikemas">
                                            <button 
                                                type="submit" 
                                                onclick="return confirm('Tolak pengajuan pembatalan pesanan #{{ $o->invoice_number }} dan lanjutkan proses pengiriman?')" 
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 bg-slate-50 rounded-lg transition-colors border border-slate-200 cursor-pointer"
                                                title="Tolak Pembatalan"
                                            >
                                                <span>Tolak</span>
                                            </button>
                                        </form>
                                    @elseif($isUnpaid)
                                        <form action="{{ route('admin.orders.update', $o->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="dikemas">
                                            <button 
                                                type="submit" 
                                                onclick="return confirm('Konfirmasi pembayaran manual pesanan #{{ $o->invoice_number }} dan pindahkan ke Perlu Dikemas?')" 
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-sky-700 hover:bg-sky-100 bg-sky-50 rounded-lg transition-colors border border-sky-200 cursor-pointer"
                                                title="Konfirmasi Pembayaran"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Konfirmasi</span>
                                            </button>
                                        </form>
                                    @elseif($isPacking)
                                        <button 
                                            type="button" 
                                            @click="selectedOrder = ordersMap[{{ $o->id }}]; showInputResiModal = true; isSubmittingPickup = false"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-xs cursor-pointer"
                                            title="Atur Pengiriman (Request Pick Up)"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                            <span>Atur Pengiriman</span>
                                        </button>

                                        <a 
                                            href="{{ route('admin.orders.shippingLabel', $o->id) }}" 
                                            target="_blank"
                                            class="inline-flex items-center justify-center p-1 text-xs font-medium text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-100 rounded-lg transition-colors border border-slate-200 cursor-pointer"
                                            title="Cetak Label Resi Thermal A6"
                                        >
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                    @elseif($isShipping)
                                        <form action="{{ route('admin.orders.update', $o->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="selesai">
                                            <button 
                                                type="submit" 
                                                onclick="return confirm('Tandai pesanan #{{ $o->invoice_number }} sebagai SELESAI?')" 
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100 bg-emerald-50 rounded-lg transition-colors border border-emerald-200 cursor-pointer"
                                                title="Selesaikan Pesanan"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Selesai</span>
                                            </button>
                                        </form>

                                        <a 
                                            href="{{ route('tracking.public', $o->tracking_number ?: $o->invoice_number) }}" 
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-purple-700 hover:bg-purple-100 bg-purple-50 rounded-lg transition-colors border border-purple-200 cursor-pointer"
                                            title="Lacak Paket"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                            <span>Lacak</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </div>
                                    <p class="text-xs font-medium text-slate-700">Tidak ada transaksi ditemukan</p>
                                    <p class="text-xs text-slate-400">Belum ada pesanan yang masuk atau coba ubah kriteria filter status pesanan.</p>
                                    @if($search || $courier !== 'all' || $period !== 'all' || $status !== 'semua')
                                        <a href="{{ route('admin.orders') }}" class="inline-block mt-2 text-xs text-blue-600 hover:text-blue-700 font-medium">Reset Semua Filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Orders Cards View (< md) - Format Persis Katalog Produk -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($orders as $o)
                @php
                    $isPendingCancel = ($o->status === 'pengajuan_batal');
                    $isCancelled     = ($o->status === 'batal');
                    $isCompleted     = ($o->status === 'selesai');
                    $isShipping      = ($o->status === 'dikirim');
                    $isPacking       = in_array($o->status, ['dikemas', 'diproses']);
                    $isUnpaid        = in_array($o->status, ['belum_bayar', 'belum_dibayar']);
                    $firstItem       = $o->items->first();
                @endphp
                <div class="p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <img 
                            src="{{ $firstItem ? $firstItem->image : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80' }}" 
                            alt="{{ $firstItem->product_name ?? 'Pesanan' }}" 
                            class="w-14 h-14 rounded-lg object-cover border border-slate-200 bg-slate-100 shrink-0"
                        >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-mono text-xs font-bold text-slate-900">#{{ $o->invoice_number }}</span>
                                @if($isPendingCancel)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-800">Pengajuan Batal</span>
                                @elseif($isCancelled)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700">Dibatalkan</span>
                                @elseif($isCompleted)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700">Selesai</span>
                                @elseif($isShipping)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-50 text-purple-700">Dikirim</span>
                                @elseif($isPacking)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-sky-50 text-sky-700">Perlu Dikemas</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700">Belum Bayar</span>
                                @endif
                            </div>
                            <h4 class="font-medium text-slate-900 text-xs mt-1 truncate">{{ $firstItem->product_name ?? 'Pesanan' }}</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Pembeli: {{ $o->recipient_name ?: ($o->user->name ?? 'Pelanggan') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 bg-slate-50 p-2.5 rounded-lg text-xs border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-500 block">Total</span>
                            <span class="font-semibold text-slate-900 text-xs">{{ $o->formatted_total }}</span>
                        </div>
                        <div class="text-center">
                            <span class="text-[10px] text-slate-500 block">Kurir</span>
                            <span class="font-medium text-slate-800 text-xs">{{ $o->courier ?: '-' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-500 block">Metode</span>
                            <span class="font-medium text-slate-700 text-xs truncate">{{ $o->payment_method ?: '-' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button 
                            type="button" 
                            @click="selectedOrder = ordersMap[{{ $o->id }}]; showDetailModal = true"
                            class="flex-1 text-xs font-medium text-slate-700 hover:text-slate-900 bg-white py-1.5 rounded-lg border border-slate-200 transition-colors text-center cursor-pointer"
                        >
                            Detail
                        </button>

                        @if($isPendingCancel)
                            <form action="{{ route('admin.orders.update', $o->id) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="status" value="batal">
                                <button type="submit" onclick="return confirm('Setujui pembatalan pesanan ini?')" class="w-full text-xs font-medium text-emerald-700 bg-emerald-50 py-1.5 rounded-lg border border-emerald-200 text-center">
                                    Setujui
                                </button>
                            </form>
                        @elseif($isUnpaid)
                            <form action="{{ route('admin.orders.update', $o->id) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="status" value="dikemas">
                                <button type="submit" onclick="return confirm('Konfirmasi pembayaran pesanan ini?')" class="w-full text-xs font-medium text-sky-700 bg-sky-50 py-1.5 rounded-lg border border-sky-200 text-center">
                                    Konfirmasi
                                </button>
                            </form>
                        @elseif($isPacking)
                            <button 
                                type="button" 
                                @click="selectedOrder = ordersMap[{{ $o->id }}]; showInputResiModal = true; isSubmittingPickup = false"
                                class="flex-1 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 py-1.5 rounded-lg transition-colors text-center cursor-pointer shadow-xs"
                            >
                                Atur Pengiriman
                            </button>
                        @elseif($isShipping)
                            <form action="{{ route('admin.orders.update', $o->id) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="status" value="selesai">
                                <button type="submit" onclick="return confirm('Tandai pesanan ini SELESAI?')" class="w-full text-xs font-medium text-emerald-700 bg-emerald-50 py-1.5 rounded-lg border border-emerald-200 text-center">
                                    Selesai
                                </button>
                            </form>
                            <a 
                                href="{{ route('tracking.public', $o->tracking_number ?: $o->invoice_number) }}" 
                                target="_blank"
                                class="flex-1 text-xs font-medium text-purple-700 bg-purple-50 py-1.5 rounded-lg border border-purple-200 text-center"
                            >
                                Lacak
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    Tidak ada transaksi ditemukan.
                </div>
            @endforelse
        </div>

        <!-- Pagination Footer (Persis Katalog Produk: Kotak Biru di Kanan Bawah) -->
        @if ($orders->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-slate-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan <span class="text-slate-900 font-semibold">{{ $orders->firstItem() }}</span> - <span class="text-slate-900 font-semibold">{{ $orders->lastItem() }}</span> dari <span class="text-slate-900 font-semibold">{{ $orders->total() }}</span> pesanan
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    {{-- Previous Page Link --}}
                    @if ($orders->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center text-slate-300 cursor-not-allowed select-none">
                            <svg class="w-4 h-4 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $orders->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors select-none" title="Sebelumnya">
                            <svg class="w-4 h-4 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $orders->currentPage();
                        $lastPage = $orders->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $orders->url(1) }}" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors select-none">1</a>
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
                            <a href="{{ $orders->url($i) }}" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors select-none">
                                {{ $i }}
                            </a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="w-6 h-7 sm:h-8 flex items-center justify-center text-xs sm:text-sm text-slate-400 select-none">...</span>
                        @endif
                        <a href="{{ $orders->url($lastPage) }}" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors select-none">{{ $lastPage }}</a>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors select-none" title="Selanjutnya">
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

    <!-- MODAL DETAIL TRANSAKSI LENGKAP -->
    <div 
        x-show="showDetailModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <template x-if="selectedOrder">
            <div @click.outside="showDetailModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl p-5 sm:p-6 space-y-4 max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-semibold text-slate-900 text-base">Rincian Lengkap Pesanan</h3>
                        <p class="text-xs font-mono text-slate-500 mt-0.5" x-text="'Invoice: #' + selectedOrder.invoice_number"></p>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Status & Waktu Pemesanan -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Waktu Transaksi</span>
                            <span class="font-semibold text-slate-800" x-text="selectedOrder.created_at_fmt"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block text-[10px]">Status Pesanan</span>
                            <span class="font-bold uppercase tracking-wider text-blue-700" x-text="selectedOrder.status"></span>
                        </div>
                    </div>

                    <!-- Alamat Pengiriman -->
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                        <span class="font-bold text-slate-700 block text-xs uppercase tracking-wider">Alamat Tujuan Pengiriman</span>
                        <p class="text-slate-900 font-bold" x-text="(selectedOrder.recipient_name || 'Pelanggan') + ' (' + (selectedOrder.recipient_phone || '-') + ')'"></p>
                        <p class="text-slate-600 leading-relaxed text-[11px]" x-text="selectedOrder.shipping_address"></p>
                    </div>

                    <!-- Items Summary -->
                    <div class="space-y-2">
                        <span class="font-bold text-slate-700 block text-xs uppercase tracking-wider">Daftar Barang Dipesan</span>
                        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                            <template x-for="item in selectedOrder.items" :key="item.id">
                                <div class="flex items-center justify-between p-2.5 bg-white border border-slate-200 rounded-xl">
                                    <div class="flex items-center gap-2.5">
                                        <img :src="item.image" class="w-9 h-9 rounded object-cover border border-slate-200 shrink-0">
                                        <div>
                                            <h5 class="font-semibold text-slate-900 text-xs" x-text="item.product_name"></h5>
                                            <span class="text-slate-400 text-[11px]" x-text="'Qty: ' + item.quantity + ' pcs'"></span>
                                        </div>
                                    </div>
                                    <span class="font-semibold text-slate-900 text-xs" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.price * item.quantity)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Rincian Biaya -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5 text-[11px]">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Produk</span>
                            <span class="font-medium text-slate-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.subtotal || 0)"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Ongkos Kirim (<span x-text="selectedOrder.courier"></span>)</span>
                            <span class="font-medium text-slate-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.shipping_cost || 0)"></span>
                        </div>
                        <template x-if="selectedOrder.discount_amount > 0">
                            <div class="flex justify-between text-emerald-600">
                                <span>Potongan Diskon / Voucher</span>
                                <span class="font-medium" x-text="'- Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.discount_amount)"></span>
                            </div>
                        </template>
                        <div class="flex justify-between text-slate-900 font-bold pt-1.5 border-t border-slate-200 text-xs">
                            <span>Total Tagihan</span>
                            <span class="text-blue-700" x-text="selectedOrder.formatted_total"></span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 flex flex-col sm:flex-row justify-between items-center gap-2 border-t border-slate-100">
                    <button 
                        type="button" 
                        @click="showDetailModal = false; showAwbPrintModal = true" 
                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold hover:bg-blue-100 cursor-pointer flex items-center justify-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>Cetak Shipping Label</span>
                    </button>
                    <button 
                        type="button" 
                        @click="showDetailModal = false" 
                        class="w-full sm:w-auto px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold cursor-pointer"
                    >
                        Tutup Jendela
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- MODAL ATUR PENGIRIMAN PAKET (SHOPEE / TOKOPEDIA SELLER CENTER STANDARD) -->
    <div 
        x-show="showInputResiModal" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <template x-if="selectedOrder">
            <div @click.outside="if(!isSubmittingPickup) { showInputResiModal = false; isSubmittingPickup = false; }" class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl space-y-0 max-h-[92vh] flex flex-col">
                <!-- Modal Header -->
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-900 text-base">Atur Pengiriman Paket</h3>
                                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Biteship Live API</span>
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 font-mono mt-0.5" x-text="'Invoice #' + selectedOrder.invoice_number"></p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        :disabled="isSubmittingPickup"
                        @click="if(!isSubmittingPickup) { showInputResiModal = false; isSubmittingPickup = false; }" 
                        class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed" 
                        title="Tutup"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto text-xs">
                    <!-- Rincian Kurir & Alamat Penerima -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between pb-2.5 border-b border-slate-200/60">
                            <span class="text-slate-500 font-medium">Kurir Pilihan Pembeli:</span>
                            <span class="font-bold text-blue-700 bg-white px-2.5 py-1 rounded-md border border-slate-200 text-xs shadow-2xs" x-text="selectedOrder.courier || 'J&T (EZ)'"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Penerima Paket:</span>
                            <span class="font-semibold text-slate-900 text-right" x-text="selectedOrder.recipient_name + ' (' + selectedOrder.recipient_phone + ')'"></span>
                        </div>
                        <div class="pt-2 border-t border-slate-200/60">
                            <span class="text-slate-500 block text-[11px] font-medium mb-1">Alamat Tujuan Pengiriman:</span>
                            <div class="flex items-start gap-2 text-slate-700 text-xs leading-relaxed bg-white p-3 rounded-lg border border-slate-200/70">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="font-medium" x-text="selectedOrder.shipping_address"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Konfirmasi Request Pick Up Resmi -->
                    <div class="bg-gradient-to-b from-blue-50/70 to-blue-50/20 border border-blue-200 rounded-xl p-4 sm:p-5 space-y-3.5">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h4 class="font-bold text-slate-900 text-sm">Penjemputan Paket Otomatis (Pick Up)</h4>
                                <p class="text-xs text-slate-600 leading-relaxed">
                                    Kurir ekspedisi resmi akan menjemput paket langsung ke alamat toko. Sistem secara otomatis memesan jadwal pick up dan menerbitkan nomor resi (AWB) resmi real-time.
                                </p>
                            </div>
                        </div>

                        <!-- 4 Keunggulan / Informasi Otomatis -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-blue-100 text-[11px] text-slate-700">
                            <div class="flex items-center gap-2 font-medium">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Nomor Resi terbit otomatis</span>
                            </div>
                            <div class="flex items-center gap-2 font-medium">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Label resi thermal A6 siap cetak</span>
                            </div>
                            <div class="flex items-center gap-2 font-medium">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Pelacakan kurir real-time</span>
                            </div>
                            <div class="flex items-center gap-2 font-medium">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Tanpa antre &amp; tanpa biaya pick up</span>
                            </div>
                        </div>
                    </div>

                    <!-- Jika Resi Sudah Ada -->
                    <template x-if="selectedOrder.tracking_number">
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-800 font-semibold">Resi Saat Ini:</span>
                                <span class="font-mono font-bold text-emerald-900 bg-white px-2 py-0.5 rounded border border-emerald-300" x-text="selectedOrder.tracking_number"></span>
                            </div>
                            <a 
                                :href="'{{ url('/admin/orders') }}/' + selectedOrder.id + '/shipping-label'" 
                                target="_blank"
                                class="text-xs text-blue-700 hover:text-blue-900 font-semibold underline flex items-center gap-1"
                            >
                                <span>Cetak Ulang Label</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </template>
                    <!-- Status Notifikasi saat sedang proses booking kurir -->
                    <div x-show="isSubmittingPickup" style="display: none;" class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-3 text-xs text-blue-900 shadow-xs animate-pulse">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 animate-truck-drive" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M1 4a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v9H1V4z" opacity="0.9"/>
                                <path d="M14 6h3.586a1 1 0 0 1 .707.293l3.414 3.414a1 1 0 0 1 .293.707V13a1 1 0 0 1-1 1H14V6z"/>
                                <circle cx="5.5" cy="17.5" r="2.5"/>
                                <circle cx="17.5" cy="17.5" r="2.5"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5 flex-1 min-w-0">
                            <p class="font-bold text-slate-900">Sedang Booking Kurir &amp; Menerbitkan Resi Real-Time...</p>
                            <p class="text-[11px] text-blue-700">Mohon jangan menutup jendela ini, sistem sedang mendaftarkan penjemputan ke kurir ekspedisi.</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                    <button 
                        type="button" 
                        @click="showInputResiModal = false; isSubmittingPickup = false" 
                        :disabled="isSubmittingPickup"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Batal
                    </button>

                    <form 
                        :action="'{{ url('/admin/orders') }}/' + selectedOrder.id + '/pickup'" 
                        method="POST" 
                        class="inline"
                        @submit="isSubmittingPickup = true"
                    >
                        @csrf
                        <button 
                            type="submit" 
                            :disabled="isSubmittingPickup"
                            class="relative overflow-hidden font-bold text-xs sm:text-sm py-2.5 px-5 rounded-xl transition-all shadow-md flex items-center justify-center gap-2 text-white"
                            :class="isSubmittingPickup ? 'bg-gradient-to-r from-blue-700 to-indigo-700 cursor-wait shadow-blue-500/20' : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-blue-500/25 active:scale-[0.98] cursor-pointer'"
                        >
                            <!-- State Normal (Sebelum Klik) -->
                            <span x-show="!isSubmittingPickup" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Serahkan ke Kurir &amp; Terbitkan Resi</span>
                            </span>

                            <!-- State Loading (Truk Ekspedisi Berjalan) -->
                            <span x-show="isSubmittingPickup" style="display: none;" class="flex items-center gap-2.5">
                                <span class="relative flex items-center">
                                    <!-- Garis Kecepatan Angin / Jalan di Belakang Mobil -->
                                    <span class="inline-flex flex-col gap-0.5 mr-1.5 opacity-90">
                                        <span class="w-2.5 h-0.5 bg-blue-200 rounded-full animate-speed-1"></span>
                                        <span class="w-4 h-0.5 bg-white rounded-full animate-speed-2"></span>
                                        <span class="w-2 h-0.5 bg-sky-200 rounded-full animate-speed-3"></span>
                                    </span>
                                    <!-- Mobil Truk Ekspedisi Bergerak & Bergetar -->
                                    <span class="inline-block animate-truck-drive">
                                        <svg class="w-5 h-5 text-white filter drop-shadow-xs" viewBox="0 0 24 24" fill="currentColor">
                                            <!-- Bak Truk / Kargo -->
                                            <path d="M1 4a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v9H1V4z" opacity="0.9" />
                                            <!-- Kepala / Kabin Truk -->
                                            <path d="M14 6h3.586a1 1 0 0 1 .707.293l3.414 3.414a1 1 0 0 1 .293.707V13a1 1 0 0 1-1 1H14V6z" />
                                            <!-- Roda Depan & Belakang -->
                                            <circle cx="5.5" cy="17.5" r="2.5" class="text-white" />
                                            <circle cx="17.5" cy="17.5" r="2.5" class="text-white" />
                                            <circle cx="5.5" cy="17.5" r="1" fill="#1d4ed8" />
                                            <circle cx="17.5" cy="17.5" r="1" fill="#1d4ed8" />
                                            <!-- Bumper & Sasis -->
                                            <rect x="2" y="14" width="2" height="1.5" fill="currentColor" />
                                            <rect x="9" y="14" width="5" height="1.5" fill="currentColor" />
                                            <rect x="21" y="14" width="2" height="1.5" fill="currentColor" />
                                        </svg>
                                    </span>
                                </span>
                                <span class="tracking-tight font-bold text-white flex items-center gap-1">
                                    <span>Menghubungi Kurir Ekspedisi</span>
                                    <span class="inline-flex">
                                        <span class="animate-pulse">.</span>
                                        <span class="animate-pulse" style="animation-delay: 200ms;">.</span>
                                        <span class="animate-pulse" style="animation-delay: 400ms;">.</span>
                                    </span>
                                </span>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <!-- MODAL CETAK SHIPPING LABEL AWB (THERMAL A6 / SHOPEE-STYLE) -->
    <div 
        x-show="showAwbPrintModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/70 backdrop-blur-xs"
        style="display: none;"
    >
        <template x-if="selectedOrder">
            <div @click.outside="showAwbPrintModal = false" class="bg-white border border-slate-300 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl space-y-0 text-slate-900 font-sans">
                <!-- Label Header -->
                <div class="bg-slate-900 text-white p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="bg-blue-600 text-white font-semibold text-xs px-2.5 py-1 rounded" x-text="selectedOrder.courier"></span>
                        <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">CASHLESS AWB</span>
                    </div>
                    <button type="button" @click="showAwbPrintModal = false" class="text-slate-400 hover:text-white font-bold">✕</button>
                </div>

                <!-- Label Content Body (Persis Shopee Airway Bill) -->
                <div class="p-5 space-y-4 text-xs border-b border-slate-200">
                    <!-- Barcode Resi -->
                    <div class="text-center py-3 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-widest">NOMOR RESI PENGIRIMAN</span>
                        <h2 class="text-xl font-bold font-mono tracking-wider text-slate-900" x-text="selectedOrder.tracking_number || selectedOrder.invoice_number"></h2>
                        <!-- Simulated Barcode Lines -->
                        <div class="flex justify-center items-center gap-0.5 h-10 py-1">
                            <div class="w-1 h-full bg-slate-900"></div><div class="w-0.5 h-full bg-slate-900"></div>
                            <div class="w-1.5 h-full bg-slate-900"></div><div class="w-0.5 h-full bg-slate-900"></div>
                            <div class="w-2 h-full bg-slate-900"></div><div class="w-1 h-full bg-slate-900"></div>
                            <div class="w-0.5 h-full bg-slate-900"></div><div class="w-1.5 h-full bg-slate-900"></div>
                            <div class="w-2 h-full bg-slate-900"></div><div class="w-1 h-full bg-slate-900"></div>
                        </div>
                    </div>

                    <!-- Grid Data Pengirim & Penerima (Real Store Data) -->
                    <div class="grid grid-cols-2 gap-3 text-[11px] border border-slate-200 rounded-xl p-3 bg-slate-50/50">
                        <div class="space-y-0.5">
                            <span class="font-bold text-slate-500 uppercase block text-[9px]">PENGIRIM (TOKO):</span>
                            <p class="font-semibold text-slate-900">{{ $storeSetting->store_name ?? 'Toko Online Official' }}</p>
                            <p class="text-slate-600">{{ $storeSetting->sender_phone ?: ($storeSetting->phone ?? '-') }}</p>
                            <p class="text-slate-500 text-[10px] leading-tight">
                                {{ $storeSetting->address_detail ? $storeSetting->address_detail . ', ' : '' }}{{ $storeSetting->village ? $storeSetting->village . ', ' : '' }}{{ $storeSetting->district ? $storeSetting->district . ', ' : '' }}{{ $storeSetting->city ?? '' }}{{ $storeSetting->province ? ', ' . $storeSetting->province : '' }}{{ $storeSetting->postal_code ? ' (' . $storeSetting->postal_code . ')' : '' }}
                            </p>
                        </div>
                        <div class="space-y-0.5 border-l border-slate-200 pl-3">
                            <span class="font-bold text-slate-500 uppercase block text-[9px]">PENERIMA (PELANGGAN):</span>
                            <p class="font-semibold text-slate-900" x-text="selectedOrder.recipient_name"></p>
                            <p class="text-slate-600" x-text="selectedOrder.recipient_phone"></p>
                            <p class="text-slate-600 text-[10px] leading-tight" x-text="selectedOrder.shipping_address"></p>
                        </div>
                    </div>

                    <!-- Items Summary List -->
                    <div class="space-y-1 text-[11px]">
                        <span class="font-bold text-slate-500 block uppercase text-[10px]">ISI PAKET:</span>
                        <div class="bg-white border border-slate-200 rounded-xl p-2.5 space-y-1">
                            <template x-for="item in selectedOrder.items" :key="item.id">
                                <div class="flex justify-between font-medium text-slate-800 text-[11px]">
                                    <span x-text="item.quantity + 'x ' + item.product_name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="p-4 bg-slate-50 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200">Cashless Paid - Verified</span>
                    <div class="flex gap-2">
                        <button type="button" @click="showAwbPrintModal = false" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 cursor-pointer">Tutup</button>
                        <button type="button" @click="window.print()" class="px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs cursor-pointer flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Cetak Label</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

</div>
@endsection
