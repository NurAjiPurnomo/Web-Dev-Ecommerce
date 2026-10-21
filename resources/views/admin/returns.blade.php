@extends('admin.layout')

@section('title', 'Pengembalian Barang & Dana')

@section('content')
@php
    $returnsMap = [];
    foreach ($returns as $r) {
        $firstItem = $r->order ? $r->order->items->first() : null;
        $returnsMap[$r->id] = [
            'id'                          => $r->id,
            'order_id'                    => $r->order_id,
            'invoice_number'              => $r->order->invoice_number ?? ('Order #' . $r->order_id),
            'order_total'                 => $r->order ? $r->order->formatted_total : '-',
            'customer_name'               => $r->user->name ?? ($r->order->recipient_name ?? 'Pelanggan'),
            'customer_phone'              => $r->user->phone ?? ($r->order->recipient_phone ?? '-'),
            'customer_email'              => $r->user->email ?? '-',
            'reason'                      => str_replace('_', ' ', strtoupper($r->reason ?? '-')),
            'description'                 => $r->description ?: 'Tidak ada rincian keluhan.',
            'solution_type'               => $r->solution_type ?? 'refund',
            'status'                      => $r->status,
            'bank_name'                   => $r->bank_name ?: '-',
            'bank_account_number'         => $r->bank_account_number ?: '-',
            'bank_account_name'           => $r->bank_account_name ?: '-',
            'return_tracking_number'      => $r->return_tracking_number ?: '',
            'return_shipping_receipt'     => $r->return_shipping_receipt ? asset($r->return_shipping_receipt) : null,
            'replacement_tracking_number' => $r->replacement_tracking_number ?: '',
            'photo_proof'                 => $r->photo_proof ? asset($r->photo_proof) : null,
            'video_proof'                 => $r->video_proof ? asset($r->video_proof) : null,
            'admin_notes'                 => $r->admin_notes ?: '',
            'created_at_fmt'              => $r->created_at ? $r->created_at->format('d M Y, H:i') : '-',
            'first_product_name'          => $firstItem->product_name ?? 'Produk Pesanan',
            'first_product_image'         => $firstItem ? $firstItem->image : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80',
            'items_count'                 => $r->order ? $r->order->items->count() : 0,
        ];
    }
@endphp

