@extends('admin.layout')
@section('title', 'Pengaturan Toko & Kurir Pengiriman')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Lokasi Toko & Kurir Aktif</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola titik lokasi asal toko (Origin Pengiriman) & filter kurir yang tampil di checkout pembeli.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3">
    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span class="text-sm font-semibold">{{ session('success') }}</span>
</div>
@endif

<form action="{{ route('admin.storeSettings.update') }}" method="POST" class="space-y-6">
    @csrf

    <!-- SECTION 1: LOKASI ASAL TOKO (ORIGIN PENGIRIMAN) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Detail Alamat Asal Toko (Origin)</h3>
                <p class="text-xs text-slate-500">Lokasi penjemputan barang (Pick Up) & patokan hitung ongkir ke pembeli.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Nama Toko / Pengirim <span class="text-red-500">*</span></label>
                <input type="text" name="store_name" value="{{ old('store_name', $settings->store_name) }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: Toko Online Official">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Nama Kontak Pengirim <span class="text-red-500">*</span></label>
                <input type="text" name="sender_name" value="{{ old('sender_name', $settings->sender_name) }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: Admin Gudang">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">No. HP / Telepon Toko <span class="text-red-500">*</span></label>
                <input type="text" name="sender_phone" value="{{ old('sender_phone', $settings->sender_phone) }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: 081234567890">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Kode Pos <span class="text-red-500">*</span></label>
                <input type="text" name="postal_code" value="{{ old('postal_code', $settings->postal_code) }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: 10110">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Provinsi <span class="text-red-500">*</span></label>
                <input type="text" name="province" value="{{ old('province', $settings->province) }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: DKI Jakarta">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Kota / Kabupaten <span class="text-red-500">*</span></label>
                <input type="text" name="city" value="{{ old('city', $settings->city) }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: Jakarta Pusat">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Kecamatan <span class="text-red-500">*</span></label>
                <input type="text" name="district" value="{{ old('district', $settings->district) }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: Gambir">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Desa / Kelurahan <span class="text-red-500">*</span></label>
                <input type="text" name="village" value="{{ old('village', $settings->village) }}" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: Gambir">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Alamat Detail (Jalan / RT / RW / No. Bangunan) <span class="text-red-500">*</span></label>
                <textarea name="address_detail" rows="3" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: Jl. Merdeka No. 123, RT 01 / RW 02 (Pagar Hitam Depan Masjid)">{{ old('address_detail', $settings->address_detail) }}</textarea>
            </div>

            <div class="md:col-span-2 relative">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Biteship Area ID Toko (Auto Realtime Search) <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2 relative">
                    <input 
                        type="text" 
                        name="biteship_area_id" 
                        id="biteship_area_id" 
                        value="{{ old('biteship_area_id', $settings->biteship_area_id) }}" 
                        class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-slate-50 font-mono" 
                        placeholder="Ketik Kecamatan / Kota toko Anda..."
                        oninput="onAreaSearchInput(this.value)"
                    >
                    <button type="button" onclick="searchStoreArea()" class="bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold px-4 py-3 rounded-xl transition-all shrink-0 flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Cek Real-Time Area ID
                    </button>
                </div>

                <!-- Realtime Suggestions Dropdown for Admin -->
                <div id="adminAreaDropdown" class="hidden absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-slate-100 text-xs"></div>

                <p class="text-xs text-slate-500 mt-1">💡 <strong>Status:</strong> Terhubung 100% Real-Time ke Biteship API (`/v1/maps/areas`). Mengunci titik penjemputan barang (Origin) presisi.</p>
                <div id="areaSearchResult" class="mt-2 text-xs hidden bg-blue-50 border border-blue-200 text-blue-800 p-3 rounded-xl"></div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: PENGATURAN KURIR EKSPEDISI AKTIF -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center font-bold shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Kurir Ekspedisi Aktif (Filter Tampilan Website)</h3>
                    <p class="text-xs text-slate-500">Centang ekspedisi Biteship yang ingin ditampilkan ke pembeli saat checkout. Kosongkan jika ingin menampilkan semua kurir.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="selectAllCouriers(true)" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors">
                    ✓ Pilih Semua
                </button>
                <button type="button" onclick="selectAllCouriers(false)" class="px-3 py-1.5 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg text-xs font-bold transition-colors">
                    ✕ Hapus Semua
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5">
            @php
                $allCouriers = [
                    'jnt'         => 'J&T Express',
                    'jne'         => 'JNE Express',
                    'sicepat'     => 'SiCepat Express',
                    'pos'         => 'POS Indonesia',
                    'tiki'        => 'TIKI',
                    'anteraja'    => 'Anteraja',
                    'wahana'      => 'Wahana Express',
                    'ninja'       => 'Ninja Xpress',
                    'lion'        => 'Lion Parcel',
                    'spx'         => 'Shopee Xpress (SPX)',
                    'rpx'         => 'RPX Logistics',
                    'paxel'       => 'Paxel (Sameday/Cold)',
                    'lalamove'    => 'Lalamove',
                    'borzo'       => 'Borzo (MrSpeedy)',
                    'deliveree'   => 'Deliveree',
                    'gosend'      => 'GoSend (Gojek Instant)',
                    'grabexpress' => 'GrabExpress (Instant)',
                ];
                $activeCouriers = $settings->active_couriers ?? array_keys($allCouriers);
            @endphp

            @foreach($allCouriers as $code => $label)
                <label class="courier-card border rounded-xl p-3 flex items-center gap-3 cursor-pointer hover:border-blue-600 transition-colors {{ in_array($code, $activeCouriers) ? 'bg-blue-50/50 border-blue-600' : 'bg-slate-50 border-slate-200' }}">
                    <input type="checkbox" name="active_couriers[]" value="{{ $code }}" {{ in_array($code, $activeCouriers) ? 'checked' : '' }} class="courier-checkbox w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <span class="text-xs font-bold text-slate-800 leading-tight">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <script>
        function selectAllCouriers(select) {
            document.querySelectorAll('.courier-checkbox').forEach(cb => {
                cb.checked = select;
                const card = cb.closest('.courier-card');
                if (select) {
                    card.classList.add('bg-blue-50/50', 'border-blue-600');
                    card.classList.remove('bg-slate-50', 'border-slate-200');
                } else {
                    card.classList.remove('bg-blue-50/50', 'border-blue-600');
                    card.classList.add('bg-slate-50', 'border-slate-200');
                }
            });
        }
    </script>

    <!-- SECTION 3: CUSTOM RATES & STRATEGI TARIF ONGKIR (BIAYA PACKING & SUBSIDI) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Custom Rates & Strategi Penetapan Ongkir</h3>
                <p class="text-xs text-slate-500">Atur biaya packing tambahan, berikan subsidi ongkir toko, atau aktifkan pembulatan nominal harga.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Biaya Packing Tambahan / Handling Fee (Rp)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-xs font-bold text-slate-400">Rp</span>
                    <input 
                        type="number" 
                        name="biteship_handling_fee" 
                        value="{{ old('biteship_handling_fee', (int) ($settings->biteship_handling_fee ?? 0)) }}" 
                        min="0" 
                        step="500" 
                        class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-sm pl-9 pr-3 py-3 border bg-white" 
                        placeholder="0"
                    >
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Biaya ekstra (seperti kardus/bubble wrap) yang <strong>ditambahkan otomatis</strong> ke setiap tarif ongkir.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Subsidi / Diskon Ongkir Toko (Rp)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-xs font-bold text-slate-400">Rp</span>
                    <input 
                        type="number" 
                        name="biteship_shipping_discount" 
                        value="{{ old('biteship_shipping_discount', (int) ($settings->biteship_shipping_discount ?? 0)) }}" 
                        min="0" 
                        step="500" 
                        class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-sm pl-9 pr-3 py-3 border bg-white" 
                        placeholder="0"
                    >
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Potongan diskon ongkir yang ditanggung toko untuk meringankan biaya pembeli.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Minimal Belanja untuk Subsidi Ongkir (Rp)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-xs font-bold text-slate-400">Rp</span>
                    <input 
                        type="number" 
                        name="min_order_for_discount" 
                        value="{{ old('min_order_for_discount', (int) ($settings->min_order_for_discount ?? 0)) }}" 
                        min="0" 
                        step="5000" 
                        class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-sm pl-9 pr-3 py-3 border bg-white" 
                        placeholder="0"
                    >
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Syarat minimal subtotal belanja pembeli agar subsidi ongkir aktif. Isi <code>0</code> jika gratis tanpa syarat.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Metode Pembulatan Nominal Ongkir
                </label>
                @php
                    $currentRound = old('biteship_round_shipping', $settings->biteship_round_shipping ?? 'none');
                @endphp
                <select name="biteship_round_shipping" class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500 text-sm p-3 border bg-white">
                    <option value="none" {{ $currentRound === 'none' ? 'selected' : '' }}>Tanpa Pembulatan (Harga Asli Apa Adanya)</option>
                    <option value="up_1000" {{ $currentRound === 'up_1000' ? 'selected' : '' }}>Pembulatan Ke Atas (Kelipatan Rp 1.000)</option>
                    <option value="nearest_1000" {{ $currentRound === 'nearest_1000' ? 'selected' : '' }}>Pembulatan Terdekat (Kelipatan Rp 1.000)</option>
                    <option value="down_1000" {{ $currentRound === 'down_1000' ? 'selected' : '' }}>Pembulatan Ke Bawah (Kelipatan Rp 1.000)</option>
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Membulatkan angka ganjil ongkir (misal: Rp 14.350) agar nominal akhir rapi saat checkout.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 4: INTEGRASI API BITESHIP (LIVE API KEY) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Konfigurasi API Key Biteship</h3>
                <p class="text-xs text-slate-500">Gunakan API Key Live dari Dashboard Biteship agar pemotongan saldo top-up & pencetakan resi asli berjalan otomatis.</p>
            </div>
        </div>

        @php
            $currentApiKey = old('biteship_api_key', $settings->biteship_api_key ?? env('BITESHIP_API_KEY', ''));
            $isLiveKey = str_contains($currentApiKey, 'biteship_live') || (!str_contains($currentApiKey, 'biteship_test') && !empty($currentApiKey));
            $isTestKey = str_contains($currentApiKey, 'biteship_test');
        @endphp

        <div class="space-y-4">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Biteship API Key (Production / Live)
                    </label>
                    @if($isTestKey)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                            ⚠️ Mode Sandbox (Test Key Active)
                        </span>
                    @elseif($isLiveKey)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            ✅ Mode Production (Live Key Active)
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                            Belum Dikonfigurasi
                        </span>
                    @endif
                </div>
                
                <input 
                    type="text" 
                    name="biteship_api_key" 
                    value="{{ old('biteship_api_key', $settings->biteship_api_key) }}" 
                    class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3 border bg-white font-mono" 
                    placeholder="Contoh: biteship_live.eyJhbGciOiJIUzI1Ni..."
                >
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    💡 <strong>Tips:</strong> Ambil API Key Anda di <a href="https://biteship.com/dashboard/settings/api-keys" target="_blank" class="text-indigo-600 underline font-semibold hover:text-indigo-800">Dashboard Biteship > Settings > API Keys</a> (Pilih mode <strong>Live</strong>).
                    Jika kolom ini diisi, sistem akan otomatis menggunakannya daripada setting di file `.env`.
                </p>
            </div>

            @if($isTestKey)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-xl text-xs space-y-1">
                    <p class="font-bold flex items-center gap-1.5 text-amber-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Perhatian: Mode Test (Sandbox) Aktif
                    </p>
                    <p>
                        Dengan API Key Test (`biteship_test.xxx`), resi yang terbuat adalah resi simulasi internal (seperti `JT20260917...`). Saldo top-up Anda di Biteship <strong>tidak akan terpotong</strong>. Untuk memotong saldo & menerbitkan resi asli J&T/JNE, masukkan API Key Live (`biteship_live.xxx`) Anda pada kolom di atas lalu simpan.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white font-bold text-sm px-6 py-3.5 rounded-xl transition-all shadow-md cursor-pointer">
            Simpan Pengaturan Toko & Kurir
        </button>
    </div>
