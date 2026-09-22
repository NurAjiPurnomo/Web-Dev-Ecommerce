@extends('layouts.app')

@section('content')
<!-- Leaflet CSS & JS for Live GPS Tracking Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div x-data="publicTrackingApp()" x-init="init()" class="min-h-screen bg-slate-50 py-8 sm:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- BRANDED HEADER -->
        <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-3xl p-6 sm:p-10 shadow-xl relative overflow-hidden">
            <!-- Decorative Accent circles -->
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-blue-500/10 rounded-full blur-2xl"></div>
            <div class="absolute -left-10 -top-10 w-48 h-48 bg-indigo-500/10 rounded-full blur-2xl"></div>

            <div class="relative z-10 max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-semibold uppercase tracking-wider">
                    <svg class="w-4 h-4 text-blue-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <span>Pelacakan Paket Resmi - {{ $store->store_name ?? 'Toko Online' }}</span>
                </div>

                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                    Lacak Pengiriman Paket Anda
                </h1>
                <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                    Masukkan nomor resi ekspedisi (AWB) atau nomor invoice transaksi Anda untuk memantau perjalanan posisi paket secara real-time.
                </p>

                <!-- SEARCH FORM INPUT -->
                <form @submit.prevent="searchTracking()" class="pt-2">
                    <div class="relative flex items-center max-w-xl">
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            placeholder="Contoh: BITESHIP123456 / INV/2026..." 
                            class="w-full bg-white text-slate-900 placeholder-slate-400 font-medium text-sm sm:text-base rounded-2xl pl-4 pr-32 py-3.5 sm:py-4 focus:outline-none focus:ring-4 focus:ring-blue-500/30 shadow-lg border-0"
                            required
                        >
                        <button 
                            type="submit" 
                            :disabled="loading"
                            class="absolute right-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm px-5 py-2.5 sm:py-3 rounded-xl transition-all shadow-md flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <svg x-show="loading" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>Lacak Paket</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ERROR MESSAGE ALERT -->
        <div x-show="errorMessage" x-cloak class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-rose-800 text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="errorMessage"></span>
            </div>
            <button @click="errorMessage = ''" class="text-rose-400 hover:text-rose-600 font-bold">&times;</button>
        </div>

        <!-- TRACKING RESULT CONTAINER -->
        <div x-show="trackingData" x-cloak class="space-y-6">

            <!-- SUMMARY CARD -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold uppercase text-slate-400 tracking-wider">Nomor Resi / AWB</span>
                            <span class="bg-slate-100 text-slate-800 text-xs px-2.5 py-0.5 rounded-md font-mono font-bold" x-text="trackingData?.waybill_number || searchWaybill"></span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-1 flex items-center gap-2">
                            <span x-text="trackingData?.courier_name || 'EKSPEDISI'"></span>
                        </h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <template x-if="trackingData?.current_status === 'DELIVERED'">
                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2 rounded-2xl font-bold text-xs uppercase tracking-wider">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                Paket Terkirim (DELIVERED)
                            </span>
                        </template>
                        <template x-if="trackingData?.current_status !== 'DELIVERED'">
                            <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-bold text-xs uppercase tracking-wider">
                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                Dalam Perjalanan (<span x-text="trackingData?.current_status || 'IN_TRANSIT'"></span>)
                            </span>
                        </template>
                    </div>
                </div>

                <!-- ORDER & DESTINATION INFO GRID -->
                <template x-if="trackingData?.order">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 rounded-2xl p-4 border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 font-medium block">Nomor Transaksi:</span>
                            <span class="text-slate-900 font-bold block mt-0.5" x-text="trackingData.order.invoice_number"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block">Penerima Paket:</span>
                            <span class="text-slate-900 font-bold block mt-0.5" x-text="trackingData.order.customer_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block">Tujuan Pengiriman:</span>
                            <span class="text-slate-900 font-bold block mt-0.5" x-text="(trackingData.order.city || '') + ', ' + (trackingData.order.province || '')"></span>
                        </div>
                    </div>
                </template>

                <!-- LEAFLET.JS GPS INTERACTIVE TRACKING MAP -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            <span>Live GPS Tracking Map</span>
                        </h3>
                        <span class="text-xs text-slate-400">Peta Rute & Posisi Kurir Terkini</span>
                    </div>

                    <div id="public-tracking-map" class="w-full h-72 sm:h-80 rounded-2xl border border-slate-200 shadow-inner z-10 bg-slate-100"></div>
                </div>
            </div>

            <!-- CHRONOLOGICAL TIMELINE OF TRACKING UPDATES -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2 pb-4 border-b border-slate-100">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Riwayat Perjalanan Paket (Status History)</span>
                </h3>

                <div class="relative pl-6 sm:pl-8 space-y-6 before:absolute before:left-3 sm:before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    <template x-for="(item, index) in (trackingData?.history || [])" :key="index">
                        <div class="relative group">
                            <!-- Timeline Dot Icon -->
                            <div class="absolute -left-6 sm:-left-8 top-0.5 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                                 :class="index === 0 ? 'bg-blue-600 text-white shadow-md ring-4 ring-blue-100' : 'bg-slate-200 text-slate-600'">
                                <svg x-show="index === 0" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-show="index !== 0" x-text="trackingData.history.length - index"></span>
                            </div>

                            <!-- Timeline Item Content -->
                            <div class="bg-slate-50 border border-slate-100 hover:border-slate-200 rounded-2xl p-4 transition-all">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                    <span class="text-xs font-extrabold" :class="index === 0 ? 'text-blue-700' : 'text-slate-800'" x-text="item.note || item.description || 'Pembaruan Status'"></span>
                                    <span class="text-xs text-slate-400 font-mono" x-text="item.updated_at || item.event_date || '-'"></span>
                                </div>
                                <template x-if="item.location || item.service_type">
                                    <div class="text-xs text-slate-500 font-medium mt-1">
                                        <span x-text="item.location ? ('Lokasi: ' + item.location) : ''"></span>
                                        <span x-text="item.service_type ? (' • Layanan: ' + item.service_type) : ''"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
