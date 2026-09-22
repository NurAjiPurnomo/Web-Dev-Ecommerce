@extends('layouts.app')

@section('content')
@php
    $isCod = ($order['payment_method'] ?? '') === 'cod';
    $status = $order['status'] ?? ($isCod ? 'diproses' : 'belum_dibayar');
    $isUnpaid = ($status === 'belum_dibayar');
    $payMethod = strtolower($order['payment_method'] ?? 'qris');
    $createdAtTs = !empty($order['created_at_timestamp'])
        ? (int)$order['created_at_timestamp']
        : (isset($order['created_at']) ? (strtotime(str_replace(' WIB', '', $order['created_at'])) ?: time()) : time());
    $expiryTs = $createdAtTs + 86400; // 24 Hours real-time deadline from creation time
@endphp

<script>
function checkoutSuccessApp() {
    return {
        isSubmitting: false,
        isPaid: @json(!$isUnpaid),
        copiedVa: false,
        copiedInvoice: false,
        expiryTimestamp: {{ $expiryTs }},
        hours: '00',
        minutes: '00',
        seconds: '00',
        isExpired: false,
        updateTimer() {
            const now = Math.floor(Date.now() / 1000);
            const diff = Math.max(0, this.expiryTimestamp - now);
            if (diff <= 0) {
                this.isExpired = true;
                this.hours = '00';
                this.minutes = '00';
                this.seconds = '00';
                return;
            }
            const h = Math.floor(diff / 3600);
            const m = Math.floor((diff % 3600) / 60);
            const s = diff % 60;
            this.hours = String(h).padStart(2, '0');
            this.minutes = String(m).padStart(2, '0');
            this.seconds = String(s).padStart(2, '0');
        },
        startAutoPolling() {
            if (this.isPaid) return;
            const orderIdStr = "{{ str_replace('/', '-', $order['order_id'] ?? '') }}";
            if (!orderIdStr) return;

            const pollInterval = setInterval(async () => {
                if (this.isPaid) {
                    clearInterval(pollInterval);
                    return;
                }
                try {
                    const response = await fetch('/checkout/check-status/' + orderIdStr);
                    const data = await response.json();
                    if (data && data.is_paid) {
                        this.isPaid = true;
                        clearInterval(pollInterval);
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                } catch (err) {
                    console.error('Auto polling status error:', err);
                }
            }, 3000);
        },

        initTimer() {
            this.updateTimer();
            setInterval(() => this.updateTimer(), 1000);
            this.startAutoPolling();
        },
        copyText(text, type) {
            navigator.clipboard.writeText(text);
            if (type === 'va') {
                this.copiedVa = true;
                setTimeout(() => this.copiedVa = false, 2500);
            } else if (type === 'invoice') {
                this.copiedInvoice = true;
                setTimeout(() => this.copiedInvoice = false, 2500);
            }
        }
    }
}
</script>

<div 
    x-data="checkoutSuccessApp()" 
    x-init="initTimer()"
    class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12"
>

    <!-- MAIN WRAPPER -->
    <div class="space-y-6">

        <!-- 1. HEADER CARD (CLEAN SOLID CARD, NO GRADIENTS) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs text-center space-y-4">
            
            <!-- Status Icon Circle -->
            <template x-if="isPaid">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto border border-emerald-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </template>
            <template x-if="!isPaid && !isExpired">
                <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mx-auto border border-amber-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </template>
            <template x-if="!isPaid && isExpired">
                <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto border border-red-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </template>

            <!-- Titles & Badges -->
            <div class="space-y-1.5">
                <template x-if="isPaid">
                    <div>
                        <span class="inline-block bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full mb-2">
                            Pesanan Berhasil Dibuat (Sedang Dikemas)
                        </span>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                            Pesanan Siap Dikemas oleh Penjual
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto mt-1 leading-relaxed">
                            {{ $isCod ? 'Pesanan COD berhasil dikonfirmasi. Pembayaran dilakukan secara tunai saat kurir mengantar barang.' : 'Pembayaran Anda telah terverifikasi. Pesanan sedang diproses oleh tim penjual.' }}
                        </p>
                    </div>
                </template>

                <template x-if="!isPaid && !isExpired">
                    <div>
                        <span class="inline-block bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold px-3 py-1 rounded-full mb-2">
                            Menunggu Pembayaran
                        </span>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                            Selesaikan Pembayaran Anda
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto mt-1 leading-relaxed">
                            Lakukan pembayaran sesuai nominal sebelum batas waktu berakhir agar pesanan langsung diproses ke tahap pengemasan.
                        </p>

                        <!-- Countdown Timer -->
                        <div class="pt-3 flex items-center justify-center gap-2 text-xs font-bold text-slate-700">
                            <span class="text-slate-500 font-semibold">Batas Waktu Pembayaran:</span>
                            <div class="flex items-center gap-1 font-mono text-xs sm:text-sm bg-slate-100 border border-slate-200 px-3 py-1 rounded-lg text-slate-900">
                                <span x-text="String(hours).padStart(2, '0')">23</span> :
                                <span x-text="String(minutes).padStart(2, '0')">59</span> :
                                <span x-text="String(seconds).padStart(2, '0')">59</span>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="!isPaid && isExpired">
                    <div>
                        <span class="inline-block bg-red-50 border border-red-200 text-red-800 text-xs font-bold px-3 py-1 rounded-full mb-2">
                            Pembayaran Kedaluwarsa
                        </span>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                            Waktu Pembayaran Habis
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto mt-1 leading-relaxed">
                            Mohon maaf, batas waktu pembayaran untuk pesanan ini telah berakhir. Pesanan Anda akan dibatalkan secara otomatis oleh sistem.
                        </p>
                        
                        <div class="mt-4">
                            <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm px-6 py-2.5 rounded-xl transition-colors">
                                Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Grand Total Bill Box -->
            <div class="pt-2">
                <div class="inline-block bg-slate-50 border border-slate-200 rounded-xl px-6 py-3 text-center">
                    <div class="text-xs text-slate-500 font-semibold uppercase">Total Tagihan Pembayaran</div>
                    <div class="text-xl sm:text-2xl font-bold text-blue-600 mt-0.5">
                        Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>

        </div>

        <!-- 2. PAYMENT INSTRUCTIONS CARD (NO GRADIENT, CLEAN SOLID CARD) -->
        <template x-if="!isPaid && !isExpired">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900">Petunjuk Pembayaran</h2>
                            <p class="text-[11px] text-slate-500 font-medium">Metode: <span class="uppercase font-bold text-slate-800">{{ str_replace('_', ' ', $payMethod) }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- A. QRIS PAYMENT -->
                @if($payMethod === 'qris')
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 text-center space-y-4">
                        <div class="text-xs font-bold text-slate-800">
                            Barcode QRIS Instant DOKU (Standar Indonesia)
                        </div>

                        <!-- Barcode Container -->
                        <div class="bg-white p-4 rounded-xl border border-slate-200 inline-block shadow-xs">
                            <img 
                                src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($order['payment_code'] ?? '00020101021126580014ID.GO.QRIS.WWW01189360091100000000005204581253033605802ID5911TokoOnline6007JAKARTA6304A1B2') }}" 
                                alt="QRIS Barcode Toko Online" 
                                class="w-48 h-48 sm:w-56 sm:h-56 mx-auto rounded"
                            >
                            <p class="text-[11px] font-semibold text-slate-600 mt-2">Dukungan: BCA Mobile, Livin by Mandiri, BRImo, BNI, GoPay, ShopeePay, OVO, DANA, LinkAja</p>
                        </div>

                        <p class="text-xs text-slate-600 max-w-sm mx-auto">
                            Simpan atau tangkap layar (screenshot) QRIS di atas, buka aplikasi m-Banking / E-Wallet pilihan Anda, lalu pilih <strong>Scan QRIS</strong>.
                        </p>
                    </div>

                <!-- B. VIRTUAL ACCOUNT BANK (BCA, MANDIRI, BRI, BNI, PERMATA, CIMB, DANAMON, BSI) -->
                @elseif(str_contains($payMethod, '_va') || in_array($payMethod, ['bca', 'mandiri', 'bri', 'bni', 'permata', 'cimb', 'danamon', 'bsi']))
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 sm:p-5 space-y-3">
                        <div class="text-xs font-bold text-slate-800">
                            Nomor Virtual Account DOKU {{ strtoupper(str_replace(['_va', '_'], ' ', $payMethod)) }}
                        </div>

                        <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
                            <div>
                                <div class="text-[11px] text-slate-500 font-semibold">Nomor Virtual Account:</div>
                                <div class="font-bold text-slate-900 font-mono text-lg sm:text-xl tracking-wide mt-0.5">
                                    {{ $order['payment_code'] ?? '88001' . rand(100000, 999999) }}
                                </div>
                            </div>

                            <button 
                                type="button" 
                                @click="copyText('{{ $order['payment_code'] ?? '' }}', 'va')"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg transition-colors cursor-pointer shrink-0"
                            >
                                <span x-text="copiedVa ? 'Nomor VA Disalin!' : 'Salin Nomor VA'"></span>
                            </button>
                        </div>

                        <div class="text-xs text-slate-600 space-y-1 pt-1">
                            <p>1. Buka aplikasi m-Banking atau ATM <strong>{{ strtoupper(str_replace(['_va', '_'], ' ', $payMethod)) }}</strong> Anda.</p>
                            <p>2. Pilih menu <strong>Transfer ➔ Virtual Account / Bayar Tagihan</strong>.</p>
                            <p>3. Masukkan kode VA <strong>{{ $order['payment_code'] ?? 'di atas' }}</strong> dan konfirmasi nominal Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}.</p>
                        </div>
                    </div>

                <!-- C. GERAI RITEL (ALFAMART / INDOMARET) -->
                @elseif(in_array($payMethod, ['alfamart', 'indomaret']))
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 sm:p-5 space-y-3">
                        <div class="text-xs font-bold text-slate-800">
                            Kode Pembayaran Kasir {{ strtoupper($payMethod) }}
                        </div>

                        <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
                            <div>
                                <div class="text-[11px] text-slate-500 font-semibold">Kode Bayar Kasir:</div>
                                <div class="font-bold text-slate-900 font-mono text-lg sm:text-xl tracking-wide mt-0.5">
                                    {{ $order['payment_code'] ?? 'DOKU-' . strtoupper($payMethod) . '-88991' }}
                                </div>
                            </div>

                            <button 
                                type="button" 
                                @click="copyText('{{ $order['payment_code'] ?? '' }}', 'va')"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg transition-colors cursor-pointer shrink-0"
                            >
                                <span x-text="copiedVa ? 'Kode Disalin!' : 'Salin Kode Bayar'"></span>
                            </button>
                        </div>

                        <div class="text-xs text-slate-600 space-y-1 pt-1">
                            <p>1. Datangi kasir gerai <strong>{{ strtoupper($payMethod) }}</strong> terdekat.</p>
                            <p>2. Beritahukan kepada kasir untuk melakukan pembayaran tagihan <strong>DOKU Merchant</strong>.</p>
                            <p>3. Tunjukkan Kode Bayar di atas kepada kasir dan selesaikan pembayaran tunai sebesar Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}.</p>
                        </div>
                    </div>

                <!-- D. E-WALLET & PAYLATER (OVO, SHOPEEPAY, DANA, LINKAJA, KREDIVO, AKULAKU, INDODANA, CREDIT CARD) -->
                @else
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 sm:p-5 space-y-3">
                        <div class="text-xs font-bold text-slate-800">
                            Instruksi Pembayaran {{ strtoupper(str_replace('_', ' ', $payMethod)) }}
                        </div>

                        <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
                            <div>
                                <div class="text-[11px] text-slate-500 font-semibold">Kode Referensi Transaksi:</div>
                                <div class="font-bold text-slate-900 font-mono text-lg sm:text-xl tracking-wide mt-0.5">
                                    {{ $order['payment_code'] ?? 'DOKU-' . strtoupper($payMethod) . '-99201' }}
                                </div>
                            </div>

                            <button 
                                type="button" 
                                @click="copyText('{{ $order['payment_code'] ?? '' }}', 'va')"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg transition-colors cursor-pointer shrink-0"
                            >
                                <span x-text="copiedVa ? 'Kode Disalin!' : 'Salin Kode Ref'"></span>
                            </button>
                        </div>

                        <div class="text-xs text-slate-600 space-y-1 pt-1">
                            <p>1. Buka aplikasi <strong>{{ strtoupper(str_replace('_', ' ', $payMethod)) }}</strong> Anda.</p>
                            <p>2. Konfirmasi notifikasi permintaan tagihan sebesar Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}.</p>
                            <p>3. Selesaikan transaksi & sistem akan memverifikasi secara instan.</p>
                        </div>
                    </div>
                @endif

            </div>
        </template>

        <!-- 3. INVOICE & SHIPPING DETAILS CARD -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs text-left text-xs sm:text-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Rincian Invoice &amp; Pengiriman</span>
                </h3>

                <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-blue-700 text-xs bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded">
                        {{ $order['order_id'] ?? 'INV/20260813/TK/123456' }}
                    </span>
                    <button 
                        type="button" 
                        @click="copyText('{{ $order['order_id'] ?? '' }}', 'invoice')"
                        class="text-[11px] font-semibold text-slate-600 hover:text-blue-700 border border-slate-200 px-2 py-0.5 rounded transition-colors cursor-pointer"
                    >
                        <span x-text="copiedInvoice ? 'Disalin!' : 'Salin'"></span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="space-y-1">
                    <div class="text-slate-400 font-medium">Penerima &amp; Alamat</div>
                    <div class="font-bold text-slate-900">{{ $order['user_name'] ?? 'Budi Santoso' }}</div>
                    <div class="text-slate-600">{{ $order['user_phone'] ?? '081234567890' }} • {{ $order['user_email'] ?? 'budi@gmail.com' }}</div>
                </div>

                <div class="space-y-1">
                    <div class="text-slate-400 font-medium">Ekspedisi Pengiriman</div>
                    <div class="font-bold text-slate-900">{{ $order['courier_name'] ?? 'JNE Express (REG)' }}</div>
                    <div class="text-slate-600">Ongkir: Rp {{ number_format($order['shipping_cost'] ?? 15000, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- 4. PRODUCTS LIST -->
        @if(isset($order['items']) && count($order['items']) > 0)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs text-left space-y-3">
                <h3 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Daftar Produk Pesanan ({{ count($order['items']) }})</span>
                </h3>

                <div class="divide-y divide-slate-100">
                    @foreach($order['items'] as $item)
                        <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-12 h-12 object-cover rounded-lg border border-slate-200 shrink-0">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-1">{{ $item['name'] }}</h4>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $item['qty'] }} barang x Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="font-bold text-slate-900 text-xs sm:text-sm shrink-0">
                                Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 5. ACTION BUTTONS (DOKU PAYMENT GATEWAY READY NAVIGATION) -->
        <div class="pt-4">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a 
                    href="{{ route('orders') }}" 
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm px-6 py-3.5 rounded-xl transition-all shadow-xs text-center flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>Lihat Status Pesanan Saya</span>
                </a>

                <a 
                    href="{{ route('home') }}" 
                    class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs sm:text-sm px-6 py-3.5 rounded-xl transition-colors text-center border border-slate-200 flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Kembali Ke Beranda</span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
