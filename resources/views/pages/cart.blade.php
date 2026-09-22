@extends('layouts.app')

@section('content')
<div x-data="cartApp()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    
    <!-- Main Grid: 2 Columns (Left ~65%, Right ~35%) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- LEFT COLUMN: Keranjang (~65% / 8 cols) -->
        <div class="lg:col-span-8 space-y-5">
            
            <!-- Header -->
            <div class="flex items-center justify-between pb-2">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Keranjang Belanja</h1>
                <span class="text-sm font-medium text-gray-500" x-text="items.length + ' Produk dalam Keranjang'"></span>
            </div>

            <!-- Select All Box -->
            <div x-show="items.length > 0" class="bg-white border border-gray-200 rounded-xl p-4 flex items-center justify-between shadow-sm">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        :checked="isAllSelected" 
                        @change="toggleSelectAll()" 
                        class="w-5 h-5 accent-blue-600 rounded cursor-pointer"
                    >
                    <span class="font-semibold text-gray-800 text-sm sm:text-base">
                        Pilih Semua (<span x-text="selectedCount"></span>)
                    </span>
                </label>
                <button 
                    type="button" 
                    @click="removeSelectedItems()" 
                    x-show="selectedCount > 0"
                    class="text-xs sm:text-sm font-medium text-red-500 hover:text-red-700 transition-colors cursor-pointer"
                >
                    Hapus Produk Terpilih
                </button>
            </div>

            <!-- Empty Cart State -->
            <div x-show="items.length === 0" class="bg-white border border-gray-200 rounded-xl p-8 sm:p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 bg-blue-50 text-blue-700 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Keranjang Belanja Anda Kosong</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto" x-text="isLoggedIn ? 'Sepertinya Anda belum menambahkan produk ke dalam keranjang. Silakan cari produk impian Anda.' : 'Anda belum masuk ke akun. Silakan masuk terlebih dahulu untuk melihat dan mengelola keranjang Anda.'"></p>
                
                <div class="pt-2">
                    <template x-if="!isLoggedIn">
                        <a href="{{ route('login') }}" class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-bold px-6 py-2.5 rounded-lg transition-colors text-xs sm:text-sm shadow-2xs">
                            Masuk ke Akun
                        </a>
                    </template>
                    <template x-if="isLoggedIn">
                        <a href="{{ route('home') }}" class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-bold px-6 py-2.5 rounded-lg transition-colors text-xs sm:text-sm shadow-2xs">
                            Jelajahi Produk
                        </a>
                    </template>
                </div>
            </div>

            <!-- Item Cards Container -->
            <div class="space-y-4" x-show="items.length > 0">
                <template x-for="(item, index) in items" :key="item.id">
                    <div 
                        class="bg-white border rounded-xl p-4 sm:p-5 shadow-sm space-y-4 sm:space-y-0 sm:flex sm:items-center sm:justify-between gap-4 transition-all duration-200"
                        :class="item.selected ? 'border-gray-200' : 'border-gray-200 opacity-75 bg-gray-50/50'"
                    >
                        <!-- Left Flex Items: Checkbox + Square Image + Title & Variant -->
                        <div class="flex items-center gap-3 sm:gap-4 flex-1">
                            <input 
                                type="checkbox" 
                                x-model="item.selected" 
                                class="w-5 h-5 accent-blue-600 rounded cursor-pointer shrink-0"
                            >
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-100 rounded-lg overflow-hidden shrink-0 flex items-center justify-center border border-gray-100 relative">
                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base leading-snug truncate" x-text="item.name"></h3>
                                <p class="text-xs text-gray-500 mt-1" x-text="item.variant"></p>
                                <template x-if="item.original_price && item.original_price > item.price">
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="text-xs text-slate-400 line-through" x-text="formatRupiah(item.original_price)"></span>
                                        <span class="bg-blue-100 text-blue-700 text-[10px] font-extrabold px-2 py-0.5 rounded" x-text="'Hemat ' + (item.discount ? item.discount.replace('Hemat ', '') : Math.round(((item.original_price - item.price)/item.original_price)*100) + '%')"></span>
                                    </div>
                                </template>
                                <p class="text-xs font-semibold text-gray-400 mt-0.5 sm:hidden" x-text="formatRupiah(item.price) + ' / unit'"></p>
                            </div>
                        </div>

                        <!-- Right Flex Items: Price + 'X' Icon + Quantity Selector -->
                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <template x-if="item.original_price && item.original_price > item.price">
                                        <div class="text-[11px] text-slate-400 line-through" x-text="formatRupiah(item.original_price * item.qty)"></div>
                                    </template>
                                    <span class="text-blue-600 font-bold text-base sm:text-lg" x-text="formatRupiah(item.price * item.qty)"></span>
                                </div>
                                <button 
                                    type="button" 
                                    @click="removeItem(index)" 
                                    class="text-gray-400 hover:text-red-500 transition-colors p-1 cursor-pointer" 
                                    title="Hapus Item"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Bordered Quantity Selector (- 1 +) -->
                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white">
                                <button 
                                    type="button" 
                                    @click="decreaseQty(item)" 
                                    :disabled="item.qty <= 1"
                                    :class="item.qty <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer'"
                                    class="px-3 py-1 text-gray-600 text-sm font-semibold transition-colors"
                                >
                                    -
                                </button>
                                <span class="px-3.5 py-1 text-sm font-semibold text-gray-800 border-x border-gray-200 bg-gray-50 select-none" x-text="item.qty"></span>
                                <button 
                                    type="button" 
                                    @click="increaseQty(item)" 
                                    class="px-3 py-1 text-gray-600 hover:bg-gray-100 text-sm font-semibold transition-colors cursor-pointer"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- RIGHT COLUMN: Ringkasan Belanja (~35% / 4 cols) -->
        <div class="lg:col-span-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm sticky top-20 space-y-5">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Ringkasan Belanja</h2>
                    <span class="text-xs font-semibold text-slate-500" x-text="selectedCount + ' produk dipilih'"></span>
                </div>

                <!-- Warning jika 0 produk dipilih -->
                <div x-show="selectedCount === 0" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 text-center font-medium">
                    Belum ada produk yang dicentang. Silakan centang produk terlebih dahulu.
                </div>

                <!-- Rincian Biaya Produk -->
                <div x-show="selectedCount > 0" class="space-y-3 text-xs sm:text-sm">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Total Harga (<span x-text="totalQty"></span> barang)</span>
                        <span class="font-semibold text-slate-900" x-text="formatRupiah(subtotal)"></span>
                    </div>

                    <template x-if="totalSavedAmount > 0">
                        <div class="flex items-center justify-between text-emerald-700 font-medium">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M13 7h.01M13 11h.01M13 15h.01M17 7h.01M17 11h.01M17 15h.01"/>
                                </svg>
                                <span>Total Hemat Diskon</span>
                            </span>
                            <span class="font-bold text-emerald-700" x-text="'- ' + formatRupiah(totalSavedAmount)"></span>
                        </div>
                    </template>
                </div>

                <!-- Total Harga Belanja Produk -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-sm sm:text-base font-extrabold text-slate-900">Total Belanja</span>
                    <span class="text-xl sm:text-2xl font-black text-blue-700" x-text="formatRupiah(subtotal)"></span>
                </div>

                <!-- Tombol Lanjut ke Ringkasan Checkout & Pembayaran -->
                <button 
                    type="button" 
                    @click="proceedToCheckout()"
                    :disabled="selectedCount === 0"
                    :class="selectedCount === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-blue-700 hover:bg-blue-800 text-white shadow-md hover:shadow-lg cursor-pointer'"
                    class="w-full font-extrabold py-3.5 px-4 rounded-xl transition-all text-center block text-sm sm:text-base tracking-tight"
                >
                    Lanjut ke Checkout &amp; Pembayaran
                </button>

                <!-- Jaminan Keamanan -->
                <div class="pt-2 text-center text-xs text-slate-500 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Transaksi 100% Aman &amp; Terpercaya</span>
                </div>

            </div>
        </div>

    </div>