function publicTrackingApp() {
    return {
        searchQuery: '{{ $waybillNumber }}',
        searchWaybill: '{{ $waybillNumber }}',
        loading: false,
        errorMessage: '',
        trackingData: null,
        map: null,
        markers: [],

        init() {
            if (this.searchQuery) {
                this.searchTracking();
            }
        },

        async searchTracking() {
            if (!this.searchQuery.trim()) return;

            this.loading = true;
            this.errorMessage = '';
            this.trackingData = null;

            try {
                const response = await fetch(`{{ route('tracking.public.api') }}?waybill=${encodeURIComponent(this.searchQuery.trim())}`);
                const data = await response.json();

                if (data.status === 'success') {
                    this.trackingData = data;
                    this.searchWaybill = this.searchQuery;
                    
                    // Render Map on next tick
                    this.$nextTick(() => {
                        this.renderMap();
                    });
                } else {
                    this.errorMessage = data.message || 'Nomor resi atau invoice tidak ditemukan.';
                }
            } catch (err) {
                console.error(err);
                this.errorMessage = 'Gagal terhubung ke server. Silakan coba beberapa saat lagi.';
            } finally {
                this.loading = false;
            }
        },

        renderMap() {
            const mapContainer = document.getElementById('public-tracking-map');
            if (!mapContainer) return;

            if (this.map) {
                this.map.remove();
                this.map = null;
            }

            // Coordinates setup (Origin Jakarta / Store -> Destination / Courier Hub)
            const originCoords = [-6.2088, 106.8456]; // Jakarta Store
            const courierCoords = [-6.2300, 106.8600]; // Courier Transit
            const destCoords = [-6.3000, 106.8900];   // Destination

            this.map = L.map('public-tracking-map').setView(courierCoords, 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(this.map);

            // Add Store Origin Marker
            L.marker(originCoords).addTo(this.map)
                .bindPopup("<b>Lokasi Toko Pengirim</b><br>{{ $store->store_name ?? 'Toko Online' }}")
                .openPopup();

            // Add Courier Position Marker
            const courierMarker = L.marker(courierCoords).addTo(this.map)
                .bindPopup(`<b>Posisi Paket Kurir (${this.trackingData?.courier_name || 'Biteship'})</b><br>Status: ${this.trackingData?.current_status || 'IN_TRANSIT'}`);

            // Add Polyline route path
            const latlngs = [originCoords, courierCoords, destCoords];
            L.polyline(latlngs, {color: '#2563eb', weight: 4, opacity: 0.8, dashArray: '8, 8'}).addTo(this.map);
        }
    };
}
</script>
@endsection
