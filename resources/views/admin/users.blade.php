@extends('admin.layout')

@section('title', 'Pelanggan')

@section('content')
<div class="space-y-6" x-data="customerManager()">

    <!-- Header Section (Konsisten dengan Dashboard & Pesanan) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Pelanggan</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola data pembeli terdaftar, riwayat transaksi, dan status akun toko.</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a 
                href="{{ route('admin.users.export', request()->all()) }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg shadow-xs transition-colors cursor-pointer"
                title="Download data pelanggan dalam format CSV / Excel"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
            <div class="text-xs font-medium text-slate-700 bg-white border border-slate-200 px-3.5 py-2 rounded-lg shadow-xs flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>Total: <strong class="text-slate-900 font-semibold">{{ number_format($kpi['total'] ?? 0) }}</strong> Pelanggan</span>
            </div>
        </div>
    </div>

    <!-- 4 Metric Cards: Ringkas Status Pelanggan (Persis Desain Dashboard & Pesanan) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Total Pelanggan -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Total Pelanggan</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($kpi['total'] ?? 0, 0, ',', '.') }}</div>
            <p class="text-xs text-blue-600 font-medium mt-0.5">Semua akun pembeli terdaftar</p>
        </div>

        <!-- 2. Pelanggan Aktif -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Pelanggan Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($kpi['active'] ?? 0, 0, ',', '.') }}</div>
            <p class="text-xs text-emerald-600 font-medium mt-0.5">Status akun aktif berbelanja</p>
        </div>

        <!-- 3. Pernah Belanja -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Pernah Belanja</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($kpi['buyers'] ?? 0, 0, ',', '.') }}</div>
            <p class="text-xs text-purple-600 font-medium mt-0.5">Memiliki riwayat pesanan</p>
        </div>

        <!-- 4. Ditangguhkan -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Ditangguhkan</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-2">{{ number_format($kpi['suspended'] ?? 0, 0, ',', '.') }}</div>
            <p class="text-xs text-rose-600 font-medium mt-0.5">Akses akun dibatasi</p>
        </div>
    </div>

    <!-- Main Content Container: Status Tabs, Filters & Customer Table (Persis Halaman Pesanan) -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        
        <!-- Status Tabs (Shopee / Tokopedia Seller Centre Pattern) -->
        <div class="border-b border-slate-200 px-4 sm:px-6 pt-2 bg-white">
            <nav class="flex items-center gap-6 overflow-x-auto no-scrollbar -mb-px text-xs sm:text-sm font-medium">
                @php
                    $tabs = [
                        'semua'     => ['label' => 'Semua', 'count' => $kpi['total'] ?? 0],
                        'active'    => ['label' => 'Aktif', 'count' => $kpi['active'] ?? 0],
                        'buyer'     => ['label' => 'Pernah Belanja', 'count' => $kpi['buyers'] ?? 0, 'highlight' => 'purple'],
                        'suspended' => ['label' => 'Ditangguhkan', 'count' => $kpi['suspended'] ?? 0, 'highlight' => 'rose'],
                    ];
                @endphp

                @foreach($tabs as $tabKey => $tabData)
                    @php
                        $isActive = ($status === $tabKey);
                        $queryParams = request()->query();
                        $queryParams['status'] = $tabKey;
                        $queryParams['page'] = 1;
                        $tabUrl = route('admin.users', $queryParams);
                    @endphp
                    <a 
                        href="{{ $tabUrl }}" 
                        class="py-3 border-b-2 whitespace-nowrap transition-colors flex items-center gap-1.5 {{ $isActive ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}"
                    >
                        <span>{{ $tabData['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[11px] {{ $isActive ? 'bg-blue-100 text-blue-700 font-semibold' : 'bg-slate-100 text-slate-600' }}">
                            {{ $tabData['count'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Panduan Ringkas Operasional CRM Toko -->
        <div class="mx-4 sm:mx-6 mt-4 p-3 bg-blue-50/70 border border-blue-200/80 rounded-xl text-xs text-blue-900 space-y-1">
            <div class="font-semibold flex items-center gap-1.5 text-blue-900">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Panduan Manajemen Pelanggan untuk Admin Toko:</span>
            </div>
            <p class="text-slate-600 leading-relaxed text-[11px]">
                1. Klik tombol <strong>"Chat WA"</strong> untuk menghubungi pembeli secara langsung via WhatsApp resmi untuk konfirmasi pesanan atau konsultasi produk.<br>
                2. Klik tombol <strong>"Detail"</strong> untuk melihat profil lengkap, alamat pengiriman terdaftar, total transaksi belanja (LTV), dan 5 pesanan terakhir.<br>
                3. Gunakan tombol <strong>"Suspend"</strong> apabila pembeli terindikasi melakukan spam order fiktif demi menjaga keamanan transaksi toko.
            </p>
        </div>

        <!-- Search & Filter Toolbar (Persis Desain Katalog & Pesanan) -->
        <div class="p-4 sm:p-6 bg-slate-50/50 border-b border-slate-200 mt-4">
            <form action="{{ route('admin.users') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                @if($status !== 'semua')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 max-w-2xl">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Cari nama pelanggan, email, no. telepon/WA, atau kota..." 
                            class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-8 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        @if(request('search'))
                            <a href="{{ route('admin.users', ['status' => $status, 'sort' => $sort]) }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold" title="Reset pencarian">✕</a>
                        @endif
                    </div>

                    <!-- Sort Select (Clean & Professional Standard) -->
                    <div class="sm:w-52 shrink-0">
                        <select 
                            name="sort" 
                            onchange="this.form.submit()"
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors cursor-pointer"
                        >
                            <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Terbaru Terdaftar</option>
                            <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Nama (A - Z)</option>
                            <option value="most_orders" {{ $sort === 'most_orders' ? 'selected' : '' }}>Pesanan Terbanyak</option>
                            <option value="highest_spend" {{ $sort === 'highest_spend' ? 'selected' : '' }}>Total Belanja Tertinggi</option>
                            <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Terlama Terdaftar</option>
                        </select>
                    </div>

                    <!-- Submit & Reset -->
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg transition-colors cursor-pointer shadow-xs"
                    >
                        Cari
                    </button>

                    @if(request('search') || request('sort') !== 'latest' || request('status') !== 'semua')
                        <a 
                            href="{{ route('admin.users') }}" 
                            class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium text-xs rounded-lg transition-colors text-center"
                            title="Reset filter"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Customer Table (Persis Layout & Spacing Halaman Pesanan) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-3 py-3">No. Telepon & WhatsApp</th>
                        <th class="px-3 py-3">Kota / Alamat Pengiriman</th>
                        <th class="px-3 py-3 text-center">Total Belanja & Pesanan</th>
                        <th class="px-3 py-3">Tanggal Daftar</th>
                        <th class="px-3 py-3 text-center">Status Akun</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800 font-normal">
                    @forelse($users as $u)
                        @php
                            $isSuspended = ($u->status === 'suspended');
                            $userInitial = strtoupper(substr($u->name, 0, 1));
                            $totalSpentFormatted = 'Rp ' . number_format($u->total_spent ?? 0, 0, ',', '.');
                            $ordersCount = $u->orders_count ?? 0;
                            
                            // Safe phone format for WhatsApp
                            $cleanPhone = preg_replace('/[^0-9]/', '', $u->phone ?? '');
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Pelanggan Info -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white font-semibold flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                        {{ $userInitial }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="font-medium text-slate-900 truncate text-xs sm:text-sm">{{ $u->name }}</h4>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium {{ $ordersCount > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $ordersCount > 0 ? 'Buyer' : 'Member' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-400 font-normal truncate max-w-[200px]">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Telepon & WA (Single Line Clean Badge) -->
                            <td class="px-3 py-3 whitespace-nowrap">
                                @if($u->phone)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-slate-700 text-xs font-medium">{{ $u->phone }}</span>
                                        <a 
                                            href="https://wa.me/{{ $cleanPhone }}?text=Halo%20Kak%20{{ urlencode($u->name) }},%20kami%20dari%20Customer%20Service%20TokoOnline..." 
                                            target="_blank" 
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors whitespace-nowrap"
                                            title="Chat WhatsApp Pembeli"
                                        >
                                            <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                            <span>Chat WA</span>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">Belum ada nomor</span>
                                @endif
                            </td>

                            <!-- Kota / Alamat Pengiriman -->
                            <td class="px-3 py-3">
                                <div class="font-medium text-slate-800 text-xs">
                                    {{ $u->city ?: ($u->province ?: '-') }}
                                </div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[200px]" title="{{ $u->address ?: ($u->address_detail ?: 'Alamat belum diatur') }}">
                                    {{ $u->address ?: ($u->address_detail ?: 'Alamat belum diatur') }}
                                </div>
                            </td>

                            <!-- Total Belanja & Pesanan -->
                            <td class="px-3 py-3 whitespace-nowrap text-center">
                                <div class="font-semibold text-slate-900 text-xs sm:text-sm">
                                    {{ $totalSpentFormatted }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-sans">
                                        {{ $ordersCount }} Pesanan
                                    </span>
                                </div>
                            </td>

                            <!-- Tanggal Daftar -->
                            <td class="px-3 py-3 whitespace-nowrap text-xs">
                                <div class="font-medium text-slate-800">
                                    {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $u->created_at ? $u->created_at->diffForHumans() : '-' }}
                                </div>
                            </td>

                            <!-- Status Akun (Konsisten dengan Pesanan) -->
                            <td class="px-3 py-3 whitespace-nowrap text-center">
                                @if(!$isSuspended)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Suspended</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi (Persis Desain Tabel Pesanan) -->
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Tombol Detail -->
                                    <button 
                                        type="button" 
                                        @click="openDetailModal({{ json_encode([
                                            'id' => $u->id,
                                            'name' => $u->name,
                                            'email' => $u->email,
                                            'phone' => $u->phone,
                                            'clean_phone' => $cleanPhone,
                                            'city' => $u->city ?: ($u->province ?: '-'),
                                            'province' => $u->province ?: '-',
                                            'district' => $u->district ?: '-',
                                            'postal_code' => $u->postal_code ?: '-',
                                            'address' => $u->address ?: ($u->address_detail ?: 'Belum ada detail alamat pengiriman.'),
                                            'status' => $u->status ?: 'active',
                                            'is_suspended' => $isSuspended,
                                            'created_at' => $u->created_at ? $u->created_at->format('d F Y, H:i') : '-',
                                            'orders_count' => $ordersCount,
                                            'total_spent' => $totalSpentFormatted,
                                            'orders' => $u->orders ? $u->orders->map(function($o) {
                                                return [
                                                    'id' => $o->id,
                                                    'invoice' => $o->invoice_number,
                                                    'date' => $o->created_at ? $o->created_at->format('d M Y') : '-',
                                                    'total' => 'Rp ' . number_format($o->total ?? 0, 0, ',', '.'),
                                                    'status' => $o->status,
                                                ];
                                            }) : []
                                        ]) }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors border border-slate-200 cursor-pointer"
                                        title="Rincian Lengkap Pelanggan"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Detail</span>
                                    </button>

                                    <!-- Tombol Suspend / Aktifkan -->
                                    <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $isSuspended ? 'MENGAKTIFKAN KEMBALI' : 'MENANGGUHKAN (SUSPEND)' }} akun pelanggan {{ addslashes($u->name) }}?');">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg transition-colors border cursor-pointer {{ $isSuspended ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200' : 'text-rose-600 hover:text-rose-700 hover:bg-rose-50 border-rose-200' }}"
                                            title="{{ $isSuspended ? 'Aktifkan Akun' : 'Tangguhkan Akun' }}"
                                        >
                                            @if($isSuspended)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Aktifkan</span>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                <span>Suspend</span>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <h4 class="font-medium text-slate-800 text-sm">Tidak Ada Data Pelanggan</h4>
                                    <p class="text-xs text-slate-500">
                                        @if(request('search'))
                                            Tidak ditemukan akun yang cocok dengan kata kunci <strong>"{{ request('search') }}"</strong>.
                                        @else
                                            Belum ada pengguna terdaftar untuk kategori tab yang dipilih.
                                        @endif
                                    </p>
                                    @if(request('search') || request('status') !== 'semua')
                                        <div class="pt-2">
                                            <a href="{{ route('admin.users') }}" class="px-3.5 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors inline-block">
                                                Lihat Semua Pelanggan
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination (Konsisten dengan Pesanan) -->
        @if($users->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    Menampilkan <strong>{{ $users->firstItem() ?? 0 }}</strong> sampai <strong>{{ $users->lastItem() ?? 0 }}</strong> dari <strong>{{ $users->total() }}</strong> akun pelanggan.
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- DETAIL MODAL PELANGGAN (Shopee / Tokopedia Seller Centre Standard) -->
    <div 
        x-show="showDetailModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @keydown.escape.window="showDetailModal = false"
    >
        <div 
            class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150"
            @click.outside="showDetailModal = false"
        >
            <template x-if="activeUser">
                <div class="p-5 sm:p-6 space-y-5">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between border-b border-slate-200 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-blue-600 text-white font-semibold flex items-center justify-center text-base shadow-xs shrink-0">
                                <span x-text="activeUser.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base sm:text-lg font-semibold text-slate-900" x-text="activeUser.name"></h3>
                                    <span 
                                        class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider"
                                        :class="activeUser.is_suspended ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                                        x-text="activeUser.is_suspended ? 'Suspended' : 'Aktif'"
                                    ></span>
                                </div>
                                <p class="text-xs text-slate-400 font-mono mt-0.5" x-text="activeUser.email"></p>
                            </div>
                        </div>
                        <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 font-bold p-1 text-sm">✕</button>
                    </div>

                    <!-- 3 Quick Metrics Cards (RFM Pelanggan) -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center">
                            <span class="text-[10px] font-medium text-slate-500 uppercase block">Total Pesanan</span>
                            <span class="text-base sm:text-lg font-semibold text-slate-900 mt-0.5 block" x-text="activeUser.orders_count + ' Pesanan'"></span>
                        </div>
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center">
                            <span class="text-[10px] font-medium text-slate-500 uppercase block">Total Belanja (LTV)</span>
                            <span class="text-base sm:text-lg font-semibold text-emerald-600 mt-0.5 block" x-text="activeUser.total_spent"></span>
                        </div>
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center">
                            <span class="text-[10px] font-medium text-slate-500 uppercase block">Tanggal Bergabung</span>
                            <span class="text-xs sm:text-sm font-semibold text-slate-800 mt-1 block" x-text="activeUser.created_at"></span>
                        </div>
                    </div>

                    <!-- Detail Kontak & Alamat Pengiriman -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                        <!-- Kontak Info -->
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                            <span class="font-semibold text-slate-700 uppercase tracking-wider block text-[10px]">Informasi Kontak:</span>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">No. HP / WA:</span>
                                <span class="font-mono font-medium text-slate-900" x-text="activeUser.phone || '-'"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Email:</span>
                                <span class="font-mono text-slate-800 truncate max-w-[170px]" x-text="activeUser.email"></span>
                            </div>
                            <template x-if="activeUser.phone">
                                <div class="pt-2 border-t border-slate-200">
                                    <a 
                                        :href="'https://wa.me/' + activeUser.clean_phone + '?text=Halo%20Kak%20' + encodeURIComponent(activeUser.name) + ',%20kami%20dari%20tim%20Customer%20Service%20TokoOnline...'" 
                                        target="_blank"
                                        class="w-full py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg text-center flex items-center justify-center gap-1.5 transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        <span>Chat via WhatsApp</span>
                                    </a>
                                </div>
                            </template>
                        </div>

                        <!-- Alamat Pengiriman Utama -->
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                            <span class="font-semibold text-slate-700 uppercase tracking-wider block text-[10px]">Alamat Pengiriman Utama:</span>
                            <p class="font-medium text-slate-900" x-text="activeUser.city && activeUser.city !== '-' ? activeUser.city + (activeUser.province && activeUser.province !== '-' ? ', ' + activeUser.province : '') : 'Alamat belum diatur'"></p>
                            <p class="text-slate-600 leading-relaxed text-[11px]" x-text="activeUser.address"></p>
                            <template x-if="(activeUser.district && activeUser.district !== '-') || (activeUser.postal_code && activeUser.postal_code !== '-')">
                                <p class="text-[10px] text-slate-400" x-text="(activeUser.district && activeUser.district !== '-' ? 'Kecamatan: ' + activeUser.district : '') + (activeUser.postal_code && activeUser.postal_code !== '-' ? ' | Kode Pos: ' + activeUser.postal_code : '')"></p>
                            </template>
                        </div>
                    </div>

                    <!-- Riwayat 5 Pesanan Terakhir -->
                    <div class="space-y-2">
                        <span class="font-semibold text-slate-700 block text-xs uppercase tracking-wider">Riwayat 5 Pesanan Terakhir:</span>
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <template x-if="activeUser.orders && activeUser.orders.length > 0">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-medium text-[10px] uppercase">
                                        <tr>
                                            <th class="px-3 py-2">Invoice</th>
                                            <th class="px-3 py-2">Tanggal</th>
                                            <th class="px-3 py-2">Total</th>
                                            <th class="px-3 py-2 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                                        <template x-for="ord in activeUser.orders" :key="ord.id">
                                            <tr class="hover:bg-slate-50/70">
                                                <td class="px-3 py-2 font-mono font-medium text-blue-600" x-text="'#' + ord.invoice"></td>
                                                <td class="px-3 py-2 text-slate-400" x-text="ord.date"></td>
                                                <td class="px-3 py-2 font-semibold text-slate-900" x-text="ord.total"></td>
                                                <td class="px-3 py-2 text-center">
                                                    <span 
                                                        class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase"
                                                        :class="{
                                                            'bg-emerald-50 text-emerald-700 border border-emerald-200': ord.status === 'selesai',
                                                            'bg-purple-50 text-purple-700 border border-purple-200': ord.status === 'dikirim',
                                                            'bg-sky-50 text-sky-700 border border-sky-200': ord.status === 'dikemas' || ord.status === 'diproses',
                                                            'bg-amber-50 text-amber-700 border border-amber-200': ord.status === 'belum_dibayar',
                                                            'bg-rose-50 text-rose-700 border border-rose-200': ord.status === 'batal'
                                                        }"
                                                        x-text="ord.status.replace('_', ' ')"
                                                    ></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </template>
                            <template x-if="!activeUser.orders || activeUser.orders.length === 0">
                                <div class="p-4 text-center text-slate-400 text-xs italic bg-slate-50/50">
                                    Pelanggan belum memiliki riwayat transaksi pesanan.
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between gap-3">
                        <form :action="'/admin/users/' + activeUser.id + '/toggle'" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun pelanggan ini?');">
                            @csrf
                            <button 
                                type="submit" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 font-medium text-xs rounded-lg border transition-colors cursor-pointer"
                                :class="activeUser.is_suspended ? 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-300 hover:bg-rose-100'"
                            >
                                <span x-text="activeUser.is_suspended ? 'Aktifkan Akun' : 'Tangguhkan (Suspend) Akun'"></span>
                            </button>
                        </form>

                        <button 
                            type="button" 
                            @click="showDetailModal = false" 
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-lg transition-colors cursor-pointer"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
function customerManager() {
    return {
        showDetailModal: false,
        activeUser: null,

        openDetailModal(user) {
            this.activeUser = user;
            this.showDetailModal = true;
        }
    }
}
</script>
@endsection
