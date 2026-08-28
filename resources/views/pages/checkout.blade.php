@extends('layouts.app')

@section('content')
<div x-data="checkoutApp()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

    <!-- Header & Breadcrumb -->
    <div class="mb-6 space-y-1">
        <nav class="flex text-xs text-slate-500 gap-2 items-center">
            <a href="{{ route('home') }}" class="hover:text-blue-700">Beranda</a>
            <span>/</span>
            <a href="{{ route('cart') }}" class="hover:text-blue-700">Keranjang</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Ringkasan Checkout</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
            Ringkasan Checkout &amp; Pembayaran
        </h1>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- LEFT COLUMN (~65% / 8 cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. ALAMAT PENGIRIMAN -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">Alamat Pengiriman</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-700 text-[11px] font-bold px-2.5 py-0.5 rounded-full">Alamat Utama</span>
                            <a 
                                href="{{ route('profile') }}" 
                                class="text-xs font-bold text-blue-700 hover:text-blue-800 hover:underline border border-blue-200 px-3 py-1 rounded-lg transition-colors cursor-pointer"
                            >
                                Ubah Alamat
                            </a>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs sm:text-sm text-slate-700 leading-relaxed">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-sm sm:text-base">{{ $address['recipient_name'] }}</span>
                            <span class="text-slate-400">•</span>
                            <span class="font-semibold text-slate-700">{{ $address['phone'] }}</span>
                        </div>
                        <p class="text-slate-600">
                            {{ $address['full_address'] }}, {{ $address['city_province'] }} {{ $address['postal_code'] }}
                        </p>
                    </div>

                    <!-- Info Rute Pengiriman: Lokasi Toko ➔ Lokasi Pembeli -->
                    <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-xl text-xs space-y-1">
                        <div class="flex items-center justify-between font-bold text-slate-800 flex-wrap gap-2">
                            <span class="flex items-center gap-1.5 text-blue-800">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                Lokasi Asal Toko: <strong class="font-extrabold">{{ $storeOrigin['city_name'] ?? 'Kota Jakarta Pusat' }}</strong>
                            </span>
                            <span class="text-blue-600 font-extrabold text-sm">➔</span>
                            <span class="flex items-center gap-1.5 text-indigo-800">
                                Lokasi Tujuan Pembeli: <strong class="font-extrabold" x-text="getSelectedCityName()"></strong>
                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500">
                            Tarif ekspedisi JNE, POS, dan TIKI dihitung secara realtime berdasarkan rute lokasi toko ke kota tujuan pembeli.
                        </p>
                    </div>

                    <!-- Pilihan Kota Tujuan RajaOngkir -->
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>Pilih Kota / Kabupaten Tujuan Pembeli:</span>
                            <span class="text-[11px] text-blue-700 font-semibold" x-text="'Total Berat Paket: ' + totalWeight + ' gram'"></span>
                        </label>
                        <select 
                            x-model="selectedCityId" 
                            @change="fetchShippingRates()" 
                            class="w-full bg-slate-50 text-slate-800 text-xs sm:text-sm rounded-xl px-3.5 py-2.5 border border-slate-300 focus:border-blue-700 focus:bg-white focus:outline-none transition-all"
                        >
                            <option value="153">DKI Jakarta - Kota Jakarta Selatan (ID: 153)</option>
                            <option value="152">DKI Jakarta - Kota Jakarta Pusat (ID: 152)</option>
                            <option value="151">DKI Jakarta - Kota Jakarta Barat (ID: 151)</option>
                            <option value="154">DKI Jakarta - Kota Jakarta Timur (ID: 154)</option>
                            <option value="155">DKI Jakarta - Kota Jakarta Utara (ID: 155)</option>
                            <option value="22">Jawa Barat - Kota Bandung (ID: 22)</option>
                            <option value="54">Jawa Barat - Kota Bekasi (ID: 54)</option>
                            <option value="78">Jawa Barat - Kota Bogor (ID: 78)</option>
                            <option value="115">Jawa Barat - Kota Depok (ID: 115)</option>
                            <option value="444">Jawa Timur - Kota Surabaya (ID: 444)</option>
                            <option value="256">Jawa Timur - Kota Malang (ID: 256)</option>
                            <option value="399">Jawa Tengah - Kota Semarang (ID: 399)</option>
                            <option value="427">Jawa Tengah - Kota Surakarta / Solo (ID: 427)</option>
                            <option value="501">DI Yogyakarta - Kota Yogyakarta (ID: 501)</option>
                            <option value="114">Bali - Kota Denpasar (ID: 114)</option>
                            <option value="278">Sumatera Utara - Kota Medan (ID: 278)</option>
                            <option value="254">Sulawesi Selatan - Kota Makassar (ID: 254)</option>
                        </select>
                    </div>

                </div>

                <!-- 2. RINCIAN PRODUK YANG DIBELI -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">Produk Yang Dibeli ({{ count($checkoutItems) }})</h2>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100 space-y-3">
                        @foreach($checkoutItems as $item)
                            <div class="pt-3 first:pt-0 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 rounded-xl overflow-hidden shrink-0 border border-slate-200">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="space-y-1">
                                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm leading-snug line-clamp-2">{{ $item['name'] }}</h3>
                                        <p class="text-[11px] text-slate-500">{{ $item['variant'] ?? 'Varian Standar' }}</p>
                                        <div class="text-xs font-bold text-slate-700 sm:hidden">
                                            {{ $item['qty'] }} x Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-xs sm:text-sm font-extrabold text-blue-700">
                                        Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-medium hidden sm:block">
                                        {{ $item['qty'] }} barang @ Rp {{ number_format($item['price'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 3. PILIH METODE PENGIRIMAN REALTIME RAJAONGKIR (JNE, POS, TIKI) -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4 relative">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">Pilih Jasa Pengiriman</h2>
                                <p class="text-[11px] text-slate-500">Tarif Realtime RajaOngkir Starter (JNE, POS, TIKI)</p>
                            </div>
                        </div>
                        <template x-if="loadingShipping">
                            <div class="flex items-center gap-1.5 text-xs text-blue-700 font-bold bg-blue-50 px-2.5 py-1 rounded-lg">
                                <svg class="w-4 h-4 animate-spin text-blue-700" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Menghitung Ongkir...</span>
                            </div>
                        </template>
                    </div>

                    <!-- Notifikasi Error Jaringan / Fallback -->
                    <template x-if="isLiveApi">
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 font-bold flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>🟢 100% HARGA ASLI LIVE DARI RAJAONGKIR API</span>
                        </div>
                    </template>
                    <template x-if="!isLiveApi && shippingErrorMsg">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="shippingErrorMsg"></span>
                        </div>
                    </template>

                    <!-- Dynamic List Couriers from RajaOngkir -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="courier in couriers" :key="courier.id">
                            <label 
                                class="border rounded-xl p-3.5 flex items-center justify-between cursor-pointer transition-all hover:border-blue-700" 
                                :class="selectedCourier === courier.id ? 'border-2 border-blue-700 bg-blue-50/50 shadow-2xs' : 'border-slate-200 bg-white'"
                            >
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="radio" 
                                        name="courier_id" 
                                        :value="courier.id" 
                                        x-model="selectedCourier" 
                                        @change="selectedCourierCost = courier.price; selectedCourierName = courier.name" 
                                        class="w-4 h-4 text-blue-700 border-slate-300 focus:ring-blue-700 cursor-pointer"
                                    >
                                    <div>
                                        <div class="font-bold text-xs sm:text-sm text-slate-900" x-text="courier.name"></div>
                                        <div class="text-[11px] text-slate-500" x-text="'Estimasi: ' + courier.etd"></div>
                                        <div class="text-[10px] text-slate-400" x-text="courier.description || ''"></div>
                                    </div>
                                </div>
                                <div class="text-xs font-extrabold text-blue-700 shrink-0" x-text="formatRupiah(courier.price)"></div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- 4. PILIH METODE PEMBAYARAN -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">Pilih Metode Pembayaran</h2>
                        </div>
                    </div>

                    @foreach($paymentMethods as $group)
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $group['category'] }}</h3>
                            <div class="grid grid-cols-1 gap-2">
                                @foreach($group['methods'] as $method)
                                    <label class="border rounded-xl p-3 flex items-center justify-between cursor-pointer transition-all hover:border-blue-700" :class="selectedPayment === '{{ $method['id'] }}' ? 'border-2 border-blue-700 bg-blue-50/50 shadow-2xs' : 'border-slate-200 bg-white'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="payment_method" value="{{ $method['id'] }}" x-model="selectedPayment" class="w-4 h-4 text-blue-700 border-slate-300 focus:ring-blue-700 cursor-pointer">
                                            <span class="font-bold text-xs sm:text-sm text-slate-900">{{ $method['name'] }}</span>
                                        </div>
                                        @if(file_exists(public_path($method['logo'])))
                                            <img src="{{ asset($method['logo']) }}" alt="{{ $method['name'] }}" class="h-5 object-contain">
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            <!-- RIGHT COLUMN (~35% / 4 cols - Ringkasan Pembayaran Sticky) -->
            <div class="lg:col-span-4 sticky top-20">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm space-y-5">
                    
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3">
                        Ringkasan Pembayaran
                    </h2>

                    <!-- KOTAK VOUCHER DISKON -->
                    <div class="pt-3 pb-3 border-t border-b border-slate-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 011 1.732 2 2 0 01-1 1.732V17a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 01-1-1.732 2 2 0 011-1.732V7a2 2 0 00-2-2H5z"/>
                                </svg>
                                <span>Voucher Toko Online</span>
                            </label>
                            <template x-if="totalDiscount > 0">
                                <span class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full" x-text="'Hemat ' + formatRupiah(totalDiscount)"></span>
                            </template>
                        </div>

                        <!-- Tombol Buka Modal Pilih Voucher -->
                        <button 
                            type="button" 
                            @click="openVoucherModal()" 
                            class="w-full bg-blue-50/80 border-2 border-dashed border-blue-600 hover:bg-blue-100/70 text-blue-700 font-bold py-2.5 px-3 rounded-xl flex items-center justify-between transition-all shadow-2xs cursor-pointer group"
                        >
                            <div class="flex items-center gap-2 text-xs">
                                <svg class="w-4 h-4 text-blue-700 group-hover:scale-110 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M13 7h.01M13 11h.01M13 15h.01M17 7h.01M17 11h.01M17 15h.01"/>
                                </svg>
                                <span x-text="(appliedShippingVoucher || appliedDiscountVoucher) ? 'Ubah / Lihat Voucher Toko' : 'Pilih Voucher Toko Online'"></span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] font-extrabold">
                                <span x-text="(appliedShippingVoucher && appliedDiscountVoucher) ? '2 Voucher Dipilih' : (appliedShippingVoucher || appliedDiscountVoucher ? '1 Voucher Dipilih' : 'Pilih >')"></span>
                            </div>
                        </button>

                        <!-- LIST VOUCHER AKTIF YANG DITERAPKAN -->
                        <div x-show="appliedShippingVoucher || appliedDiscountVoucher" class="space-y-1.5 pt-0.5">
                            <!-- Badge Voucher Gratis Ongkir -->
                            <template x-if="appliedShippingVoucher">
                                <div class="p-2 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-900 shadow-2xs">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span class="text-xs shrink-0">🚚</span>
                                        <div class="truncate">
                                            <span class="font-bold">Gratis Ongkir:</span>
                                            <span class="font-mono bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-extrabold text-[10px] ml-1" x-text="appliedShippingVoucher.code"></span>
                                            <span class="text-[10px] font-semibold text-emerald-700 ml-1" x-text="'(-' + formatRupiah(shippingDiscountAmount) + ')'"></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeShippingVoucher()" class="text-red-600 hover:text-red-800 font-bold text-[11px] shrink-0 ml-1.5 cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            </template>

                            <!-- Badge Voucher Diskon Produk -->
                            <template x-if="appliedDiscountVoucher">
                                <div class="p-2 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between text-xs text-blue-900 shadow-2xs">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span class="text-xs shrink-0">🏷️</span>
                                        <div class="truncate">
                                            <span class="font-bold">Diskon Produk:</span>
                                            <span class="font-mono bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-extrabold text-[10px] ml-1" x-text="appliedDiscountVoucher.code"></span>
                                            <span class="text-[10px] font-semibold text-blue-700 ml-1" x-text="'(-' + formatRupiah(productDiscountAmount) + ')'"></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeDiscountVoucher()" class="text-red-600 hover:text-red-800 font-bold text-[11px] shrink-0 ml-1.5 cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden Inputs for Server Form Submit -->
                    <input type="hidden" name="voucher_code" :value="appliedDiscountVoucher ? appliedDiscountVoucher.code : (appliedShippingVoucher ? appliedShippingVoucher.code : '')">
                    <input type="hidden" name="shipping_voucher" :value="appliedShippingVoucher ? appliedShippingVoucher.code : ''">
                    <input type="hidden" name="discount_voucher" :value="appliedDiscountVoucher ? appliedDiscountVoucher.code : ''">
                    <input type="hidden" name="discount_amount" :value="totalDiscount">
                    <input type="hidden" name="shipping_cost" :value="selectedCourierCost">
                    <input type="hidden" name="courier_name" :value="selectedCourierName">

                    <!-- Rincian Biaya -->
                    <div class="space-y-2.5 text-xs sm:text-sm">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Subtotal Produk ({{ count($checkoutItems) }})</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format(array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $checkoutItems)), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Ongkos Kirim Realtime</span>
                            <span class="font-bold text-blue-700" x-text="formatRupiah(selectedCourierCost)"></span>
                        </div>

                        <!-- Potongan Gratis Ongkir -->
                        <template x-if="appliedShippingVoucher && shippingDiscountAmount > 0">
                            <div class="flex items-center justify-between text-emerald-700 font-medium">
                                <span class="flex items-center gap-1">🚚 Potongan Ongkir</span>
                                <span class="font-bold" x-text="'- ' + formatRupiah(shippingDiscountAmount)"></span>
                            </div>
                        </template>

                        <!-- Potongan Diskon Produk -->
                        <template x-if="appliedDiscountVoucher && productDiscountAmount > 0">
                            <div class="flex items-center justify-between text-blue-700 font-medium">
                                <span class="flex items-center gap-1">🏷️ Diskon Produk</span>
                                <span class="font-bold" x-text="'- ' + formatRupiah(productDiscountAmount)"></span>
                            </div>
                        </template>

                        <div class="flex items-center justify-between text-slate-600">
                            <span>Biaya Layanan</span>
                            <span class="font-bold text-emerald-600">GRATIS</span>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-sm sm:text-base font-extrabold text-slate-900">Total Tagihan</span>
                            <span class="text-lg sm:text-xl font-black text-blue-700" x-text="formatRupiah(grandTotal)"></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold text-sm py-3.5 rounded-xl transition-all shadow-md cursor-pointer flex items-center justify-center gap-2"
                    >
                        <span>Konfirmasi &amp; Bayar Pesanan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>

                    <div class="text-center text-[11px] text-slate-400 flex items-center justify-center gap-1 pt-1">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Transaksi 100% Aman &amp; Terenkripsi</span>
                    </div>

                </div>
            </div>

        </div>

        <!-- Include Voucher Selection Modal Component -->
        <x-voucher-modal />

    </form>

</div>

@push('scripts')
<script>
function checkoutApp() {
    return {
        subtotal: {{ array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $checkoutItems)) }},
        selectedCityId: '{{ $address["city_id"] ?? "153" }}',
        totalWeight: {{ $totalWeight ?? 1000 }},
        couriers: @json($couriers),
        selectedCourier: '{{ $couriers[0]["id"] ?? "jne_reg" }}',
        selectedCourierCost: {{ $couriers[0]["price"] ?? 15000 }},
        selectedCourierName: '{{ $couriers[0]["name"] ?? "JNE Express (Reguler)" }}',
        selectedPayment: 'bca_va',
        loadingShipping: false,
        shippingErrorMsg: '',
        isLiveApi: false,

        getSelectedCityName() {
            const citiesMap = {
                '153': 'Kota Jakarta Selatan',
                '152': 'Kota Jakarta Pusat',
                '151': 'Kota Jakarta Barat',
                '154': 'Kota Jakarta Timur',
                '155': 'Kota Jakarta Utara',
                '22': 'Kota Bandung',
                '54': 'Kota Bekasi',
                '78': 'Kota Bogor',
                '115': 'Kota Depok',
                '444': 'Kota Surabaya',
                '256': 'Kota Malang',
                '399': 'Kota Semarang',
                '427': 'Kota Surakarta / Solo',
                '501': 'Kota Yogyakarta',
                '114': 'Kota Denpasar',
                '278': 'Kota Medan',
                '254': 'Kota Makassar'
            };
            return citiesMap[this.selectedCityId] || 'Kota ID: ' + this.selectedCityId;
        },

        // VOUCHER SYSTEM STATE
        showVoucherModal: false,
        manualVoucherCode: '',
        modalMsg: { text: '', type: '' },
        tempShippingVoucherId: null,
        tempDiscountVoucherId: null,
        appliedShippingVoucher: null,
        appliedDiscountVoucher: null,

        shippingVouchers: ( @json($dbShippingVouchers ?? []) ).length > 0 ? @json($dbShippingVouchers ?? []) : [
            {
                id: 'v_ongkir_1',
                code: 'FREEONGKIR20',
                category: 'shipping',
                title: 'Gratis Ongkir s.d. Rp 20.000',
                minSpend: 50000,
                discountType: 'shipping',
                discountValue: 20000,
                description: 'Min. belanja Rp 50.000 (Khusus potongan ongkos kirim)',
                expiry: 'Berlaku s.d. 31 Agt 2026',
                badge: 'GRATIS ONGKIR',
                badgeBg: 'bg-emerald-100 text-emerald-800 border-emerald-200'
            },
            {
                id: 'v_ongkir_2',
                code: 'FREEONGKIR15',
                category: 'shipping',
                title: 'Gratis Ongkir s.d. Rp 15.000',
                minSpend: 30000,
                discountType: 'shipping',
                discountValue: 15000,
                description: 'Min. belanja Rp 30.000 (Khusus potongan ongkos kirim)',
                expiry: 'Berlaku s.d. 25 Agt 2026',
                badge: 'GRATIS ONGKIR',
                badgeBg: 'bg-emerald-100 text-emerald-800 border-emerald-200'
            }
        ],

        discountVouchers: ( @json($dbDiscountVouchers ?? []) ).length > 0 ? @json($dbDiscountVouchers ?? []) : [
            {
                id: 'v_diskon_1',
                code: 'DISKON50K',
                category: 'discount',
                title: 'Potongan Harga Rp 50.000',
                minSpend: 200000,
                discountType: 'fixed',
                discountValue: 50000,
                description: 'Min. belanja Rp 200.000 (Khusus potongan harga produk)',
                expiry: 'Berlaku s.d. 31 Agt 2026',
                badge: 'DISKON PRODUK',
                badgeBg: 'bg-blue-100 text-blue-800 border-blue-200'
            },
            {
                id: 'v_diskon_2',
                code: 'DISKON10',
                category: 'discount',
                title: 'Diskon 10% (s.d. Rp 100.000)',
                minSpend: 0,
                discountType: 'percent',
                discountValue: 10,
                maxDiscount: 100000,
                description: 'Tanpa min. belanja (Khusus potongan harga produk)',
                expiry: 'Berlaku s.d. 20 Agt 2026',
                badge: 'DISKON PERSEN',
                badgeBg: 'bg-purple-100 text-purple-800 border-purple-200'
            },
            {
                id: 'v_diskon_3',
                code: 'CASHBACK25K',
                category: 'discount',
                title: 'Cashback Ekstra Rp 25.000',
                minSpend: 100000,
                discountType: 'fixed',
                discountValue: 25000,
                description: 'Min. belanja Rp 100.000 (Khusus potongan harga produk)',
                expiry: 'Berlaku s.d. 28 Agt 2026',
                badge: 'CASHBACK',
                badgeBg: 'bg-amber-100 text-amber-800 border-amber-200'
            }
        ],

        init() {
            // Fetch live shipping rates when checkout loads
            this.fetchShippingRates();
        },

        async fetchShippingRates() {
            this.loadingShipping = true;
            this.shippingErrorMsg = '';

            try {
                const response = await fetch('{{ route("checkout.calculateShipping") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        destination_city_id: this.selectedCityId,
                        weight: this.totalWeight
                    })
                });

                const data = await response.json();

                if (data.couriers && data.couriers.length > 0) {
                    this.couriers = data.couriers;
                    
                    // Match currently selected courier or fallback to first courier
                    const match = this.couriers.find(c => c.id === this.selectedCourier);
                    if (match) {
                        this.selectedCourierCost = match.price;
                        this.selectedCourierName = match.name;
                    } else {
                        this.selectedCourier = this.couriers[0].id;
                        this.selectedCourierCost = this.couriers[0].price;
                        this.selectedCourierName = this.couriers[0].name;
                    }
                }

                if (data.source === 'api') {
                    this.isLiveApi = true;
                    this.shippingErrorMsg = '';
                } else {
                    this.isLiveApi = false;
                    this.shippingErrorMsg = data.message || 'Menggunakan estimasi tarif lokal (backup jaringan).';
                }

            } catch (error) {
                console.error('Error fetching RajaOngkir rates:', error);
                this.shippingErrorMsg = 'Koneksi RajaOngkir API tidak merespon. Menggunakan tarif standar.';
            } finally {
                this.loadingShipping = false;
            }
        },

        get shippingCost() {
            return this.selectedCourierCost || 0;
        },

        get shippingDiscountAmount() {
            if (!this.appliedShippingVoucher) return 0;
            if (!this.isVoucherEligible(this.appliedShippingVoucher)) return 0;
            return Math.min(this.appliedShippingVoucher.discountValue, this.shippingCost);
        },

        get productDiscountAmount() {
            if (!this.appliedDiscountVoucher) return 0;
            if (!this.isVoucherEligible(this.appliedDiscountVoucher)) return 0;
            if (this.appliedDiscountVoucher.discountType === 'fixed') {
                return Math.min(this.appliedDiscountVoucher.discountValue, this.subtotal);
            }
            if (this.appliedDiscountVoucher.discountType === 'percent') {
                const p = Math.round((this.subtotal * this.appliedDiscountVoucher.discountValue) / 100);
                return Math.min(p, this.appliedDiscountVoucher.maxDiscount || 999999999);
            }
            return 0;
        },

        get totalDiscount() {
            return this.shippingDiscountAmount + this.productDiscountAmount;
        },

        get grandTotal() {
            const total = (this.subtotal + this.shippingCost) - this.totalDiscount;
            return total < 0 ? 0 : total;
        },

        // VOUCHER MODAL METHODS
        openVoucherModal() {
            this.modalMsg = { text: '', type: '' };
            this.manualVoucherCode = '';
            this.tempShippingVoucherId = this.appliedShippingVoucher ? this.appliedShippingVoucher.id : null;
            this.tempDiscountVoucherId = this.appliedDiscountVoucher ? this.appliedDiscountVoucher.id : null;
            this.showVoucherModal = true;
        },

        isVoucherEligible(v) {
            return (this.subtotal || 0) >= (v.minSpend || 0);
        },

        calculateTempDiscountTotal() {
            let total = 0;
            const shipV = this.shippingVouchers.find(v => v.id === this.tempShippingVoucherId);
            if (shipV && this.isVoucherEligible(shipV)) {
                total += Math.min(shipV.discountValue, this.shippingCost || 0);
            }
            const discV = this.discountVouchers.find(v => v.id === this.tempDiscountVoucherId);
            if (discV && this.isVoucherEligible(discV)) {
                if (discV.discountType === 'fixed') {
                    total += Math.min(discV.discountValue, this.subtotal || 0);
                } else if (discV.discountType === 'percent') {
                    const p = Math.round(((this.subtotal || 0) * discV.discountValue) / 100);
                    total += Math.min(p, discV.maxDiscount || 999999999);
                }
            }
            return total;
        },

        confirmVoucherSelection() {
            const shipV = this.shippingVouchers.find(v => v.id === this.tempShippingVoucherId);
            const discV = this.discountVouchers.find(v => v.id === this.tempDiscountVoucherId);
            this.appliedShippingVoucher = (shipV && this.isVoucherEligible(shipV)) ? shipV : null;
            this.appliedDiscountVoucher = (discV && this.isVoucherEligible(discV)) ? discV : null;
            this.showVoucherModal = false;
        },

        applyManualVoucherInModal() {
            const code = this.manualVoucherCode.trim().toUpperCase();
            if (!code) {
                this.modalMsg = { text: 'Masukkan kode promo terlebih dahulu.', type: 'error' };
                return;
            }
            const foundShip = this.shippingVouchers.find(v => v.code === code);
            if (foundShip) {
                if (!this.isVoucherEligible(foundShip)) {
                    this.modalMsg = { text: 'Minimal belanja untuk ' + code + ' belum terpenuhi.', type: 'error' };
                    return;
                }
                this.tempShippingVoucherId = foundShip.id;
                this.modalMsg = { text: 'Voucher Gratis Ongkir ' + code + ' dipilih! (Maks. 1 per pesanan)', type: 'success' };
                return;
            }
            const foundDisc = this.discountVouchers.find(v => v.code === code);
            if (foundDisc) {
                if (!this.isVoucherEligible(foundDisc)) {
                    this.modalMsg = { text: 'Minimal belanja untuk ' + code + ' belum terpenuhi.', type: 'error' };
                    return;
                }
                this.tempDiscountVoucherId = foundDisc.id;
                this.modalMsg = { text: 'Voucher Diskon Produk ' + code + ' dipilih! (Maks. 1 per pesanan)', type: 'success' };
                return;
            }
            const customV = {
                id: 'v_custom_' + Date.now(),
                code: code,
                category: 'discount',
                title: 'Voucher Promo ' + code,
                minSpend: 0,
                discountType: 'fixed',
                discountValue: 30000,
                description: 'Voucher promo khusus ' + code + ' (Khusus potongan harga produk)',
                expiry: 'Berlaku hari ini',
                badge: 'PROMO KHUSUS',
                badgeBg: 'bg-blue-100 text-blue-800 border-blue-200'
            };
            this.discountVouchers.unshift(customV);
            this.tempDiscountVoucherId = customV.id;
            this.modalMsg = { text: 'Voucher Diskon ' + code + ' ditemukan & dipilih!', type: 'success' };
        },

        removeShippingVoucher() {
            this.appliedShippingVoucher = null;
            this.tempShippingVoucherId = null;
        },

        removeDiscountVoucher() {
            this.appliedDiscountVoucher = null;
            this.tempDiscountVoucherId = null;
        },

        formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        }
    }
}
</script>
@endpush
@endsection
