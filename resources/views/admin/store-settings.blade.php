@extends('admin.layout')

@section('title', 'Pengaturan Toko & Pengiriman')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'address' }">

    <!-- Header Section (Standar Seller Centre: Shopee / Tokopedia) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Pengaturan</span>
                <span>/</span>
                <span class="text-blue-600 font-medium">Pengiriman & Logistik</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Pengaturan Toko & Kurir</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola lokasi penjemputan paket (pick-up), aktivasi ekspedisi pembeli, dan integrasi otomatis Biteship.</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <button 
                type="submit" 
                form="storeSettingsForm"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-xs transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <!-- Alert Sukses & Error -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between gap-3 text-xs sm:text-sm animate-in fade-in duration-200">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs p-1 cursor-pointer">✕</button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs sm:text-sm space-y-1">
            <div class="font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Terdapat kesalahan pengisian form:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $activeCouriers = $settings->active_couriers ?? ['jnt', 'jne', 'sicepat'];
        $activeCouriersCount = count($activeCouriers);
        $currentApiKey = old('biteship_api_key', $settings->biteship_api_key ?? env('BITESHIP_API_KEY', ''));
        $isLiveKey = str_contains($currentApiKey, 'biteship_live') || (!str_contains($currentApiKey, 'biteship_test') && !empty($currentApiKey));
        $isTestKey = str_contains($currentApiKey, 'biteship_test');
        $hasOrigin = !empty($settings->biteship_area_id);
    @endphp

    <!-- TAB NAVIGATION BAR (Shopee / Tokopedia Seller Centre Pattern) -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="border-b border-slate-200 px-4 sm:px-6 bg-white overflow-x-auto no-scrollbar">
            <nav class="flex items-center gap-6 sm:gap-8 -mb-px text-xs sm:text-sm font-medium">
                <!-- Tab 1: Alamat Penjemputan -->
                <button 
                    type="button" 
                    @click="activeTab = 'address'"
                    class="py-3.5 border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'address' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Alamat Toko & Pick-Up</span>
                    <span class="w-2 h-2 rounded-full {{ $hasOrigin ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                </button>

                <!-- Tab 2: Jasa Kirim / Kurir -->
                <button 
                    type="button" 
                    @click="activeTab = 'couriers'"
                    class="py-3.5 border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'couriers' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    <span>Jasa Kirim Pembeli</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-blue-50 text-blue-700 font-semibold">
                        {{ $activeCouriersCount }} Aktif
                    </span>
                </button>

                <!-- Tab 3: Biaya & Subsidi -->
                <button 
                    type="button" 
                    @click="activeTab = 'pricing'"
                    class="py-3.5 border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'pricing' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>Biaya & Subsidi Ongkir</span>
                </button>

                <!-- Tab 4: Integrasi Biteship -->
                <button 
                    type="button" 
                    @click="activeTab = 'api'"
                    class="py-3.5 border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'api' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/>
                    </svg>
                    <span>Integrasi API Biteship</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium {{ $isLiveKey ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $isLiveKey ? 'Live' : 'Sandbox' }}
                    </span>
                </button>
            </nav>
        </div>

        <!-- FORM UTAMA PENGATURAN -->
        <form id="storeSettingsForm" action="{{ route('admin.storeSettings.update') }}" method="POST" class="p-4 sm:p-6 space-y-6">
            @csrf

            <!-- ========================================== -->
            <!-- TAB 1: ALAMAT TOKO & LOKASI PENJEMPUTAN   -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'address'" class="space-y-6">
                <!-- Preview Card Alamat Utama Saat Ini (Shopee Alamat Saya style) -->
                <div class="p-4 rounded-xl border {{ $hasOrigin ? 'bg-slate-50/70 border-slate-200' : 'bg-amber-50/60 border-amber-200' }} flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate-900">{{ $settings->store_name ?: 'Toko Online' }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-700">Alamat Penjemputan Utama</span>
                            @if($hasOrigin)
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-700">Terhubung Biteship</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">Area ID Belum Dipilih</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600">
                            <strong>{{ $settings->sender_name ?: 'Admin Toko' }}</strong> | {{ $settings->sender_phone ?: '08xxxxxxxxxx' }}
                        </p>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            {{ $settings->address_detail ?: 'Alamat detail belum diatur' }}, {{ $settings->village ? 'Desa ' . $settings->village . ', ' : '' }}{{ $settings->district ? 'Kec. ' . $settings->district . ', ' : '' }}{{ $settings->city ?: '' }}, {{ $settings->province ?: '' }} {{ $settings->postal_code ? '(' . $settings->postal_code . ')' : '' }}
                        </p>
                        @if($settings->biteship_area_id)
                            <p class="text-[11px] font-mono text-slate-400">
                                Biteship Area ID: <span class="text-slate-700 font-semibold">{{ $settings->biteship_area_id }}</span>
                            </p>
                        @endif
                    </div>
                    <div class="shrink-0">
                        <div class="text-xs text-slate-400 italic">
                            *Alamat ini digunakan kurir untuk pick-up paket dan perhitungan ongkir pembeli.
                        </div>
                    </div>
                </div>

                <!-- Form Input Alamat Penjemputan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 pt-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Nama Toko / Pengirim <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="store_name" 
                            value="{{ old('store_name', $settings->store_name) }}" 
                            required 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white text-slate-800 placeholder-slate-400"
                            placeholder="Contoh: Toko Online Store"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Nama Kontak Penanggung Jawab (PIC) <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="sender_name" 
                            value="{{ old('sender_name', $settings->sender_name) }}" 
                            required 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white text-slate-800 placeholder-slate-400"
                            placeholder="Contoh: Admin Toko"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">No. Telepon / WhatsApp Toko <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="sender_phone" 
                            value="{{ old('sender_phone', $settings->sender_phone) }}" 
                            required 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white text-slate-800 font-mono placeholder-slate-400"
                            placeholder="Contoh: 082220567326"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Nomor yang dapat dihubungi oleh kurir penjemput paket.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Kode Pos <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="postal_code" 
                            id="postal_code_input"
                            value="{{ old('postal_code', $settings->postal_code) }}" 
                            required 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white text-slate-800 font-mono placeholder-slate-400"
                            placeholder="Contoh: 52181"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Provinsi <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="province" 
                            id="province_input"
                            value="{{ old('province', $settings->province) }}" 
                            required 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white text-slate-800 placeholder-slate-400"
                            placeholder="Contoh: Jawa Tengah"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="city" 
                            id="city_input"
                            value="{{ old('city', $settings->city) }}" 
                            required 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white text-slate-800 placeholder-slate-400"
                            placeholder="Contoh: Tegal"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Kecamatan <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="district" 
                            id="district_input"
                            value="{{ old('district', $settings->district) }}" 
                            required 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white text-slate-800 placeholder-slate-400"
                            placeholder="Contoh: Kramat"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Desa / Kelurahan <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="village" 
                            id="village_input"
                            value="{{ old('village', $settings->village) }}" 
                            required 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white text-slate-800 placeholder-slate-400"
                            placeholder="Contoh: Tanjungharja"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Alamat Lengkap & Patokan Bangunan <span class="text-rose-500">*</span></label>
                        <textarea 
                            name="address_detail" 
                            rows="2" 
                            required 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white text-slate-800 placeholder-slate-400"
                            placeholder="Contoh: Jl. Tanjung RT 01 / RW 02 (Depan Masjid Al-Ikhlas), Kramat"
                        >{{ old('address_detail', $settings->address_detail) }}</textarea>
                    </div>

                    <!-- Area ID Biteship Pencarian Realtime -->
                    <div class="md:col-span-2 relative p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-900">
                                Verifikasi Wilayah Logistik (Biteship Area ID)
                            </label>
                            <span class="text-[11px] text-slate-500">Ketik kecamatan atau kode pos untuk mencari otomatis</span>
                        </div>
                        <div class="flex gap-2 relative">
                            <input 
                                type="text" 
                                name="biteship_area_id" 
                                id="biteship_area_id" 
                                value="{{ old('biteship_area_id', $settings->biteship_area_id) }}" 
                                class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white font-mono text-slate-800" 
                                placeholder="Ketik nama kecamatan atau kode pos..."
                                oninput="onAreaSearchInput(this.value)"
                            >
                            <button 
                                type="button" 
                                onclick="searchStoreArea()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-4 py-2.5 rounded-lg transition-colors shrink-0 flex items-center gap-1.5 cursor-pointer shadow-xs"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>Cari Wilayah</span>
                            </button>
                        </div>

                        <!-- Dropdown Hasil Pencarian AJAX -->
                        <div id="adminAreaDropdown" class="hidden absolute z-50 left-4 right-4 mt-1 bg-white border border-slate-200 rounded-lg shadow-xl max-h-56 overflow-y-auto divide-y divide-slate-100 text-xs"></div>

                        <div id="areaSearchResult" class="{{ $settings->biteship_area_id ? '' : 'hidden' }} text-xs bg-white border border-slate-200 text-slate-800 p-2.5 rounded-lg flex items-center justify-between gap-2">
                            <span><strong class="text-blue-700">✓ Wilayah Terpilih:</strong> <span id="areaSelectedLabel">{{ $settings->district ? $settings->district.', ' : '' }}{{ $settings->city }} {{ $settings->province }} ({{ $settings->postal_code }})</span></span>
                            <span class="text-[11px] font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200" id="areaSelectedId">ID: {{ $settings->biteship_area_id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: PILIHAN JASA KIRIM (SHOPEE STYLE)  -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'couriers'" class="space-y-6">
                <!-- Bar Pilihan Massal -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-900">Aktivasi Ekspedisi Pembeli</h3>
                        <p class="text-[11px] text-slate-500">Pilih kurir yang ingin Anda aktifkan saat checkout pembeli. Geser saklar toggle untuk mengaktifkan/menonaktifkan.</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button 
                            type="button" 
                            onclick="setAllCouriersToggle(true)" 
                            class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-medium transition-colors cursor-pointer"
                        >
                            Aktifkan Semua
                        </button>
                        <button 
                            type="button" 
                            onclick="setAllCouriersToggle(false)" 
                            class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-medium transition-colors cursor-pointer"
                        >
                            Nonaktifkan Semua
                        </button>
                    </div>
                </div>

                @php
                    $courierCategories = [
                        'Reguler & Standar' => [
                            'jnt'      => ['name' => 'J&T Express', 'desc' => 'Layanan reguler drop-point & pick-up terluas di Indonesia', 'tag' => 'Populer'],
                            'jne'      => ['name' => 'JNE Express', 'desc' => 'Layanan JNE Reguler & YES (Yakin Esok Sampai)', 'tag' => 'Populer'],
                            'sicepat'  => ['name' => 'SiCepat Express', 'desc' => 'Layanan SIUNTUNG jemput paket cepat', 'tag' => 'Populer'],
                            'spx'      => ['name' => 'Shopee Xpress (SPX)', 'desc' => 'Layanan pengiriman SPX Standard & Hemat', 'tag' => 'E-Commerce'],
                            'anteraja' => ['name' => 'Anteraja', 'desc' => 'Pengiriman reguler dengan armada Satria', 'tag' => 'Reguler'],
                            'pos'      => ['name' => 'POS Indonesia', 'desc' => 'Pos Kilat Khusus menjangkau seluruh kecamatan pelosok', 'tag' => 'Nasional'],
                            'tiki'     => ['name' => 'TIKI', 'desc' => 'Layanan TIKI REG & ONS (Over Night Services)', 'tag' => 'Reguler'],
                            'ninja'    => ['name' => 'Ninja Xpress', 'desc' => 'Layanan Ninja Standard pengantaran door-to-door', 'tag' => 'Reguler'],
                            'lion'     => ['name' => 'Lion Parcel', 'desc' => 'Layanan kirim paket cepat via armada udara Lion Group', 'tag' => 'Udara'],
                        ],
                        'Instant & Sameday' => [
                            'gosend'      => ['name' => 'GoSend', 'desc' => 'Pengiriman instan motor Gojek dalam hitungan jam', 'tag' => 'Instant'],
                            'grabexpress' => ['name' => 'GrabExpress', 'desc' => 'Pengantaran instan & sameday armada Grab', 'tag' => 'Instant'],
                            'paxel'       => ['name' => 'Paxel', 'desc' => 'Sameday delivery & fasilitas pendingin makanan (Cold Delivery)', 'tag' => 'Cold Chain'],
                            'lalamove'    => ['name' => 'Lalamove', 'desc' => 'Pengiriman on-demand motor, sedan, MPV, hingga van', 'tag' => 'On-Demand'],
                            'borzo'       => ['name' => 'Borzo', 'desc' => 'Kurir motor instan perkotaan bertarif hemat', 'tag' => 'Hemat'],
                        ],
                        'Kargo & Ekonomis' => [
                            'deliveree' => ['name' => 'Deliveree Kargo', 'desc' => 'Pengiriman barang besar, muatan berat, dan armada truk', 'tag' => 'Kargo'],
                            'wahana'    => ['name' => 'Wahana Express', 'desc' => 'Pengiriman paket ekonomis harga terjangkau', 'tag' => 'Hemat'],
                            'rpx'       => ['name' => 'RPX Logistics', 'desc' => 'Layanan ekspedisi logistik paket dan paket korporat', 'tag' => 'Logistik'],
                        ]
                    ];
                @endphp

                <div class="space-y-6">
                    @foreach($courierCategories as $catTitle => $couriers)
                        <div class="space-y-3">
                            <h4 class="text-xs font-semibold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                <span>{{ $catTitle }}</span>
                                <span class="text-[10px] text-slate-400 font-normal">({{ count($couriers) }} Opsi)</span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($couriers as $code => $c)
                                    @php
                                        $isOn = in_array($code, $activeCouriers);
                                    @endphp
                                    <div class="courier-card p-3.5 rounded-xl border transition-all flex items-start justify-between gap-3 {{ $isOn ? 'bg-white border-blue-200 shadow-2xs' : 'bg-slate-50/60 border-slate-200 opacity-75' }}">
                                        <div class="space-y-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-slate-900 text-xs sm:text-sm">{{ $c['name'] }}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                    {{ $c['tag'] }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 leading-snug line-clamp-2">{{ $c['desc'] }}</p>
                                        </div>

                                        <!-- Toggle Switch (Shopee Style) -->
                                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                            <input 
                                                type="checkbox" 
                                                name="active_couriers[]" 
                                                value="{{ $code }}" 
                                                {{ $isOn ? 'checked' : '' }} 
                                                class="sr-only peer courier-toggle"
                                                onchange="onCourierSwitchChange(this)"
                                            >
                                            <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: BIAYA PACKING & SUBSIDI ONGKIR     -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'pricing'" class="space-y-6">
                <div class="p-4 bg-indigo-50/60 border border-indigo-200 rounded-xl text-xs text-indigo-900 space-y-1">
                    <span class="font-semibold block">Strategi Penetapan Tarif & Promosi Ongkir:</span>
                    <p class="text-indigo-800 leading-relaxed text-[11px]">
                        Gunakan fitur ini jika Anda ingin menambahkan biaya kardus/bubble wrap otomatis, atau memberikan subsidi potongan ongkir dari toko untuk menarik lebih banyak pembeli.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Biaya Handling -->
                    <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-2">
                        <label class="block text-xs font-semibold text-slate-900">
                            Biaya Packing Tambahan / Handling Fee (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-xs font-semibold text-slate-400">Rp</span>
                            <input 
                                type="number" 
                                name="biteship_handling_fee" 
                                value="{{ old('biteship_handling_fee', (int) ($settings->biteship_handling_fee ?? 0)) }}" 
                                min="0" 
                                step="500" 
                                class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm pl-9 pr-3 py-2 border bg-white font-mono text-slate-800" 
                                placeholder="0"
                            >
                        </div>
                        <p class="text-[11px] text-slate-400">Nominal ini akan <strong>ditambahkan otomatis</strong> ke setiap tarif ongkir kurir saat pembeli checkout.</p>
                    </div>

                    <!-- Subsidi Diskon Ongkir -->
                    <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-2">
                        <label class="block text-xs font-semibold text-slate-900">
                            Subsidi Diskon Ongkir Toko (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-xs font-semibold text-slate-400">Rp</span>
                            <input 
                                type="number" 
                                name="biteship_shipping_discount" 
                                value="{{ old('biteship_shipping_discount', (int) ($settings->biteship_shipping_discount ?? 0)) }}" 
                                min="0" 
                                step="500" 
                                class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm pl-9 pr-3 py-2 border bg-white font-mono text-slate-800" 
                                placeholder="0"
                            >
                        </div>
                        <p class="text-[11px] text-slate-400">Potongan ongkir yang Anda tanggung untuk meringankan biaya belanja pelanggan.</p>
                    </div>

                    <!-- Min Belanja Subsidi -->
                    <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-2">
                        <label class="block text-xs font-semibold text-slate-900">
                            Syarat Minimum Belanja untuk Subsidi (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-xs font-semibold text-slate-400">Rp</span>
                            <input 
                                type="number" 
                                name="min_order_for_discount" 
                                value="{{ old('min_order_for_discount', (int) ($settings->min_order_for_discount ?? 0)) }}" 
                                min="0" 
                                step="5000" 
                                class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm pl-9 pr-3 py-2 border bg-white font-mono text-slate-800" 
                                placeholder="0"
                            >
                        </div>
                        <p class="text-[11px] text-slate-400">Syarat minimal subtotal belanja pembeli agar subsidi aktif. Isi <code>0</code> jika subsidi berlaku tanpa syarat belanja.</p>
                    </div>

                    <!-- Pembulatan Ongkir -->
                    <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-2">
                        <label class="block text-xs font-semibold text-slate-900">
                            Aturan Pembulatan Angka Ongkir
                        </label>
                        @php
                            $currentRound = old('biteship_round_shipping', $settings->biteship_round_shipping ?? 'none');
                        @endphp
                        <select 
                            name="biteship_round_shipping" 
                            class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2 border bg-white text-slate-800 cursor-pointer"
                        >
                            <option value="none" {{ $currentRound === 'none' ? 'selected' : '' }}>Tanpa Pembulatan (Harga Asli Apa Adanya)</option>
                            <option value="up_1000" {{ $currentRound === 'up_1000' ? 'selected' : '' }}>Bulatkan Ke Atas (Kelipatan Rp 1.000)</option>
                            <option value="nearest_1000" {{ $currentRound === 'nearest_1000' ? 'selected' : '' }}>Bulatkan Terdekat (Kelipatan Rp 1.000)</option>
                            <option value="down_1000" {{ $currentRound === 'down_1000' ? 'selected' : '' }}>Bulatkan Ke Bawah (Kelipatan Rp 1.000)</option>
                        </select>
                        <p class="text-[11px] text-slate-400">Membulatkan angka ganjil (misal: Rp 14.350 menjadi Rp 15.000) agar rapi saat transfer.</p>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: INTEGRASI API BITESHIP             -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'api'" class="space-y-6">
                <!-- Status Card -->
                <div class="p-4 rounded-xl border {{ $isLiveKey ? 'bg-emerald-50/60 border-emerald-200' : 'bg-amber-50/60 border-amber-200' }} flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg {{ $isLiveKey ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate-900">Status Integrasi Biteship:</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $isLiveKey ? 'bg-emerald-200 text-emerald-900' : 'bg-amber-200 text-amber-900' }}">
                                {{ $isLiveKey ? 'Production (Live)' : 'Sandbox (Uji Coba)' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            @if($isLiveKey)
                                Sistem saat ini menggunakan <strong>API Key Live</strong>. Pemotongan saldo dan penerbitan resi asli kurir berjalan secara resmi dan otomatis.
                            @else
                                Sistem saat ini menggunakan <strong>API Key Sandbox (Test)</strong>. Anda dapat menguji seluruh alur checkout, penentuan ongkir, dan cetak resi simulasi tanpa memotong saldo akun Biteship Anda.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Input API Key -->
                <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                    <label class="block text-xs font-semibold text-slate-900">
                        Biteship API Key
                    </label>
                    <input 
                        type="text" 
                        name="biteship_api_key" 
                        value="{{ old('biteship_api_key', $settings->biteship_api_key) }}" 
                        class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-xs sm:text-sm p-2.5 border bg-white font-mono text-slate-800" 
                        placeholder="Contoh: biteship_live.eyJhbGciOiJIUzI1Ni..."
                    >
                    <div class="text-[11px] text-slate-500 space-y-1 leading-relaxed">
                        <p>
                            • Kunci API dapat diambil dari menu <a href="https://biteship.com/dashboard/settings/api-keys" target="_blank" class="text-blue-600 underline font-semibold hover:text-blue-800">Dashboard Biteship &gt; Settings &gt; API Keys</a>.
                        </p>
                        <p>
                            • Saat siap melakukan publikasi resmi, salin API Key dengan awalan <code>biteship_live.xxx</code> ke kolom di atas lalu klik simpan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sticky Save Bar di Bawah -->
            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <span class="text-xs text-slate-500">
                    Pastikan informasi sudah benar sebelum menerapkan perubahan pada website toko.
                </span>
                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center gap-1.5 px-6 py-2.5 text-xs sm:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-xs transition-colors cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Pengaturan Toko & Kurir</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
let searchDebounce = null;

function onAreaSearchInput(val) {
    clearTimeout(searchDebounce);
    if (val.length < 3) {
        document.getElementById('adminAreaDropdown').classList.add('hidden');
        return;
    }
    searchDebounce = setTimeout(() => {
        fetchStoreAreas(val);
    }, 300);
}

function fetchStoreAreas(query) {
    const dropdown = document.getElementById('adminAreaDropdown');
    dropdown.innerHTML = '<div class="p-3 text-center text-slate-400 text-xs">Mencari wilayah di Biteship...</div>';
    dropdown.classList.remove('hidden');

    fetch('/shipping/biteship/areas?query=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data.length > 0) {
                let html = '';
                res.data.forEach(item => {
                    const postal = item.postal_code || '';
                    const district = item.administrative_division_level_3_name || '';
                    const city = item.administrative_division_level_2_name || '';
                    const province = item.administrative_division_level_1_name || '';
                    
                    html += `
                        <div onclick="selectStoreArea('${item.id}', '${item.name.replace(/'/g, "\\'")}', '${district.replace(/'/g, "\\'")}', '${city.replace(/'/g, "\\'")}', '${province.replace(/'/g, "\\'")}', '${postal}')" class="p-3 hover:bg-blue-50 cursor-pointer flex items-center justify-between transition-colors">
                            <div>
                                <div class="font-medium text-slate-900">${item.name}</div>
                                <div class="text-[11px] text-slate-500">${district ? district + ', ' : ''}${city}, ${province} (${postal})</div>
                            </div>
                            <span class="text-[10px] font-mono bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded shrink-0">ID: ${item.id}</span>
                        </div>
                    `;
                });
                dropdown.innerHTML = html;
                dropdown.classList.remove('hidden');
            } else {
                dropdown.innerHTML = '<div class="p-3 text-center text-slate-400 text-xs italic">Tidak ada wilayah ditemukan. Coba ketik nama kecamatan atau kode pos lain.</div>';
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 2500);
            }
        })
        .catch(() => {
            dropdown.classList.add('hidden');
        });
}

function selectStoreArea(id, name, district, city, province, postalCode) {
    document.getElementById('biteship_area_id').value = id;
    if (district) document.getElementById('district_input').value = district;
    if (city) document.getElementById('city_input').value = city;
    if (province) document.getElementById('province_input').value = province;
    if (postalCode) document.getElementById('postal_code_input').value = postalCode;
    
    document.getElementById('adminAreaDropdown').classList.add('hidden');

    const resultDiv = document.getElementById('areaSearchResult');
    const labelSpan = document.getElementById('areaSelectedLabel');
    const idSpan = document.getElementById('areaSelectedId');
    
    resultDiv.classList.remove('hidden');
    labelSpan.innerText = name;
    idSpan.innerText = 'ID: ' + id;
}

function searchStoreArea() {
    const district = document.getElementById('district_input').value.trim();
    const city = document.getElementById('city_input').value.trim();
    const postal = document.getElementById('postal_code_input').value.trim();
    const inputVal = document.getElementById('biteship_area_id').value.trim();
    
    const q = postal || (district ? (district + ' ' + city) : inputVal);
    
    if (!q) {
        alert('Silakan ketik nama kecamatan, kota, atau kode pos terlebih dahulu.');
        return;
    }
    
    fetchStoreAreas(q);
}

function setAllCouriersToggle(enable) {
    document.querySelectorAll('.courier-toggle').forEach(toggle => {
        toggle.checked = enable;
        onCourierSwitchChange(toggle);
    });
}

function onCourierSwitchChange(toggle) {
    const card = toggle.closest('.courier-card');
    if (toggle.checked) {
        card.classList.add('bg-white', 'border-blue-200', 'shadow-2xs');
        card.classList.remove('bg-slate-50/60', 'opacity-75');
    } else {
        card.classList.remove('bg-white', 'border-blue-200', 'shadow-2xs');
        card.classList.add('bg-slate-50/60', 'opacity-75');
    }
}
</script>
@endsection