<div x-data="{ returnsMap: @js($returnsMap), activeReturn: null, showDetailModal: false, showMediaModal: false, activeMediaUrl: '', activeMediaType: 'image', adminNoteInput: '', replacementTrackingInput: '' }" class="space-y-6">

    <!-- Header Section (Konsisten 100% dengan Katalog Produk, Pesanan & Dashboard) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Pengembalian Barang &amp; Dana</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola klaim komplain pembeli, rekening refund dana, peninjauan unboxing, dan pengiriman barang pengganti.</p>
        </div>
        <div>
            <a 
                href="{{ route('admin.returns.export', request()->query()) }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                title="Unduh Laporan Retur & Komplain Format CSV"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-xs">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Ringkasan Komplain (4 KPI Cards - Persis Grid Katalog Produk & Pesanan) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Klaim -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Total Klaim</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($allCount, 0, ',', '.') }}</div>
            <p class="text-xs text-slate-500 mt-0.5">Semua pengajuan retur</p>
        </div>

        <!-- Menunggu Tinjauan -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Menunggu Tinjauan</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($pendingCount, 0, ',', '.') }}</div>
            <p class="text-xs text-amber-600 font-medium mt-0.5">Perlu tindakan admin</p>
        </div>

        <!-- Menunggu Fisik / Dalam Proses -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Menunggu Fisik</span>
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($processCount, 0, ',', '.') }}</div>
            <p class="text-xs text-sky-600 font-medium mt-0.5">Paket dikirim pembeli</p>
        </div>

        <!-- Klaim Selesai -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Klaim Selesai</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($completedCount, 0, ',', '.') }}</div>
            <p class="text-xs text-emerald-600 font-medium mt-0.5">Refund / tukar sukses</p>
        </div>
    </div>

    <!-- Main Content Container: Status Tabs, Filters & Returns Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <!-- Status Tabs (Shopee / Tokopedia Seller Centre Pattern) -->
        <div class="border-b border-slate-200 px-4 sm:px-6 pt-2 bg-white">
            <nav class="flex items-center gap-6 overflow-x-auto no-scrollbar -mb-px text-xs sm:text-sm font-medium">
                @php
                    $tabs = [
                        'all'       => ['label' => 'Semua', 'count' => $allCount],
                        'pending'   => ['label' => 'Menunggu Tinjauan', 'count' => $pendingCount, 'highlight' => 'amber'],
                        'process'   => ['label' => 'Disetujui (Menunggu Fisik)', 'count' => $processCount, 'highlight' => 'sky'],
                        'completed' => ['label' => 'Selesai', 'count' => $completedCount],
                        'rejected'  => ['label' => 'Ditolak', 'count' => $rejectedCount],
                    ];
                @endphp

                @foreach($tabs as $tabKey => $tabData)
                    @php
                        $isActive = ($status === $tabKey);
                        $queryParams = request()->query();
                        $queryParams['status'] = $tabKey;
                        $tabUrl = route('admin.returns', $queryParams);
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

        <!-- Search & Filter Toolbar (Persis Desain Katalog Produk & Pesanan) -->
        <div class="p-4 sm:p-5 bg-slate-50/50 border-b border-slate-200">
            <form action="{{ route('admin.returns') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
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
                            placeholder="Cari no. invoice, resi retur, pembeli, rekening refund..." 
                            class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all shadow-xs"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Filter Solusi Dropdown -->
                    <div class="sm:w-44">
                        <select 
                            name="solution" 
                            onchange="this.form.submit()" 
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all shadow-xs cursor-pointer"
                        >
                            <option value="all" {{ $solution === 'all' ? 'selected' : '' }}>Semua Solusi</option>
                            <option value="refund" {{ $solution === 'refund' ? 'selected' : '' }}>Refund Dana</option>
                            <option value="exchange" {{ $solution === 'exchange' ? 'selected' : '' }}>Tukar Barang</option>
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
                @if($search || $solution !== 'all' || $period !== 'all' || $status !== 'all')
                    <a 
                        href="{{ route('admin.returns') }}" 
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

        <!-- Desktop Returns Table View (md+) - Format Standar Marketplace Profesional -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-medium text-xs">
                    <tr>
                        <th class="px-4 py-3 font-medium">No. Invoice &amp; Waktu</th>
                        <th class="px-3 py-3 font-medium">Pembeli</th>
                        <th class="px-3 py-3 font-medium">Produk &amp; Alasan</th>
                        <th class="px-3 py-3 font-medium">Solusi &amp; Rekening / Resi</th>
                        <th class="px-3 py-3 font-medium text-center">Bukti Fisik</th>
                        <th class="px-3 py-3 font-medium">Status Klaim</th>
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($returns as $r)
                        @php
                            $isPending   = ($r->status === 'pending');
                            $isProcess   = in_array($r->status, ['approved', 'approved_pending_shipment']);
                            $isCompleted = ($r->status === 'completed');
                            $isRejected  = ($r->status === 'rejected');
                            $isExchange  = in_array($r->solution_type ?? 'refund', ['exchange', 'replacement']);
                            $firstItem   = $r->order ? $r->order->items->first() : null;
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <!-- Invoice & Waktu -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-semibold text-slate-900 text-xs">#{{ $r->order->invoice_number ?? ('Order #' . $r->order_id) }}</span>
                                    <button 
                                        type="button" 
                                        @click="navigator.clipboard.writeText('{{ $r->order->invoice_number ?? $r->order_id }}'); alert('Nomor invoice disalin!')" 
                                        class="text-slate-400 hover:text-slate-700 cursor-pointer" 
                                        title="Salin No. Invoice"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $r->created_at ? $r->created_at->format('d M Y, H:i') : '-' }}
                                </div>
                            </td>

                            <!-- Pembeli -->
                            <td class="px-3 py-3">
                                <div class="font-medium text-slate-900 truncate max-w-[130px] text-xs" title="{{ $r->user->name ?? ($r->order->recipient_name ?? 'Pelanggan') }}">
                                    {{ $r->user->name ?? ($r->order->recipient_name ?? 'Pelanggan') }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $r->user->phone ?? ($r->order->recipient_phone ?? '-') }}
                                </div>
                            </td>

                            <!-- Produk & Alasan -->
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2.5">
                                    <img 
                                        src="{{ $firstItem ? $firstItem->image : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80' }}" 
                                        alt="{{ $firstItem->product_name ?? 'Produk' }}" 
                                        class="w-10 h-10 rounded-lg object-cover border border-slate-200 bg-slate-100 shrink-0"
                                    >
                                    <div class="min-w-0 max-w-[180px] lg:max-w-[200px]">
                                        <h5 class="font-medium text-slate-900 truncate text-xs" title="{{ $firstItem->product_name ?? 'Produk' }}">
                                            {{ $firstItem->product_name ?? 'Produk Pesanan' }}
                                        </h5>
                                        <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                                            Alasan: <span class="text-slate-800 font-semibold">{{ str_replace('_', ' ', strtoupper($r->reason ?? '-')) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Solusi & Rekening / Resi -->
                            <td class="px-3 py-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        @if($isExchange)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                🔄 Tukar Barang Baru
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                💰 Refund Dana
                                            </span>
                                        @endif
                                    </div>

                                    @if(!$isExchange)
                                        @if($r->bank_account_number)
                                            <div class="text-[11px] text-slate-700 font-mono bg-slate-50 p-1.5 rounded border border-slate-200/80">
                                                <span class="font-bold text-slate-900">{{ $r->bank_name ?? 'Bank' }}</span>: <span class="font-bold text-emerald-700">{{ $r->bank_account_number }}</span>
                                                <span class="block text-[10px] text-slate-500 font-sans truncate">a.n {{ $r->bank_account_name }}</span>
                                            </div>
                                        @else
                                            <div class="text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 font-medium">
                                                ⚠️ Rekening belum diisi
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-[10px] text-slate-500 italic">
                                            Kirim barang baru (Tanpa transfer refund)
                                        </div>
                                    @endif

                                    @if($r->return_tracking_number)
                                        <div class="text-[10px] font-mono flex items-center gap-1 mt-0.5">
                                            <span class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded font-bold border border-blue-200">Resi Biteship: {{ $r->return_tracking_number }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Bukti Fisik Thumbnail Icons -->
                            <td class="px-3 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($r->photo_proof)
                                        <button 
                                            type="button" 
                                            @click="activeMediaUrl = '{{ asset($r->photo_proof) }}'; activeMediaType = 'image'; showMediaModal = true"
                                            class="p-1 rounded-md bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 transition-colors cursor-pointer border border-slate-200"
                                            title="Lihat Foto Fisik"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </button>
                                    @endif
                                    @if($r->video_proof)
                                        <button 
                                            type="button" 
                                            @click="activeMediaUrl = '{{ asset($r->video_proof) }}'; activeMediaType = 'video'; showMediaModal = true"
                                            class="p-1 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors cursor-pointer border border-rose-200"
                                            title="Tonton Video Unboxing"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    @endif
                                    @if(!$r->photo_proof && !$r->video_proof)
                                        <span class="text-slate-300 text-xs">-</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status Klaim -->
                            <td class="px-3 py-3 whitespace-nowrap">
                                @if($isPending)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                        <span>Menunggu Tinjauan</span>
                                    </span>
                                @elseif($isProcess)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                        <span>Menunggu Fisik</span>
                                    </span>
                                @elseif($isCompleted)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Selesai</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Ditolak</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        type="button" 
                                        @click="activeReturn = returnsMap[{{ $r->id }}]; adminNoteInput = activeReturn.admin_notes || ''; replacementTrackingInput = activeReturn.replacement_tracking_number || ''; showDetailModal = true"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-100 rounded-lg transition-colors border border-slate-200 cursor-pointer"
                                        title="Rincian & Tinjauan Retur"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Tinjau</span>
                                    </button>

                                    @if($isPending)
                                        <form action="{{ route('admin.returns.process', $r->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button 
                                                type="submit" 
                                                onclick="return confirm('Setujui pengajuan klaim retur pesanan #{{ $r->order->invoice_number ?? $r->order_id }}? Pembeli akan dipersilakan mengirimkan fisik barang ke gudang toko.')" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100 bg-emerald-50 rounded-lg transition-colors border border-emerald-200 cursor-pointer"
                                                title="Setujui Klaim"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Setujui</span>
                                            </button>
                                        </form>
                                    @elseif($isProcess)
                                        @if($isExchange)
                                            <form action="{{ route('admin.returns.process', $r->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="generate_resi2">
                                                <button 
                                                    type="submit" 
                                                    onclick="return confirm('Terbitkan Resi 2 dan kirim produk pengganti baru untuk pesanan #{{ $r->order->invoice_number ?? $r->order_id }}?')" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-purple-700 hover:bg-purple-100 bg-purple-50 rounded-lg transition-colors border border-purple-200 cursor-pointer"
                                                    title="Terbitkan Resi 2"
                                                >
                                                    <span>Kirim Pengganti</span>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.returns.process', $r->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="completed">
                                                <button 
                                                    type="submit" 
                                                    onclick="return confirm('Konfirmasi bahwa barang fisik telah diterima di gudang dan dana refund telah dicairkan ke pembeli? Stok produk akan otomatis dikembalikan ke etalase.')" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100 bg-emerald-50 rounded-lg transition-colors border border-emerald-200 cursor-pointer"
                                                    title="Selesaikan & Refund"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Selesai &amp; Refund</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                                    </div>
                                    <p class="text-xs font-medium text-slate-700">Tidak ada pengajuan retur ditemukan</p>
                                    <p class="text-xs text-slate-400">Belum ada klaim komplain pembeli atau coba ubah kriteria filter yang dipilih.</p>
                                    @if($search || $solution !== 'all' || $period !== 'all' || $status !== 'all')
                                        <a href="{{ route('admin.returns') }}" class="inline-block mt-2 text-xs text-blue-600 hover:text-blue-700 font-medium">Reset Semua Filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Returns Cards View (< md) - Format Persis Katalog Produk & Pesanan -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($returns as $r)
                @php
                    $isPending   = ($r->status === 'pending');
                    $isProcess   = in_array($r->status, ['approved', 'approved_pending_shipment']);
                    $isCompleted = ($r->status === 'completed');
                    $isRejected  = ($r->status === 'rejected');
                    $isExchange  = (($r->solution_type ?? 'refund') === 'exchange');
                    $firstItem   = $r->order ? $r->order->items->first() : null;
                @endphp
                <div class="p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <img 
                            src="{{ $firstItem ? $firstItem->image : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80' }}" 
                            alt="{{ $firstItem->product_name ?? 'Produk' }}" 
                            class="w-14 h-14 rounded-lg object-cover border border-slate-200 bg-slate-100 shrink-0"
                        >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-mono text-xs font-bold text-slate-900">#{{ $r->order->invoice_number ?? $r->order_id }}</span>
                                @if($isPending)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-800">Menunggu Tinjauan</span>
                                @elseif($isProcess)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-sky-100 text-sky-800">Menunggu Fisik</span>
                                @elseif($isCompleted)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 text-emerald-800">Selesai</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-100 text-rose-800">Ditolak</span>
                                @endif
                            </div>
                            <h4 class="font-medium text-slate-900 text-xs mt-1 truncate">{{ $firstItem->product_name ?? 'Produk Pesanan' }}</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Pembeli: {{ $r->user->name ?? 'Pelanggan' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-lg text-xs border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-500 block">Solusi Klaim</span>
                            <span class="font-semibold {{ $isExchange ? 'text-purple-700' : 'text-emerald-700' }} text-xs">
                                {{ $isExchange ? '🔄 Tukar Barang' : '💰 Refund Dana' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block">Alasan</span>
                            <span class="font-medium text-slate-800 text-xs truncate block">{{ str_replace('_', ' ', strtoupper($r->reason ?? '-')) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button 
                            type="button" 
                            @click="activeReturn = returnsMap[{{ $r->id }}]; adminNoteInput = activeReturn.admin_notes || ''; replacementTrackingInput = activeReturn.replacement_tracking_number || ''; showDetailModal = true"
                            class="flex-1 text-xs font-medium text-slate-700 hover:text-slate-900 bg-white py-1.5 rounded-lg border border-slate-200 transition-colors text-center cursor-pointer"
                        >
                            Detail
                        </button>

                        @if($isPending)
                            <form action="{{ route('admin.returns.process', $r->id) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" onclick="return confirm('Setujui pengajuan klaim retur ini?')" class="w-full text-xs font-medium text-emerald-700 bg-emerald-50 py-1.5 rounded-lg border border-emerald-200 text-center cursor-pointer">
                                    Setujui
                                </button>
                            </form>
                        @elseif($isProcess)
                            @if($isExchange)
                                <form action="{{ route('admin.returns.process', $r->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="status" value="generate_resi2">
                                    <button type="submit" onclick="return confirm('Kirim pengganti baru untuk pesanan ini?')" class="w-full text-xs font-medium text-purple-700 bg-purple-50 py-1.5 rounded-lg border border-purple-200 text-center cursor-pointer">
                                        Kirim Pengganti
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.returns.process', $r->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" onclick="return confirm('Selesaikan retur dan konfirmasi refund dana?')" class="w-full text-xs font-medium text-emerald-700 bg-emerald-50 py-1.5 rounded-lg border border-emerald-200 text-center cursor-pointer">
                                        Refund Selesai
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    Tidak ada pengajuan retur ditemukan.
                </div>
            @endforelse
        </div>

        <!-- Pagination Footer (Persis Katalog Produk & Pesanan: Kotak Biru di Kanan Bawah) -->
        @if ($returns->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-slate-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan <span class="text-slate-900 font-semibold">{{ $returns->firstItem() }}</span> - <span class="text-slate-900 font-semibold">{{ $returns->lastItem() }}</span> dari <span class="text-slate-900 font-semibold">{{ $returns->total() }}</span> pengajuan
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    {{-- Previous Page Link --}}
                    @if ($returns->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center text-slate-300 cursor-not-allowed select-none">
                            <svg class="w-4 h-4 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $returns->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors select-none" title="Sebelumnya">
                            <svg class="w-4 h-4 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $returns->currentPage();
                        $lastPage = $returns->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $returns->url(1) }}" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors select-none">1</a>
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
                            <a href="{{ $returns->url($i) }}" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors select-none">
                                {{ $i }}
                            </a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="w-6 h-7 sm:h-8 flex items-center justify-center text-xs sm:text-sm text-slate-400 select-none">...</span>
                        @endif
                        <a href="{{ $returns->url($lastPage) }}" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-xs sm:text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors select-none">{{ $lastPage }}</a>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($returns->hasMorePages())
                        <a href="{{ $returns->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors select-none" title="Selanjutnya">
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

    <!-- 🔍 MODAL DETAIL & REVIEW KLAIM RETUR (LENGKAP & MUDAH DIPAHAMI ADMIN) -->
    <div 
        x-show="showDetailModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <template x-if="activeReturn">
            <div @click.outside="showDetailModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl p-5 sm:p-6 space-y-4 max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-slate-900 text-base">Tinjauan Klaim Pengembalian</h3>
                            <span 
                                class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                :class="activeReturn.solution_type === 'exchange' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800'"
                                x-text="activeReturn.solution_type === 'exchange' ? '🔄 Tukar Barang' : '💰 Refund Dana'"
                            ></span>
                        </div>
                        <p class="text-xs font-mono text-slate-500 mt-0.5" x-text="'No. Invoice: #' + activeReturn.invoice_number"></p>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
                </div>

                <!-- SOP Ringkas Alur Retur Toko -->
                <div class="bg-blue-50/70 border border-blue-200/80 rounded-xl p-3 text-[11px] text-blue-900 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-blue-900">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Panduan Operasional Retur Otomatis (Biteship Cashless):</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed text-[11px]">
                        1. Tinjau foto fisik &amp; video unboxing pembeli. <br>
                        2. Jika komplain valid, klik <strong>"Setujui Klaim"</strong> — Sistem akan <strong>otomatis menerbitkan Nomor Resi Retur Biteship (Cashless)</strong> untuk pembeli. Pembeli tinggal drop-off ke counter kurir tanpa bayar tunai di gerai. <br>
                        3. Setelah barang sampai dan dicek di gudang, klik <strong>"Selesaikan Klaim"</strong> untuk mencairkan dana refund atau kirimkan produk pengganti baru.
                    </p>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Status & Waktu Pengajuan -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Waktu Pengajuan</span>
                            <span class="font-semibold text-slate-800" x-text="activeReturn.created_at_fmt"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block text-[10px]">Status Saat Ini</span>
                            <span class="font-bold uppercase tracking-wider text-blue-700" x-text="activeReturn.status"></span>
                        </div>
                    </div>

                    <!-- Pembeli & Produk yang Dikomplain -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-0.5">
                            <span class="font-bold text-slate-500 uppercase block text-[10px]">DATA PEMBELI:</span>
                            <p class="font-semibold text-slate-900" x-text="activeReturn.customer_name"></p>
                            <p class="text-slate-600 font-mono text-[11px]" x-text="activeReturn.customer_phone"></p>
                            <p class="text-slate-500 text-[10px]" x-text="activeReturn.customer_email"></p>
                        </div>
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-0.5">
                            <span class="font-bold text-slate-500 uppercase block text-[10px]">PRODUK YANG DIKOMPLAIN:</span>
                            <p class="font-semibold text-slate-900 truncate" x-text="activeReturn.first_product_name"></p>
                            <p class="text-slate-500 text-[10px]">Alasan: <strong class="text-slate-800" x-text="activeReturn.reason"></strong></p>
                            <p class="text-slate-500 text-[10px]">Total Nilai Order: <strong class="text-blue-700" x-text="activeReturn.order_total"></strong></p>
                        </div>
                    </div>

                    <!-- Uraian Keluhan Pembeli -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                        <span class="font-bold text-slate-700 block text-xs uppercase tracking-wider">Uraian Masalah dari Pembeli:</span>
                        <p class="text-slate-800 italic leading-relaxed text-[11px] bg-white p-2.5 rounded-lg border border-slate-200" x-text="'&quot;' + activeReturn.description + '&quot;'"></p>
                    </div>

                    <!-- Rekening Tujuan Refund (Jika Refund Dana) -->
                    <template x-if="activeReturn.solution_type === 'refund'">
                        <div class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-emerald-900 text-xs flex items-center gap-1.5">
                                    💳 Rekening Tujuan Pencairan Refund:
                                </span>
                                <span class="px-2 py-0.5 bg-emerald-700 text-white font-extrabold text-[10px] rounded uppercase" x-text="activeReturn.bank_name || 'Bank'"></span>
                            </div>
                            <template x-if="activeReturn.bank_account_number">
                                <div class="flex items-center justify-between bg-white border border-emerald-200 rounded-xl p-3">
                                    <div>
                                        <div class="font-mono font-bold text-slate-900 text-sm tracking-wide" x-text="activeReturn.bank_account_number"></div>
                                        <div class="text-slate-600 text-xs font-medium mt-0.5" x-text="'a.n. ' + (activeReturn.bank_account_name || '-')"></div>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="navigator.clipboard.writeText(activeReturn.bank_account_number); alert('Nomor rekening ' + activeReturn.bank_account_number + ' disalin!')"
                                        class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[11px] rounded-lg border border-emerald-300 transition-colors cursor-pointer"
                                    >
                                        📋 Salin No Rek
                                    </button>
                                </div>
                            </template>
                            <template x-if="!activeReturn.bank_account_number">
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 flex items-center justify-between gap-2">
                                    <span>⚠️ Nomor rekening belum diisi pembeli saat mengajukan klaim. Hubungi pembeli untuk nomor rekening refund.</span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Info Jika Tukar Barang Baru -->
                    <template x-if="['replacement', 'exchange'].includes(activeReturn.solution_type)">
                        <div class="p-3 bg-purple-50/80 border border-purple-200 rounded-xl text-xs text-purple-900 flex items-center gap-2">
                            <span class="text-lg">🔄</span>
                            <div>
                                <strong class="block text-purple-950 font-bold">Solusi: Tukar Barang Baru (Replacement)</strong>
                                <span class="text-[11px] text-purple-700">Pembeli meminta dikirimkan produk pengganti baru (bukan pengembalian dana tunai).</span>
                            </div>
                        </div>
                    </template>

                    <!-- Bukti Unboxing & Foto Fisik -->
                    <div class="space-y-1.5">
                        <span class="font-bold text-slate-700 block text-xs uppercase tracking-wider">Bukti Fisik &amp; Video Unboxing:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-if="activeReturn.photo_proof">
                                <div class="border border-slate-200 rounded-xl p-2.5 bg-slate-50 space-y-1.5 text-center">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase block">Foto Kondisi Fisik</span>
                                    <a :href="activeReturn.photo_proof" target="_blank" class="block overflow-hidden rounded-lg border border-slate-200 bg-white group">
                                        <img :src="activeReturn.photo_proof" class="w-full h-32 object-cover group-hover:scale-105 transition-transform" alt="Bukti Foto">
                                    </a>
                                </div>
                            </template>
                            <template x-if="activeReturn.video_proof">
                                <div class="border border-slate-200 rounded-xl p-2.5 bg-slate-50 space-y-1.5 text-center">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase block">Video Unboxing Pembeli</span>
                                    <video controls class="w-full h-32 rounded-lg border border-slate-200 bg-black">
                                        <source :src="activeReturn.video_proof" type="video/mp4">
                                        Browser tidak mendukung video.
                                    </video>
                                </div>
                            </template>
                            <template x-if="!activeReturn.photo_proof && !activeReturn.video_proof">
                                <div class="col-span-2 p-3 text-center text-slate-400 bg-slate-50 rounded-xl border border-slate-200 italic">
                                    Pembeli tidak melampirkan file foto atau video unboxing.
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Alamat Toko Tujuan Pengembalian Paket -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                        <span class="font-bold text-slate-700 block text-xs uppercase tracking-wider">Alamat Gudang Toko untuk Pengembalian Paket:</span>
                        <p class="font-semibold text-slate-900">{{ $storeSetting->store_name ?? 'Toko Online Official' }} ({{ $storeSetting->sender_phone ?: ($storeSetting->phone ?? '-') }})</p>
                        <p class="text-slate-600 text-[11px] leading-relaxed">
                            {{ $storeSetting->address_detail ? $storeSetting->address_detail . ', ' : '' }}
                            {{ $storeSetting->village ? $storeSetting->village . ', ' : '' }}
                            {{ $storeSetting->district ? $storeSetting->district . ', ' : '' }}
                            {{ $storeSetting->city ?? '' }}{{ $storeSetting->province ? ', ' . $storeSetting->province : '' }}
                            {{ $storeSetting->postal_code ? ' (' . $storeSetting->postal_code . ')' : '' }}
                        </p>
                    </div>

                    <!-- Resi & Pelacakan Pengembalian -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                        <span class="font-bold text-slate-700 block text-xs uppercase tracking-wider">Logistik &amp; Resi Retur (Biteship Cashless):</span>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-slate-500 block text-[10px]">Nomor Resi Retur (Biteship Drop-off):</span>
                                <span class="font-mono font-bold text-slate-900 text-xs" x-text="activeReturn.return_tracking_number || 'Otomatis dibuat saat klaim Disetujui'"></span>
                            </div>
                            <template x-if="activeReturn.return_tracking_number">
                                <a :href="'https://biteship.com/id/cek-resi?waybill=' + activeReturn.return_tracking_number" target="_blank" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-[10px]">
                                    Lacak Paket 🌐
                                </a>
                            </template>
                        </div>

                        <template x-if="activeReturn.solution_type === 'exchange'">
                            <div class="pt-2 border-t border-slate-200 space-y-1.5">
                                <label class="block text-slate-700 font-semibold text-[11px]">Nomor Resi Produk Pengganti (Resi 2):</label>
                                <input 
                                    type="text" 
                                    x-model="replacementTrackingInput"
                                    placeholder="Masukkan nomor resi produk baru (misal: JP1234567890 / JNE009988)" 
                                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 font-mono text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600"
                                >
                                <span class="text-[10px] text-slate-400 block">Nomor resi ini akan dikirimkan langsung ke pelanggan untuk melacak kiriman unit pengganti.</span>
                            </div>
                        </template>
                    </div>

                    <!-- Form Catatan Admin -->
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Catatan Admin untuk Pembeli (Opsional):</label>
                        <textarea 
                            x-model="adminNoteInput"
                            rows="2"
                            placeholder="Tuliskan pesan / instruksi tambahan yang akan terkirim ke notifikasi akun pembeli..."
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        ></textarea>
                    </div>
                </div>

                <!-- Modal Action Buttons -->
                <div class="pt-3 flex flex-wrap justify-end items-center gap-2 border-t border-slate-100">
                    <template x-if="activeReturn.status === 'pending'">
                        <div class="flex items-center gap-2">
                            <form :action="'{{ url('/admin/returns') }}/' + activeReturn.id + '/process'" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <input type="hidden" name="admin_notes" :value="adminNoteInput">
                                <button 
                                    type="submit" 
                                    onclick="return confirm('Tolak klaim pengembalian ini?')" 
                                    class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-xl text-xs border border-rose-200 cursor-pointer"
                                >
                                    ✕ Tolak Klaim
                                </button>
                            </form>
                            <form :action="'{{ url('/admin/returns') }}/' + activeReturn.id + '/process'" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <input type="hidden" name="admin_notes" :value="adminNoteInput">
                                <button 
                                    type="submit" 
                                    onclick="return confirm('Setujui klaim pengembalian ini? Pembeli akan diarahkan mengirim barang ke gudang toko.')" 
                                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs shadow-xs cursor-pointer"
                                >
                                    ✓ Setujui Klaim
                                </button>
                            </form>
                        </div>
                    </template>

                    <template x-if="activeReturn.status === 'approved' || activeReturn.status === 'approved_pending_shipment'">
                        <div class="flex items-center gap-2">
                            <template x-if="activeReturn.solution_type === 'exchange'">
                                <form :action="'{{ url('/admin/returns') }}/' + activeReturn.id + '/process'" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="generate_resi2">
                                    <input type="hidden" name="admin_notes" :value="adminNoteInput">
                                    <input type="hidden" name="replacement_tracking_number" :value="replacementTrackingInput">
                                    <button 
                                        type="submit" 
                                        onclick="return confirm('Terbitkan Resi 2 dan kirim produk pengganti baru via kurir?')" 
                                        class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl text-xs shadow-xs cursor-pointer"
                                    >
                                        🚚 Kirim Barang Pengganti
                                    </button>
                                </form>
                            </template>
                            <template x-if="activeReturn.solution_type !== 'exchange'">
                                <form :action="'{{ url('/admin/returns') }}/' + activeReturn.id + '/process'" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <input type="hidden" name="admin_notes" :value="adminNoteInput">
                                    <button 
                                        type="submit" 
                                        onclick="return confirm('Konfirmasi bahwa barang retur fisik telah tiba di toko dan dana refund telah berhasil dicairkan ke rekening pembeli?')" 
                                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs shadow-xs cursor-pointer"
                                    >
                                        💰 Selesaikan &amp; Konfirmasi Refund
                                    </button>
                                </form>
                            </template>
                        </div>
                    </template>

                    <button 
                        type="button" 
                        @click="showDetailModal = false" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-xl text-xs transition-colors cursor-pointer"
                    >
                        Tutup Jendela
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- 📷 MODAL MEDIA PREVIEW (ZOOM FOTO / VIDEO FULL SCREEN) -->
    <div 
        x-show="showMediaModal" 
        x-transition 
        class="fixed inset-0 z-60 flex items-center justify-center p-3 sm:p-4 bg-slate-900/80 backdrop-blur-xs"
        style="display: none;"
    >
        <div @click.outside="showMediaModal = false" class="bg-black/90 border border-white/20 rounded-2xl max-w-2xl w-full p-4 relative overflow-hidden text-center">
            <button @click="showMediaModal = false" class="absolute top-3 right-3 text-white/70 hover:text-white font-bold text-lg p-1 z-10 cursor-pointer">✕</button>
            <template x-if="activeMediaType === 'image'">
                <img :src="activeMediaUrl" class="max-h-[80vh] mx-auto rounded-lg object-contain shadow-2xl" alt="Preview Foto Bukti">
            </template>
            <template x-if="activeMediaType === 'video'">
                <video controls autoplay class="max-h-[80vh] w-full mx-auto rounded-lg">
                    <source :src="activeMediaUrl" type="video/mp4">
                    Browser tidak mendukung video.
                </video>
            </template>
        </div>
    </div>

</div>
@endsection