</div>

</div>

@php
    $u = session('user') ?? auth()->user();
    $uName = $u ? (is_array($u) ? ($u['name'] ?? 'Budi Santoso') : ($u->name ?? 'Budi Santoso')) : 'Budi Santoso';
    $uAddress = $u ? (is_array($u) ? ($u['address'] ?? '') : ($u->address ?? '')) : 'Jl. Jendral Sudirman No. 45, Kebayoran Baru, Jakarta Selatan, 12190';
    $uCityId = $u ? (is_array($u) ? ($u['city_id'] ?? '153') : ($u->city_id ?? '153')) : '153';
@endphp

<script>
function cartApp() {
    return {
        isLoggedIn: @json(session()->has('user') || auth()->check()),
        customerName: @json($uName),
        customerAddress: @json($uAddress),
        items: @json($cartItems ?? []),

        // BITESHIP REAL-TIME API STATE
        selectedCityId: @json($uCityId),
        loadingShipping: false,
        isLiveApi: false,
        shippingStatusMsg: 'Memuat tarif ongkir...',
        selectedCourier: 'jne_reg',
        selectedCourierCost: 15000,
        selectedCourierName: 'JNE Express (REG)',
        couriers: [
            { id: 'jne_reg', code: 'jne', name: 'JNE Express (REG)', description: 'Layanan Reguler JNE', etd: '1-2 Hari', price: 15000 },
            { id: 'pos_kilat', code: 'pos', name: 'POS Indonesia (Kilat Khusus)', description: 'Layanan Kilat POS', etd: '2-3 Hari', price: 14000 },
            { id: 'tiki_reg', code: 'tiki', name: 'TIKI (REG)', description: 'Layanan Reguler TIKI', etd: '2-3 Hari', price: 16000 }
        ],

        init() {
            this.fetchShippingRates();
        },

        get totalWeight() {
            let selected = this.items.filter(item => item.selected);
            if (selected.length === 0) return 1000;
            let weight = selected.reduce((sum, item) => sum + ((item.weight || 500) * item.qty), 0);
            return Math.max(1000, weight);
        },

        async fetchShippingRates() {
            this.loadingShipping = true;
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
                if (data.source === 'api') {
                    this.isLiveApi = true;
                    this.shippingStatusMsg = '🟢 100% HARGA ASLI LIVE DARI BITESHIP API';
                } else {
                    this.isLiveApi = false;
                    this.shippingStatusMsg = data.message || '⚡ Server API Biteship lambat/offline. Menggunakan Tarif Resmi Ekspedisi.';
                }

                if (data.couriers && data.couriers.length > 0) {
                    this.couriers = data.couriers;
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
            } catch (err) {
                console.error("Biteship fetch error", err);
                this.isLiveApi = false;
                this.shippingStatusMsg = '⚡ Menggunakan Tarif Resmi Ekspedisi (Jarak & Berat).';
            } finally {
                this.loadingShipping = false;
            }
        },

        selectCourier(courier) {
            this.selectedCourier = courier.id;
            this.selectedCourierCost = courier.price;
            this.selectedCourierName = courier.name;
        },

        getSelectedCourierName() {
            return this.selectedCourierName || 'JNE Express';
        },

        // VOUCHER SYSTEM STATE
        showVoucherModal: false,
        manualVoucherCode: '',
        modalMsg: { text: '', type: '' },
        tempShippingVoucherId: null,
        tempDiscountVoucherId: null,
        appliedShippingVoucher: null,
        appliedDiscountVoucher: null,

        shippingVouchers: @json($dbShippingVouchers ?? []),
        discountVouchers: @json($dbDiscountVouchers ?? []),

        ineligibleVouchers: [
            {
                id: 'v_inelig_1',
                code: 'SUPERDEAL100K',
                title: 'Potongan Spesial Rp 100.000',
                reason: 'Minimal belanja Rp 1.000.000 belum terpenuhi',
                expiry: 'Berlaku s.d. 31 Agt 2026'
            },
            {
                id: 'v_inelig_2',
                code: 'FLASH70',
                title: 'Diskon Flash Sale 70%',
                reason: 'Kuota voucher harian telah habis',
                expiry: 'Berlaku s.d. 01 Agt 2026'
            }
        ],

        get selectedCount() {
            return this.items.filter(item => item.selected).length;
        },

        get isAllSelected() {
            return this.items.length > 0 && this.items.every(item => item.selected);
        },

        get totalQty() {
            return this.items
                .filter(item => item.selected)
                .reduce((sum, item) => sum + item.qty, 0);
        },

        get subtotal() {
            return this.items
                .filter(item => item.selected)
                .reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        get totalSavedAmount() {
            return this.items
                .filter(item => item.selected)
                .reduce((sum, item) => {
                    if (item.original_price && item.original_price > item.price) {
                        return sum + ((item.original_price - item.price) * item.qty);
                    }
                    return sum;
                }, 0);
        },

        get shippingCost() {
            if (this.selectedCount === 0) return 0;
            const courier = this.couriers.find(c => c.id === this.selectedCourier);
            return courier ? courier.price : (this.selectedCourierCost || 15000);
        },

        get shippingDiscountAmount() {
            if (!this.appliedShippingVoucher || this.selectedCount === 0) return 0;
            if (!this.isVoucherEligible(this.appliedShippingVoucher)) return 0;
            return Math.min(this.appliedShippingVoucher.discountValue, this.shippingCost);
        },

        get productDiscountAmount() {
            if (!this.appliedDiscountVoucher || this.selectedCount === 0) return 0;
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
            if (this.selectedCount === 0) return 0;
            const total = (this.subtotal + this.shippingCost) - this.totalDiscount;
            return total < 0 ? 0 : total;
        },

        toggleSelectAll() {
            const targetState = !this.isAllSelected;
            this.items.forEach(item => item.selected = targetState);
        },

        removeSelectedItems() {
            const selectedIds = this.items.filter(item => item.selected).map(item => item.id);
            if (selectedIds.length === 0) return;

            window.dispatchEvent(new CustomEvent('open-confirm', {
                detail: {
                    message: 'Apakah Anda yakin ingin menghapus produk yang dipilih dari keranjang?',
                    action: () => {
                        this.items = this.items.filter(item => !item.selected);
                        
                        fetch("{{ route('cart.removeSelected') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ product_ids: selectedIds, cart_keys: selectedIds, ids: selectedIds })
                        }).then(res => res.json()).then(data => {
                            window.location.reload();
                        }).catch(err => console.error(err));
                    }
                }
            }));
        },

        removeItem(index) {
            const item = this.items[index];
            if (!item) return;

            window.dispatchEvent(new CustomEvent('open-confirm', {
                detail: {
                    message: 'Hapus "' + item.name + '" dari keranjang?',
                    action: () => {
                        const targetId = item.id;
                        this.items.splice(index, 1);

                        fetch("{{ route('cart.remove') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ product_id: targetId, cart_key: targetId, id: targetId })
                        }).then(res => res.json()).then(data => {
                            window.location.reload();
                        }).catch(err => console.error(err));
                    }
                }
            }));
        },

        increaseQty(item) {
            item.qty++;
            this.updateQtyBackend(item.id, item.qty);
        },

        decreaseQty(item) {
            if (item.qty > 1) {
                item.qty--;
                this.updateQtyBackend(item.id, item.qty);
            }
        },

        updateQtyBackend(cartKey, qty) {
            fetch("{{ route('cart.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ cart_key: cartKey, product_id: cartKey, id: cartKey, qty: qty })
            }).catch(err => console.error(err));
        },

        getSelectedCourierName() {
            const courier = this.couriers.find(c => c.id === this.selectedCourier);
            return courier ? courier.name : '-';
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
                this.modalMsg = { text: 'Voucher ' + code + ' berhasil dipilih!', type: 'success' };
                return;
            }
            const foundDisc = this.discountVouchers.find(v => v.code === code);
            if (foundDisc) {
                if (!this.isVoucherEligible(foundDisc)) {
                    this.modalMsg = { text: 'Minimal belanja untuk ' + code + ' belum terpenuhi.', type: 'error' };
                    return;
                }
                this.tempDiscountVoucherId = foundDisc.id;
                this.modalMsg = { text: 'Voucher ' + code + ' berhasil dipilih!', type: 'success' };
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
                description: 'Voucher promo khusus ' + code,
                expiry: 'Berlaku hari ini',
                badge: 'PROMO KHUSUS',
                badgeBg: 'bg-blue-100 text-blue-800 border-blue-200'
            };
            this.discountVouchers.unshift(customV);
            this.tempDiscountVoucherId = customV.id;
            this.modalMsg = { text: 'Voucher ' + code + ' berhasil ditemukan dan dipilih!', type: 'success' };
        },

        removeShippingVoucher() {
            this.appliedShippingVoucher = null;
            this.tempShippingVoucherId = null;
        },

        removeDiscountVoucher() {
            this.appliedDiscountVoucher = null;
            this.tempDiscountVoucherId = null;
        },

        getCalculationExplanation() {
            if (this.selectedCount === 0) return 'Belum ada produk yang dipilih.';
            let explanation = `Subtotal (${this.totalQty} barang: ${this.formatRupiah(this.subtotal)}) + Ongkir ${this.getSelectedCourierName()} (${this.formatRupiah(this.shippingCost)})`;
            if (this.shippingDiscountAmount > 0) {
                explanation += ` - Gratis Ongkir (${this.formatRupiah(this.shippingDiscountAmount)})`;
            }
            if (this.productDiscountAmount > 0) {
                explanation += ` - Diskon Produk (${this.formatRupiah(this.productDiscountAmount)})`;
            }
            explanation += ` = ${this.formatRupiah(this.grandTotal)}.`;
            return explanation;
        },

        proceedToCheckout() {
            if (!this.isLoggedIn) {
                window.location.href = "{{ route('login') }}";
                return;
            }
            if (this.selectedCount === 0) return;

            fetch("{{ route('cart.updateSelected') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ items: this.items })
            }).finally(() => {
                window.location.href = "{{ route('checkout') }}";
            });
        },

        formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        }
    }
}
</script>
@endsection