</form>

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
        fetch('/shipping/biteship/areas?query=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(item => {
                        html += `
                            <div onclick="selectStoreArea('${item.id}', '${item.name.replace(/'/g, "\\'")}', '${(item.administrative_division_level_3_name||'').replace(/'/g, "\\'")}', '${(item.administrative_division_level_2_name||'').replace(/'/g, "\\'")}', '${(item.administrative_division_level_1_name||'').replace(/'/g, "\\'")}', '${item.postal_code||''}')" class="p-3 hover:bg-blue-50 cursor-pointer flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-slate-900">${item.name}</div>
                                    <div class="text-[11px] text-slate-500">${item.administrative_division_level_3_name||''}, ${item.administrative_division_level_2_name||''}, ${item.administrative_division_level_1_name||''} (${item.postal_code||''})</div>
                                </div>
                                <span class="text-[10px] font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full shrink-0">ID: ${item.id}</span>
                            </div>
                        `;
                    });
                    dropdown.innerHTML = html;
                    dropdown.classList.remove('hidden');
                } else {
                    dropdown.classList.add('hidden');
                }
            });
    }

    function selectStoreArea(id, name, district, city, province, postalCode) {
        document.getElementById('biteship_area_id').value = id;
        if(district) document.querySelector('input[name="district"]').value = district;
        if(city) document.querySelector('input[name="city"]').value = city;
        if(province) document.querySelector('input[name="province"]').value = province;
        if(postalCode) document.querySelector('input[name="postal_code"]').value = postalCode;
        
        document.getElementById('adminAreaDropdown').classList.add('hidden');

        const resultDiv = document.getElementById('areaSearchResult');
        resultDiv.classList.remove('hidden');
        resultDiv.innerHTML = '🟢 <strong>100% Realtime Area Biteship Dikunci:</strong> ' + name + ' (ID: <code>' + id + '</code>)';
    }

    function searchStoreArea() {
        const district = document.querySelector('input[name="district"]').value;
        const city = document.querySelector('input[name="city"]').value;
        const inputVal = document.getElementById('biteship_area_id').value;
        const q = district ? (district + ' ' + city) : (inputVal || 'Jakarta');
        
        fetchStoreAreas(q);
    }
</script>

@endsection
