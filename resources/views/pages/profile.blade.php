@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'biodata' }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

    <!-- Page Header & Breadcrumb -->
    <div class="mb-6 space-y-1">
        <nav class="flex text-xs text-slate-500 gap-2 items-center">
            <a href="{{ route('home') }}" class="hover:text-blue-700">Beranda</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Profil Saya</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
            Pengaturan Akun &amp; Biodata
        </h1>
    </div>

    <!-- Flash Alert Messages -->
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-xs sm:text-sm text-emerald-800 font-medium flex items-center gap-2.5">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 space-y-1">
            @foreach($errors->all() as $error)
                <p class="text-xs sm:text-sm text-red-700 font-medium flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $error }}
                </p>
            @endforeach
        </div>
    @endif

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- LEFT SIDEBAR (~30% / 4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- User Avatar & Card Header -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs text-center space-y-3">
                <div class="relative w-20 h-20 rounded-full bg-blue-700 text-white font-extrabold text-3xl flex items-center justify-center mx-auto shadow-md border-2 border-white">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    <div class="absolute bottom-0 right-0 w-6 h-6 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center text-white" title="Email Terverifikasi">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <h2 class="font-extrabold text-slate-900 text-base sm:text-lg">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    <span class="inline-block bg-blue-50 text-blue-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full mt-1 border border-blue-200">
                        Member Toko Online
                    </span>
                </div>
            </div>

            <!-- Navigation Tabs Sidebar -->
            <div class="bg-white border border-slate-200 rounded-2xl p-2 shadow-xs space-y-1">
                <button 
                    type="button" 
                    @click="activeTab = 'biodata'"
                    :class="activeTab === 'biodata' ? 'bg-blue-50 text-blue-700 font-bold border-l-4 border-blue-700' : 'text-slate-700 hover:bg-slate-50 font-medium'"
                    class="w-full text-left px-4 py-3 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-3 cursor-pointer"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Biodata Diri</span>
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'alamat'"
                    :class="activeTab === 'alamat' ? 'bg-blue-50 text-blue-700 font-bold border-l-4 border-blue-700' : 'text-slate-700 hover:bg-slate-50 font-medium'"
                    class="w-full text-left px-4 py-3 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-3 cursor-pointer"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Alamat Pengiriman Utama</span>
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'keamanan'"
                    :class="activeTab === 'keamanan' ? 'bg-blue-50 text-blue-700 font-bold border-l-4 border-blue-700' : 'text-slate-700 hover:bg-slate-50 font-medium'"
                    class="w-full text-left px-4 py-3 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-3 cursor-pointer"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Keamanan &amp; Kata Sandi</span>
                </button>
            </div>

        </div>

        <!-- RIGHT CONTENT AREA (~70% / 8 cols) -->
        <div class="lg:col-span-8">

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- TAB 1: BIODATA DIRI -->
                <div x-show="activeTab === 'biodata'" class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">Ubah Biodata Diri</h2>
                        <p class="text-xs text-slate-500">Kelola informasi profil akun Toko Online Anda</p>
                    </div>

                    <div class="space-y-4">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            value="{{ old('name', $user->name) }}" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Alamat Email (Terverifikasi)
                            </label>
                            <input 
                                type="email" 
                                value="{{ $user->email }}" 
                                disabled 
                                class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-500 cursor-not-allowed"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Nomor WhatsApp / HP <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="phone" 
                                value="{{ old('phone', $user->phone) }}" 
                                required 
                                placeholder="081234567890"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                            <select 
                                name="gender" 
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none cursor-pointer"
                            >
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('gender', $user->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('gender', $user->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                            <input 
                                type="date" 
                                name="birth_date" 
                                value="{{ old('birth_date', $user->birth_date) }}" 
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                            >
                        </div>
                    </div>

                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-colors shadow-2xs cursor-pointer"
                        >
                            Simpan Perubahan Biodata
                        </button>
                    </div>
                </div>

                <!-- TAB 2: ALAMAT TUJUAN PENGIRIMAN (RUMAH ANDA) -->
                <div x-show="activeTab === 'alamat'" class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">Alamat Tujuan Pengiriman (Rumah Anda)</h2>
                        <p class="text-xs text-slate-500">Alamat ini akan otomatis terpasang saat Anda melakukan pembelian / checkout</p>
                    </div>

                    <div class="space-y-4">

                    <!-- Address Type / Label Preset Badges -->
                    <div class="bg-blue-50/70 border border-blue-200/70 p-3 rounded-xl flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-blue-900 uppercase tracking-wider">Tipe / Label Alamat:</span>
                            <span class="text-[11px] text-slate-500 hidden sm:inline">(Pilih kategori lokasi tujuan)</span>
                        </div>
                        <div class="flex items-center gap-1.5" x-data="{ activeLabel: 'Rumah' }">
                            <button 
                                type="button" 
                                @click="activeLabel = 'Rumah'; const textarea = document.getElementById('user_address_field'); if (textarea && !textarea.value.includes('[Rumah]')) { textarea.value = '[Rumah] ' + textarea.value.replace(/^\[(Rumah|Kantor|Lainnya)\]\s*/, ''); }"
                                class="px-2.5 py-1 text-xs font-bold rounded-lg transition-colors cursor-pointer flex items-center gap-1 bg-blue-700 text-white shadow-2xs"
                            >
                                🏠 <span>Rumah</span>
                            </button>
                            <button 
                                type="button" 
                                @click="activeLabel = 'Kantor'; const textarea = document.getElementById('user_address_field'); if (textarea) { textarea.value = '[Kantor] ' + textarea.value.replace(/^\[(Rumah|Kantor|Lainnya)\]\s*/, ''); }"
                                class="px-2.5 py-1 text-xs font-bold rounded-lg transition-colors cursor-pointer flex items-center gap-1 bg-white border border-slate-300 text-slate-700 hover:bg-slate-100"
                            >
                                🏢 <span>Kantor</span>
                            </button>
                            <button 
                                type="button" 
                                @click="activeLabel = 'Lainnya'; const textarea = document.getElementById('user_address_field'); if (textarea) { textarea.value = '[Lainnya] ' + textarea.value.replace(/^\[(Rumah|Kantor|Lainnya)\]\s*/, ''); }"
                                class="px-2.5 py-1 text-xs font-bold rounded-lg transition-colors cursor-pointer flex items-center gap-1 bg-white border border-slate-300 text-slate-700 hover:bg-slate-100"
                            >
                                🏡 <span>Lainnya</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Detail Alamat Pengiriman (Nama Jalan, No. Rumah, RT/RW, Patokan / PT) <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="user_address_field"
                            name="address" 
                            rows="2" 
                            required
                            placeholder="Contoh: [Rumah] Jl. Ahmad Yani No. 12, RT 03/RW 05, PT Bumitekno Indonesia"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >{{ old('address', $user->address && $user->address !== 'denpasar' ? $user->address : '') }}</textarea>

                        <p class="text-[11px] text-slate-500 mt-1">
                            Tuliskan detail <strong>Nomor Rumah, RT/RW, Nama Jalan, atau Nama PT/Gedung</strong> Anda secara spesifik di sini.
                        </p>
                    </div>

                    <!-- BITESHIP LIVE GPS & AREA AUTOCOMPLETE SEARCH -->
                    <div 
                        x-data="{
                            searchQuery: '{{ old('city', $user->city) ? (($user->district ? $user->district . ', ' : '') . $user->city . ', ' . ($user->province ?? '') . ($user->postal_code ? '. ' . $user->postal_code : '')) : '' }}',
                            selectedArea: {
                                id: '{{ old('biteship_area_id', $user->biteship_area_id ?: $user->city_id) }}',
                                district: '{{ old('district', $user->district) }}',
                                village: '{{ old('village', $user->village) }}',
                                city: '{{ old('city', $user->city) }}',
                                province: '{{ old('province', $user->province) }}',
                                postal_code: '{{ old('postal_code', $user->postal_code) }}'
                            },
                            suggestions: [],
                            showDropdown: false,
                            loading: false,
                            showMapModal: false,
                            mapInstance: null,
                            markerInstance: null,

                            async searchArea() {
                                if (this.searchQuery.length < 3) {
                                    this.suggestions = [];
                                    return;
                                }
                                this.loading = true;
                                try {
                                    const [biteshipRes, osmRes] = await Promise.all([
                                        fetch('/shipping/biteship/areas?query=' + encodeURIComponent(this.searchQuery)).then(r => r.json()).catch(() => ({ status: 'error' })),
                                        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(this.searchQuery) + '&countrycodes=id&limit=5').then(r => r.json()).catch(() => ([]))
                                    ]);

                                    let list = [];
                                    if (biteshipRes.status === 'success' && biteshipRes.data) {
                                        biteshipRes.data.forEach(item => {
                                            list.push({
                                                type: 'biteship',
                                                id: item.id,
                                                name: item.name,
                                                subtext: (item.administrative_division_level_3_name || '') + ', ' + (item.administrative_division_level_2_name || '') + ', ' + (item.administrative_division_level_1_name || '') + ' (' + (item.postal_code || '') + ')',
                                                district: item.administrative_division_level_3_name || '',
                                                village: item.name.split(',')[0] || '',
                                                city: item.administrative_division_level_2_name || '',
                                                province: item.administrative_division_level_1_name || '',
                                                postal_code: item.postal_code || '',
                                                badge: 'Biteship Area',
                                                badgeClass: 'bg-blue-100 text-blue-800'
                                            });
                                        });
                                    }

                                    if (Array.isArray(osmRes)) {
                                        osmRes.forEach(item => {
                                            list.push({
                                                type: 'osm',
                                                id: 'osm_' + item.place_id,
                                                name: item.display_name,
                                                subtext: '📍 Hasil Peta (Kecamatan/Jalan/PT/Gedung)',
                                                lat: item.lat,
                                                lon: item.lon,
                                                badge: '🗺️ Peta Map GPS',
                                                badgeClass: 'bg-emerald-100 text-emerald-800'
                                            });
                                        });
                                    }

                                    this.suggestions = list;
                                    this.showDropdown = list.length > 0;
                                } catch (e) {
                                    console.error('Error searching area:', e);
                                } finally {
                                    this.loading = false;
                                }
                            },

                            selectArea(item) {
                                if (item.type === 'osm') {
                                    const fullAddressTextarea = document.querySelector('textarea[name=address]');
                                    if (fullAddressTextarea) {
                                        fullAddressTextarea.value = item.name;
                                    }
                                    this.reverseGeocode(item.lat, item.lon);
                                    this.searchQuery = item.name;
                                    this.showDropdown = false;
                                    return;
                                }

                                this.selectedArea.id = item.id;
                                this.selectedArea.district = item.district || '';
                                this.selectedArea.village = item.village || '';
                                this.selectedArea.city = item.city || '';
                                this.selectedArea.province = item.province || '';
                                this.selectedArea.postal_code = item.postal_code || '';
                                
                                this.searchQuery = item.name;
                                this.showDropdown = false;
                                
                                const postalInput = document.querySelector('input[name=postal_code]');
                                if (postalInput && item.postal_code) {
                                    postalInput.value = item.postal_code;
                                }
                            },

                            openMapPicker() {
                                this.showMapModal = true;
                                this.$nextTick(() => {
                                    if (!this.mapInstance) {
                                        this.initMap();
                                    } else {
                                        setTimeout(() => { this.mapInstance.invalidateSize(); }, 300);
                                    }
                                });
                            },

                            initMap() {
                                const defaultLat = -6.2088;
                                const defaultLng = 106.8456;
                                this.mapInstance = L.map('leafletMapContainer').setView([defaultLat, defaultLng], 13);

                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '© OpenStreetMap contributors'
                                }).addTo(this.mapInstance);

                                this.markerInstance = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(this.mapInstance);

                                this.markerInstance.on('dragend', (event) => {
                                    const position = event.target.getLatLng();
                                    this.reverseGeocode(position.lat, position.lng);
                                });

                                this.mapInstance.on('click', (e) => {
                                    this.markerInstance.setLatLng(e.latlng);
                                    this.reverseGeocode(e.latlng.lat, e.latlng.lng);
                                });

                                this.detectGPSForMap();
                            },

                            detectGPSForMap() {
                                if (navigator.geolocation) {
                                    navigator.geolocation.getCurrentPosition((pos) => {
                                        const lat = pos.coords.latitude;
                                        const lng = pos.coords.longitude;
                                        if (this.mapInstance && this.markerInstance) {
                                            this.mapInstance.setView([lat, lng], 15);
                                            this.markerInstance.setLatLng([lat, lng]);
                                            this.reverseGeocode(lat, lng);
                                        }
                                    });
                                }
                            },

                            async reverseGeocode(lat, lng) {
                                try {
                                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                                    const data = await res.json();
                                    if (data && data.address) {
                                        const addr = data.address;
                                        const street = addr.road || addr.building || addr.amenity || '';
                                        const suburb = addr.suburb || addr.village || addr.neighbourhood || '';
                                        const city = addr.city || addr.town || addr.city_district || addr.county || '';
                                        const postcode = addr.postcode || '';

                                        if (city || suburb) {
                                            this.searchQuery = suburb ? (suburb + ', ' + city) : city;
                                            this.searchArea();
                                        }

                                        const fullAddressTextarea = document.querySelector('textarea[name=address]');
                                        if (fullAddressTextarea && data.display_name) {
                                            fullAddressTextarea.value = data.display_name;
                                        }

                                        if (postcode) {
                                            this.selectedArea.postal_code = postcode;
                                            const postalInput = document.querySelector('input[name=postal_code]');
                                            if (postalInput) postalInput.value = postcode;
                                        }
                                    }
                                } catch (e) {
                                    console.error('Reverse geocode error:', e);
                                }
                            }
                        }"
                        class="space-y-4"
                    >
                        <!-- Leaflet CSS and Script Include -->
                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                        <!-- Search Box for Biteship Area -->
                        <div class="relative">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">
                                    Cari Kecamatan, Kelurahan, Desa & Kode Pos (Biteship GPS Live Search) <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <button 
                                        type="button" 
                                        @click="openMapPicker()" 
                                        class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 cursor-pointer bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200"
                                    >
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        <span>Pilih Pinpoint Peta Interaktif</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="relative">
                                <input 
                                    type="text" 
                                    x-model="searchQuery" 
                                    @input.debounce.300ms="searchArea()" 
                                    @focus="if(suggestions.length > 0) showDropdown = true" 
                                    placeholder="Ketik nama Kecamatan, Kelurahan, Kota, atau Kode Pos (misal: Slawi, Tegal, Denpasar, 56211)..." 
                                    class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-9 pr-8 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                                >
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <div x-show="loading" class="absolute right-3 top-3">
                                    <div class="w-4 h-4 border-2 border-blue-700 border-t-transparent rounded-full animate-spin"></div>
                                </div>
                            </div>

                            <!-- Helpful Note -->
                            <p class="text-[11px] text-slate-500 mt-1">
                                <strong>Tips:</strong> Biteship API membutuhkan pencarian nama <strong>Kecamatan, Kelurahan, Kota, atau Kode Pos</strong> (bukan nama PT/Toko). Jika ingin menandai lokasi bangunan/PT spesifik, gunakan tombol <strong>Pilih Pinpoint Peta Interaktif</strong>.
                            </p>

                            <!-- Hidden Inputs Submitted to Server -->
                            <input type="hidden" name="biteship_area_id" :value="selectedArea.id">
                            <input type="hidden" name="city_id" :value="selectedArea.id">
                            <input type="hidden" name="district" :value="selectedArea.district">
                            <input type="hidden" name="village" :value="selectedArea.village">
                            <input type="hidden" name="city" :value="selectedArea.city">
                            <input type="hidden" name="province" :value="selectedArea.province">

                            <!-- Live Autocomplete Suggestions Dropdown -->
                            <div 
                                x-show="showDropdown && suggestions.length > 0" 
                                @click.away="showDropdown = false" 
                                class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100 text-xs"
                            >
                                <template x-for="item in suggestions" :key="item.id">
                                    <button 
                                        type="button" 
                                        @click="selectArea(item)" 
                                        class="w-full text-left p-3 hover:bg-blue-50 transition-colors flex items-center justify-between cursor-pointer gap-2"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-slate-900 leading-snug truncate" x-text="item.name"></div>
                                            <div class="text-[11px] text-slate-500 truncate" x-text="item.subtext"></div>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0" :class="item.badgeClass || 'bg-blue-100 text-blue-800'" x-text="item.badge"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Pos</label>
                            <input 
                                type="text" 
                                name="postal_code" 
                                x-model="selectedArea.postal_code"
                                placeholder="12345"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                            >
                        </div>

                        <!-- MODAL PETA INTERAKTIF (LEAFLET MAP PICKER) -->
                        <div 
                            x-show="showMapModal" 
                            x-transition
                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                            style="display: none;"
                        >
                            <div @click.away="showMapModal = false" class="bg-white rounded-2xl max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-200">
                                <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                                    <div>
                                        <h3 class="font-bold text-sm flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>Pilih Pinpoint Lokasi Rumah/PT di Peta</span>
                                        </h3>
                                        <p class="text-[11px] text-slate-300">Geser atau klik pin merah pada titik lokasi pengiriman Anda</p>
                                    </div>
                                    <button type="button" @click="showMapModal = false" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
                                </div>
                                <div class="p-4 space-y-3">
                                    <div id="leafletMapContainer" class="w-full h-80 rounded-xl border border-slate-300 z-10"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-slate-600 font-semibold">Geser marker ke alamat rumah/kantor Anda untuk auto-detect.</span>
                                        <button 
                                            type="button" 
                                            @click="showMapModal = false" 
                                            class="bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl hover:bg-blue-800 cursor-pointer"
                                        >
                                            Gunakan Lokasi Ini
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- LIVE FULL ADDRESS PREVIEW BOX -->
                        <div class="p-4 bg-blue-50/80 border border-blue-200 rounded-2xl space-y-1.5 mt-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                                <span class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">Pratinjau Label Alamat Pengiriman Kurir</span>
                            </div>
                            <p class="text-xs text-blue-900 font-semibold leading-relaxed flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-700 shrink-0 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span x-text="searchQuery ? searchQuery : 'Tentukan wilayah pengiriman Anda di atas...'"></span>
                            </p>
                        </div>

                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-colors shadow-2xs cursor-pointer"
                        >
                            Simpan Alamat Tujuan Pengiriman
                        </button>
                    </div>
                </div>
            </form>

            <!-- TAB 3: KEAMANAN & KATA SANDI -->
            <div x-show="activeTab === 'keamanan'" class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Keamanan &amp; Ubah Kata Sandi</h2>
                    <p class="text-xs text-slate-500">Perbarui kata sandi akun Anda secara berkala untuk menjaga keamanan</p>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Kata Sandi Saat Ini <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="current_password" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Kata Sandi Baru (Min. 6 Karakter) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-colors shadow-2xs cursor-pointer"
                        >
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
