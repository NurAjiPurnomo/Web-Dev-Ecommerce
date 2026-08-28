<!-- MODAL PILIH VOUCHER TOKO ONLINE -->
<div 
    x-show="showVoucherModal" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    style="display: none;"
    @keydown.escape.window="showVoucherModal = false"
>
    <div 
        @click.outside="showVoucherModal = false"
        class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col max-h-[85vh]"
    >
        
        <!-- Modal Header -->
        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-700 text-white flex items-center justify-center shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 011 1.732 2 2 0 01-1 1.732V17a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 01-1-1.732 2 2 0 011-1.732V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base sm:text-lg leading-tight">Pilih Voucher Toko Online</h3>
                    <p class="text-[11px] text-slate-500">Gunakan voucher hemat untuk mendapatkan potongan biaya</p>
                </div>
            </div>
            <button 
                type="button" 
                @click="showVoucherModal = false"
                class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center font-bold transition-colors cursor-pointer"
            >
                ✕
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-6 flex-1">

            <!-- Manual Input Section -->
            <div class="space-y-1.5 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <label class="block text-xs font-bold text-slate-700">Punya Kode Promo Khusus?</label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        x-model="manualVoucherCode" 
                        placeholder="Masukkan kode promo di sini..." 
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:border-blue-700 focus:outline-none uppercase"
                    >
                    <button 
                        type="button" 
                        @click="applyManualVoucherInModal()" 
                        class="bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs px-4 py-2 rounded-lg transition-colors shrink-0 cursor-pointer"
                    >
                        Terapkan
                    </button>
                </div>
                <template x-if="modalMsg.text">
                    <p class="text-[11px] font-medium" :class="modalMsg.type === 'error' ? 'text-red-600' : 'text-emerald-700'" x-text="modalMsg.text"></p>
                </template>
            </div>

            <!-- SECTION 1: VOUCHER GRATIS ONGKIR -->
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🚚</span>
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-xs sm:text-sm uppercase tracking-tight">Voucher Gratis Ongkir</h4>
                            <p class="text-[10px] text-emerald-700 font-semibold">Khusus Potongan Biaya Ongkos Kirim</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full font-extrabold">Maks. 1 Voucher</span>
                </div>

                <div class="space-y-2.5">
                    <template x-for="v in shippingVouchers" :key="v.id">
                        <label 
                            class="border rounded-xl p-3.5 flex items-start gap-3 transition-all select-none cursor-pointer"
                            :class="[
                                isVoucherEligible(v) ? (tempShippingVoucherId === v.id ? 'border-2 border-emerald-600 bg-emerald-50/50 shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300') : 'border-slate-200 bg-slate-50 opacity-60 cursor-not-allowed'
                            ]"
                        >
                            <input 
                                type="radio" 
                                name="temp_shipping_voucher" 
                                :value="v.id" 
                                :checked="tempShippingVoucherId === v.id"
                                :disabled="!isVoucherEligible(v)"
                                @change="tempShippingVoucherId = (tempShippingVoucherId === v.id ? null : v.id)"
                                @click="if(tempShippingVoucherId === v.id) { tempShippingVoucherId = null; $event.preventDefault(); }"
                                class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-600 mt-1 cursor-pointer"
                            >
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-extrabold text-xs sm:text-sm text-slate-900" x-text="v.title"></span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded border" :class="v.badgeBg" x-text="v.badge"></span>
                                    <template x-if="isVoucherEligible(v)">
                                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-extrabold px-2 py-0.5 rounded border border-emerald-200">✓ Potong Ongkir</span>
                                    </template>
                                </div>
                                <p class="text-xs text-slate-600" x-text="v.description"></p>
                                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                                    <span class="font-mono font-bold text-slate-700" x-text="'Kode: ' + v.code"></span>
                                    <span x-text="v.expiry"></span>
                                </div>
                                <template x-if="!isVoucherEligible(v)">
                                    <div class="mt-1.5 flex items-center justify-between text-[11px] font-bold text-red-600 bg-red-50 border border-red-200 px-2.5 py-1 rounded-md">
                                        <span>Belum Memenuhi Minimal Belanja</span>
                                        <span x-text="'Kurang ' + formatRupiah(v.minSpend - subtotal) + ' (Min. Belanja ' + formatRupiah(v.minSpend) + ')'"></span>
                                    </div>
                                </template>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <!-- SECTION 2: VOUCHER DISKON / CASHBACK -->
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🏷️</span>
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-xs sm:text-sm uppercase tracking-tight">Voucher Diskon Produk</h4>
                            <p class="text-[10px] text-blue-700 font-semibold">Khusus Potongan Subtotal Harga Produk</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-blue-800 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full font-extrabold">Maks. 1 Voucher</span>
                </div>

                <div class="space-y-2.5">
                    <template x-for="v in discountVouchers" :key="v.id">
                        <label 
                            class="border rounded-xl p-3.5 flex items-start gap-3 transition-all select-none cursor-pointer"
                            :class="[
                                isVoucherEligible(v) ? (tempDiscountVoucherId === v.id ? 'border-2 border-blue-700 bg-blue-50/50 shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300') : 'border-slate-200 bg-slate-50 opacity-60 cursor-not-allowed'
                            ]"
                        >
                            <input 
                                type="radio" 
                                name="temp_discount_voucher" 
                                :value="v.id" 
                                :checked="tempDiscountVoucherId === v.id"
                                :disabled="!isVoucherEligible(v)"
                                @change="tempDiscountVoucherId = (tempDiscountVoucherId === v.id ? null : v.id)"
                                @click="if(tempDiscountVoucherId === v.id) { tempDiscountVoucherId = null; $event.preventDefault(); }"
                                class="w-4 h-4 text-blue-700 border-slate-300 focus:ring-blue-700 mt-1 cursor-pointer"
                            >
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-extrabold text-xs sm:text-sm text-slate-900" x-text="v.title"></span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded border" :class="v.badgeBg" x-text="v.badge"></span>
                                    <template x-if="isVoucherEligible(v)">
                                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-extrabold px-2 py-0.5 rounded border border-emerald-200">✓ Siap Digunakan</span>
                                    </template>
                                </div>
                                <p class="text-xs text-slate-600" x-text="v.description"></p>
                                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                                    <span class="font-mono font-bold text-slate-700" x-text="'Kode: ' + v.code"></span>
                                    <span x-text="v.expiry"></span>
                                </div>
                                <template x-if="!isVoucherEligible(v)">
                                    <div class="mt-1.5 flex items-center justify-between text-[11px] font-bold text-red-600 bg-red-50 border border-red-200 px-2.5 py-1 rounded-md">
                                        <span>Belum Memenuhi Minimal Belanja</span>
                                        <span x-text="'Kurang ' + formatRupiah(v.minSpend - subtotal) + ' (Min. Belanja ' + formatRupiah(v.minSpend) + ')'"></span>
                                    </div>
                                </template>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <!-- SECTION 3: VOUCHER TIDAK BERLAKU -->
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🚫</span>
                        <h4 class="font-extrabold text-slate-400 text-xs sm:text-sm uppercase tracking-tight">Voucher Tidak Berlaku</h4>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <template x-for="v in ineligibleVouchers" :key="v.id">
                        <div class="border border-slate-200 bg-slate-50/80 rounded-xl p-3.5 opacity-60 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs sm:text-sm text-slate-700" x-text="v.title"></span>
                                <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded">Tidak Memenuhi</span>
                            </div>
                            <p class="text-xs text-slate-500" x-text="v.reason"></p>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-0.5">
                                <span class="font-mono font-semibold" x-text="'Kode: ' + v.code"></span>
                                <span x-text="v.expiry"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-5 sm:px-6 py-4 border-t border-slate-100 bg-slate-50/80 flex items-center justify-between gap-4">
            <div>
                <span class="text-[11px] text-slate-500 block font-medium">Estimasi Total Hemat:</span>
                <span class="text-base sm:text-lg font-black text-emerald-600" x-text="formatRupiah(calculateTempDiscountTotal())"></span>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="showVoucherModal = false"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="button" 
                    @click="confirmVoucherSelection()"
                    class="px-6 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-extrabold text-xs sm:text-sm transition-all shadow-md cursor-pointer flex items-center gap-1.5"
                >
                    <span>Gunakan Voucher</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>
        </div>

    </div>
</div>
