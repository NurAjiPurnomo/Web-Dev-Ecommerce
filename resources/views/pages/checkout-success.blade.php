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

    $paymentLogos = [
        'bca' => 'assets/Bca.png',
        'bca_va' => 'assets/Bca.png',
        'mandiri' => 'assets/Mandiri.png',
        'mandiri_va' => 'assets/Mandiri.png',
        'bni' => 'assets/bni.svg',
        'bni_va' => 'assets/bni.svg',
        'bri' => 'assets/bri.svg',
        'bri_va' => 'assets/bri.svg',
        'cimb' => 'assets/cimb.svg',
        'cimb_va' => 'assets/cimb.svg',
        'permata' => 'assets/permata.svg',
        'permata_va' => 'assets/permata.svg',
        'danamon' => 'assets/danamon.svg',
        'danamon_va' => 'assets/danamon.svg',
        'bsi' => 'assets/bsi.svg',
        'bsi_va' => 'assets/bsi.svg',
        'qris' => 'assets/Qris.png',
        'ovo' => 'assets/ovo.svg',
        'dana' => 'assets/dana.svg',
        'shopeepay' => 'assets/shopeepay.svg',
        'linkaja' => 'assets/linkaja.svg',
        'alfamart' => 'assets/alfamart.svg',
        'indomaret' => 'assets/indomaret.svg',
        'cod' => 'assets/cod.svg',
    ];
    $methodLogo = $paymentLogos[$payMethod] ?? null;
@endphp

<script>
function checkoutSuccessApp() {
    return {
        isSubmitting: false,
        isPaid: @json(!$isUnpaid),
        copiedVa: false,
        copiedInvoice: false,
        copiedNominal: false,
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
            } else if (type === 'nominal') {
                this.copiedNominal = true;
                setTimeout(() => this.copiedNominal = false, 2500);
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

        <!-- 1. STATUS & TAGIHAN CARD (CLEAN PROFESSIONAL E-COMMERCE LAYOUT) -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
            
            <!-- A. WAITING PAYMENT STATE -->
            <template x-if="!isPaid && !isExpired">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="space-y-1 text-left">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span class="text-xs font-bold text-amber-700 tracking-wider uppercase">Menunggu Pembayaran</span>
                            </div>
                            <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                                Selesaikan Pembayaran
                            </h1>
                            <p class="text-xs text-slate-500 max-w-lg leading-relaxed">
                                Lakukan transfer sebelum batas waktu berakhir agar pesanan dapat langsung diverifikasi dan diproses.
                            </p>
                        </div>

                        <!-- Sisa Waktu Countdown -->
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2.5 sm:text-right shrink-0">
                            <span class="text-xs text-slate-500 block">Sisa Waktu Pembayaran</span>
                            <div class="font-sans tabular-nums text-base sm:text-lg font-bold text-slate-900 tracking-tight mt-0.5">
                                <span x-text="String(hours).padStart(2, '0')">23</span><span class="text-slate-400 mx-1">:</span><span x-text="String(minutes).padStart(2, '0')">50</span><span class="text-slate-400 mx-1">:</span><span x-text="String(seconds).padStart(2, '0')">57</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- B. PAID STATE -->
            <template x-if="isPaid">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="space-y-1 text-left">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-bold text-emerald-700 tracking-wider uppercase">
                                    {{ $isCod ? 'Pesanan Terkonfirmasi' : 'Pembayaran Berhasil' }}
                                </span>
                            </div>
                            <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                                Pesanan Sedang Diproses
                            </h1>
                            <p class="text-xs text-slate-500 max-w-lg leading-relaxed">
                                {{ $isCod ? 'Pesanan COD berhasil dikonfirmasi. Pembayaran dilakukan secara tunai saat kurir tiba.' : 'Pembayaran Anda telah terverifikasi. Pesanan sedang disiapkan oleh penjual.' }}
                            </p>
                        </div>
                        <div class="inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-1.5 rounded-lg font-semibold text-xs shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Lunas</span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- C. EXPIRED STATE -->
            <template x-if="!isPaid && isExpired">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="space-y-1 text-left">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                <span class="text-xs font-bold text-rose-700 tracking-wider uppercase">Waktu Pembayaran Habis</span>
                            </div>
                            <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                                Transaksi Kedaluwarsa
                            </h1>
                            <p class="text-xs text-slate-500 max-w-lg leading-relaxed">
                                Batas waktu pembayaran untuk transaksi ini telah berakhir. Pesanan dibatalkan secara otomatis oleh sistem.
                            </p>
                        </div>
                        <div class="shrink-0">
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors">
                                Belanja Lagi
                            </a>
                        </div>
                    </div>
                </div>
            </template>

            <!-- BOTTOM ROW: TOTAL TAGIHAN & SALIN NOMINAL -->
            <div class="bg-slate-50/80 border-t border-slate-100 px-5 py-3.5 sm:px-6 sm:py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-left">
                <div>
                    <span class="text-xs text-slate-500 block">Total Tagihan</span>
                    <div class="text-xl sm:text-2xl font-bold text-blue-600 tracking-tight mt-0.5">
                        Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <template x-if="!isPaid && !isExpired">
                    <button 
                        type="button" 
                        @click="copyText('{{ $order['total_amount'] ?? 0 }}', 'nominal')"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 text-xs font-semibold transition-all shadow-2xs cursor-pointer shrink-0"
                        title="Salin Jumlah Tagihan"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="copiedNominal ? 'Tersalin!' : 'Salin'">Salin</span>
                    </button>
                </template>
            </div>

        </div>

        <!-- 2. PAYMENT INSTRUCTIONS CARD -->
        <template x-if="!isPaid && !isExpired">
            <div class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">

                <!-- Header with Real Brand Logo -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        @if(!empty($methodLogo))
                            <div class="h-10 px-3 bg-white border border-slate-200/90 rounded-xl flex items-center justify-center shrink-0 shadow-2xs">
                                <img src="{{ asset($methodLogo) }}" alt="{{ strtoupper(str_replace(['_va', '_'], ' ', $payMethod)) }}" class="h-5 w-auto max-w-[80px] object-contain">
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="text-left">
                            <h2 class="text-base font-bold text-slate-900">Petunjuk Pembayaran</h2>
                            <p class="text-xs text-slate-500">Metode: <span class="font-semibold text-slate-700 uppercase">{{ str_replace(['_va', '_'], ' ', $payMethod) }}</span></p>
                        </div>
                    </div>
                </div>

                @php
                    $payCode = $order['payment_code'] ?? '';
                    $isUrl = !empty($payCode) && (str_starts_with($payCode, 'http://') || str_starts_with($payCode, 'https://'));
                @endphp

                <!-- A. OVO E-WALLET PAYMENT (EXACT MATCH TO USER SCREENSHOT 1) -->
                @if($payMethod === 'ovo')
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-4 shadow-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="text-sm sm:text-base font-bold text-slate-800">e-Wallet</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-purple-700 text-sm">OVO</span>
                                <img src="{{ asset('assets/ovo.svg') }}" alt="OVO" class="h-6 w-auto object-contain" onError="this.style.display='none'">
                            </div>
                        </div>

                        <div class="space-y-4 pt-1">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Please Fill this Form</h3>

                            <div class="space-y-1.5 text-left">
                                <label class="block text-xs font-bold text-slate-800">
                                    Phone Number<span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    value="{{ $order['user_phone'] ?? ($order['recipient_phone'] ?? '') }}" 
                                    placeholder="Nomor HP terdaftar OVO"
                                    class="w-full bg-white border border-slate-300 focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-xl px-4 py-2.5 text-xs sm:text-sm text-slate-800 outline-none transition-all"
                                >
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 text-left">
                                <div class="text-xs font-bold text-slate-900">Petunjuk Pembayaran OVO</div>
                                <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1.5 font-medium leading-relaxed">
                                    <li>Pastikan Anda memiliki akun OVO yang aktif.</li>
                                    <li>Nominal yang harus dibayar: <strong>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</strong>.</li>
                                    <li>Klik tombol <strong>"Bayar Sekarang via OVO"</strong> di bawah.</li>
                                    <li>Selesaikan otorisasi pembayaran di aplikasi atau gerbang resmi OVO.</li>
                                </ol>
                            </div>

                            <div class="pt-2">
                                @if($isUrl)
                                    <a 
                                        href="{{ $payCode }}" 
                                        target="_blank"
                                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs sm:text-sm py-3.5 px-6 rounded-xl transition-all shadow-md text-center block cursor-pointer"
                                    >
                                        Bayar Sekarang via OVO
                                    </a>
                                @else
                                    <button 
                                        type="button" 
                                        @click="alert('Permintaan pembayaran OVO telah diproses. Silakan periksa aplikasi OVO Anda.')"
                                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs sm:text-sm py-3.5 px-6 rounded-xl transition-all shadow-md text-center block cursor-pointer"
                                    >
                                        Bayar Sekarang via OVO
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                <!-- B. OTHER E-WALLETS (SHOPEEPAY, DANA, LINKAJA) -->
                @elseif(in_array($payMethod, ['shopeepay', 'dana', 'linkaja']))
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-4 shadow-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="text-sm sm:text-base font-bold text-slate-800">e-Wallet</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800 text-sm uppercase">{{ $payMethod }}</span>
                                <img src="{{ asset('assets/' . $payMethod . '.svg') }}" alt="{{ $payMethod }}" class="h-6 w-auto object-contain" onError="this.style.display='none'">
                            </div>
                        </div>

                        <div class="space-y-4 pt-1">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Form Pembayaran {{ strtoupper($payMethod) }}</h3>

                            <div class="space-y-1.5 text-left">
                                <label class="block text-xs font-bold text-slate-800">
                                    Nomor Telepon Akun {{ strtoupper($payMethod) }}<span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    value="{{ $order['user_phone'] ?? ($order['recipient_phone'] ?? '') }}" 
                                    placeholder="Masukkan nomor HP {{ strtoupper($payMethod) }}"
                                    class="w-full bg-white border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 rounded-xl px-4 py-2.5 text-xs sm:text-sm text-slate-800 outline-none transition-all"
                                >
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 text-left">
                                <div class="text-xs font-bold text-slate-900">Cara Pembayaran</div>
                                <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1.5 font-medium leading-relaxed">
                                    <li>Buka aplikasi {{ strtoupper($payMethod) }} pada ponsel Anda.</li>
                                    <li>Pastikan nominal tagihan sesuai: <strong>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</strong>.</li>
                                    <li>Masukkan nomor telepon {{ strtoupper($payMethod) }} Anda yang terdaftar.</li>
                                    <li>Klik tombol <strong>"Bayar Sekarang via {{ strtoupper($payMethod) }}"</strong> di bawah.</li>
                                    <li>Selesaikan otorisasi &amp; pembayaran di aplikasi {{ strtoupper($payMethod) }}.</li>
                                </ol>
                            </div>

                            <div class="pt-2">
                                @if($isUrl)
                                    <a 
                                        href="{{ $payCode }}" 
                                        target="_blank"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm py-3.5 px-6 rounded-xl transition-all shadow-md text-center block cursor-pointer"
                                    >
                                        Bayar Sekarang via {{ strtoupper($payMethod) }}
                                    </a>
                                @else
                                    <button 
                                        type="button" 
                                        @click="alert('Permintaan tagihan telah dikirim ke akun {{ strtoupper($payMethod) }} Anda. Silakan buka aplikasi {{ strtoupper($payMethod) }} untuk konfirmasi.')"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm py-3.5 px-6 rounded-xl transition-all shadow-md text-center block cursor-pointer"
                                    >
                                        Bayar Sekarang via {{ strtoupper($payMethod) }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                <!-- C. QRIS PAYMENT -->
                @elseif($payMethod === 'qris')
                    @php
                        $rawQrData = (!empty($payCode) && !str_starts_with($payCode, 'http')) 
                            ? $payCode 
                            : app(\App\Http\Controllers\CheckoutController::class)->generateQrisEmvCo(
                                'TOKO ONLINE', 
                                'JAKARTA', 
                                $order['order_id'] ?? ($order['invoice_number'] ?? 'INV-ORDER'), 
                                (int) ($order['total_amount'] ?? 10000)
                            );
                        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=10&data=' . urlencode($rawQrData);
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 text-center space-y-5 shadow-xs">
                        <div class="flex flex-col items-center justify-center space-y-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                QRIS Standar Bank Indonesia &amp; ASPI
                            </span>
                            <h3 class="text-base sm:text-lg font-extrabold text-slate-900 pt-1">Scan QRIS Untuk Membayar</h3>
                            <p class="text-xs text-slate-500 max-w-md">Buka aplikasi m-Banking (BCA, Mandiri, BRI, BNI, dll) atau E-Wallet (GoPay, OVO, ShopeePay, DANA, LinkAja) lalu pilih <strong>Scan QRIS</strong>.</p>
                        </div>

                        <!-- Barcode Container QRIS Asli Toko Online -->
                        <div class="bg-gradient-to-b from-slate-50 to-white p-5 rounded-2xl border-2 border-slate-200 inline-block shadow-md max-w-sm mx-auto">
                            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-inner inline-block">
                                <img 
                                    src="{{ $qrImageUrl }}" 
                                    alt="QRIS Toko Online" 
                                    class="w-56 h-56 sm:w-64 sm:h-64 mx-auto rounded-lg object-contain"
                                >
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between text-left">
                                <div>
                                    <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Total Tagihan</div>
                                    <div class="font-extrabold text-sm sm:text-base text-rose-600">Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <a 
                                    href="{{ $qrImageUrl }}" 
                                    download="QRIS-{{ str_replace(['/', ' '], '-', $order['order_id'] ?? 'INVOICE') }}.png"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer border border-slate-300"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh QR</span>
                                </a>
                            </div>

                            <div class="mt-3 bg-slate-100/70 rounded-lg p-2 text-[10px] text-slate-500 font-medium leading-relaxed">
                                Dukungan: BCA Mobile, Livin Mandiri, BRImo, BNI, Permata, OCTO, GoPay, OVO, ShopeePay, DANA, LinkAja.
                            </div>
                        </div>

                        <div class="bg-blue-50/60 border border-blue-200 rounded-xl p-3.5 max-w-md mx-auto text-left flex items-start gap-3 text-xs text-blue-900">
                            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <span class="font-bold">Verifikasi Otomatis Real-time:</span>
                                <p class="text-[11px] text-slate-600 mt-0.5">Sistem akan mendeteksi pembayaran Anda secara otomatis. Halaman ini akan berubah seketika setelah Anda sukses membayar.</p>
                            </div>
                        </div>
                    </div>

                <!-- D. VIRTUAL ACCOUNT BANK (BCA, MANDIRI, BRI, BNI, PERMATA, CIMB, DANAMON, BSI) -->
                @elseif(str_contains($payMethod, '_va') || in_array($payMethod, ['bca', 'mandiri', 'bri', 'bni', 'permata', 'cimb', 'danamon', 'bsi']))
                    @php
                        $displayVaNumber = (!empty($payCode) && !str_starts_with($payCode, 'http')) ? $payCode : '';
                        $bankName = strtoupper(str_replace(['_va', '_'], ' ', $payMethod));
                        $bankKey = str_replace('_va', '', $payMethod);
                    @endphp
                    
                    <div class="space-y-4" x-data="{ openTab: 1 }">
                        <!-- VA Number Box -->
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="space-y-0.5 text-left">
                                <span class="text-xs text-slate-500 block">
                                    Nomor Virtual Account {{ $bankName }}
                                </span>
                                <div class="text-lg sm:text-xl font-bold text-slate-900 tracking-wider font-sans tabular-nums select-all">
                                    {{ $displayVaNumber ?: 'Memuat dari Gateway...' }}
                                </div>
                            </div>

                            @if(!empty($displayVaNumber))
                            <button 
                                type="button" 
                                @click="copyText('{{ $displayVaNumber }}', 'va')"
                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 text-xs font-semibold transition-all shadow-2xs cursor-pointer shrink-0"
                                title="Salin Nomor Virtual Account"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="copiedVa ? 'Tersalin!' : 'Salin'">Salin</span>
                            </button>
                            @endif
                        </div>

                        <!-- Real E-Commerce Accordion Guide (Tokopedia / Shopee style) -->
                        <div class="space-y-2 text-left pt-1">
                            <div class="text-xs font-bold text-slate-700 tracking-wider uppercase pt-1 pb-0.5">
                                Cara Pembayaran Transfer Virtual Account
                            </div>

                            @if($bankKey === 'bca')
                                <!-- BCA Option 1: m-BCA (BCA Mobile) -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 1 ? null : 1"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">m-BCA (BCA Mobile)</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 1" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Buka aplikasi <strong>BCA Mobile</strong>, pilih menu <strong>m-BCA</strong> lalu masukkan Kode Akses Anda.</li>
                                            <li>Pilih menu <strong>m-Transfer</strong> &gt; <strong>BCA Virtual Account</strong>.</li>
                                            <li>Masukkan Nomor Virtual Account <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong> lalu tekan <strong>Send</strong>.</li>
                                            <li>Pastikan nama tagihan dan nominal pembayaran sudah sesuai sebesar <strong>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</strong>, lalu tekan <strong>OK</strong>.</li>
                                            <li>Masukkan PIN m-BCA Anda dan simpan bukti transaksi. Pembayaran akan diverifikasi otomatis.</li>
                                        </ol>
                                    </div>
                                </div>

                                <!-- BCA Option 2: KlikBCA -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 2 ? null : 2"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">KlikBCA (Internet Banking)</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 2" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Buka situs <strong>KlikBCA Individual</strong> dan login dengan User ID &amp; PIN Anda.</li>
                                            <li>Pilih menu <strong>Transfer Dana</strong> &gt; <strong>Transfer ke BCA Virtual Account</strong>.</li>
                                            <li>Masukkan nomor Virtual Account <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong> dan klik <strong>Lanjutkan</strong>.</li>
                                            <li>Pastikan detail tagihan sudah tepat, masukkan Respon KeyBCA Appli 1, lalu klik <strong>Kirim</strong>.</li>
                                        </ol>
                                    </div>
                                </div>

                                <!-- BCA Option 3: ATM BCA -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 3 ? null : 3"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">ATM BCA</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 3" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Masukkan kartu ATM BCA dan PIN Anda di mesin ATM.</li>
                                            <li>Pilih menu <strong>Transaksi Lainnya</strong> &gt; <strong>Transfer</strong> &gt; <strong>ke Rekening BCA Virtual Account</strong>.</li>
                                            <li>Masukkan nomor Virtual Account <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong> lalu tekan <strong>Benar</strong>.</li>
                                            <li>Periksa jumlah pembayaran <strong>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</strong>, tekan <strong>Ya</strong> untuk menyelesaikan transaksi.</li>
                                        </ol>
                                    </div>
                                </div>

                            @elseif($bankKey === 'mandiri')
                                <!-- Mandiri Option 1: Livin' by Mandiri -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 1 ? null : 1"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">Livin' by Mandiri (Kuning)</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 1" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Login ke aplikasi <strong>Livin' by Mandiri</strong>, pilih menu <strong>Bayar</strong>.</li>
                                            <li>Ketik dan cari penyedia jasa atau pilih <strong>Virtual Account</strong>.</li>
                                            <li>Masukkan Nomor Virtual Account <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong>.</li>
                                            <li>Pastikan nominal tagihan sesuai (<strong>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</strong>) dan lanjutkan pembayaran.</li>
                                        </ol>
                                    </div>
                                </div>

                                <!-- Mandiri Option 2: ATM Mandiri -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 2 ? null : 2"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">ATM Mandiri</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 2" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Masukkan kartu ATM dan PIN Mandiri Anda.</li>
                                            <li>Pilih menu <strong>Bayar/Beli</strong> &gt; <strong>Multi Payment</strong>.</li>
                                            <li>Masukkan nomor Virtual Account <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong>.</li>
                                            <li>Konfirmasi pembayaran dan simpan struk transaksi.</li>
                                        </ol>
                                    </div>
                                </div>

                            @elseif($bankKey === 'bri')
                                <!-- BRI Option 1: BRImo -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 1 ? null : 1"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">BRImo (Mobile Banking)</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 1" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Buka aplikasi <strong>BRImo</strong> dan login ke akun Anda.</li>
                                            <li>Pilih menu <strong>Tagihan</strong> &gt; <strong>BRIVA</strong>.</li>
                                            <li>Pilih <strong>Pembayaran Baru</strong> dan masukkan nomor BRIVA: <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong>.</li>
                                            <li>Periksa jumlah tagihan (<strong>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</strong>) dan masukkan PIN BRImo.</li>
                                        </ol>
                                    </div>
                                </div>

                                <!-- BRI Option 2: ATM BRI -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 2 ? null : 2"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">ATM BRI</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 2" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Masukkan kartu ATM BRI dan PIN Anda.</li>
                                            <li>Pilih menu <strong>Transaksi Lain</strong> &gt; <strong>Pembayaran</strong> &gt; <strong>Lainnya</strong> &gt; <strong>BRIVA</strong>.</li>
                                            <li>Masukkan nomor BRIVA <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong> dan tekan <strong>Benar</strong>.</li>
                                            <li>Selesaikan pembayaran dan simpan struk.</li>
                                        </ol>
                                    </div>
                                </div>

                            @elseif($bankKey === 'bni')
                                <!-- BNI Option 1: BNI Mobile Banking -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 1 ? null : 1"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">BNI Mobile Banking</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 1" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Buka dan login ke aplikasi <strong>BNI Mobile Banking</strong>.</li>
                                            <li>Pilih menu <strong>Pembayaran</strong> &gt; <strong>Virtual Account Billing</strong>.</li>
                                            <li>Pilih <strong>Input Baru</strong> dan masukkan nomor Virtual Account: <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong>.</li>
                                            <li>Konfirmasi tagihan (<strong>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</strong>) dan masukkan Password Transaksi.</li>
                                        </ol>
                                    </div>
                                </div>

                                <!-- BNI Option 2: ATM BNI -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 2 ? null : 2"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">ATM BNI</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 2" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Masukkan kartu ATM BNI dan PIN Anda.</li>
                                            <li>Pilih <strong>Menu Lain</strong> &gt; <strong>Transfer</strong> &gt; <strong>Virtual Account Billing</strong>.</li>
                                            <li>Masukkan nomor VA <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong> dan selesaikan pembayaran.</li>
                                        </ol>
                                    </div>
                                </div>

                            @else
                                <!-- Default Other Banks -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                    <button 
                                        type="button" 
                                        @click="openTab = openTab === 1 ? null : 1"
                                        class="w-full px-4 py-3 bg-white hover:bg-slate-50 flex items-center justify-between text-left transition-colors cursor-pointer"
                                    >
                                        <span class="text-xs sm:text-sm font-bold text-slate-800">Mobile Banking / ATM {{ $bankName }}</span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openTab === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openTab === 1" class="px-4 pb-4 pt-1 border-t border-slate-100 text-xs text-slate-600 space-y-2 leading-relaxed bg-slate-50/40">
                                        <ol class="list-decimal list-inside space-y-1.5 pt-1">
                                            <li>Buka aplikasi m-Banking atau kunjungi ATM <strong>{{ $bankName }}</strong> terdekat.</li>
                                            <li>Pilih menu <strong>Transfer</strong> &gt; <strong>Virtual Account / Bayar Tagihan</strong>.</li>
                                            <li>Masukkan nomor Virtual Account <strong class="text-blue-600 select-all font-sans">{{ $displayVaNumber }}</strong>.</li>
                                            <li>Pastikan nominal pembayaran tepat <strong>Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}</strong> dan selesaikan transaksi.</li>
                                        </ol>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                <!-- E. GERAI RITEL (ALFAMART / INDOMARET) -->
                @elseif(in_array($payMethod, ['alfamart', 'indomaret']))
                    @php
                        $displayRetailCode = (!empty($payCode) && !str_starts_with($payCode, 'http')) ? $payCode : '';
                    @endphp
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 sm:p-5 space-y-3">
                        <div class="text-xs font-bold text-slate-800">
                            Pembayaran Kasir {{ strtoupper($payMethod) }}
                        </div>

                        @if(!empty($displayRetailCode))
                        <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
                            <div>
                                <div class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider">Kode Bayar Kasir:</div>
                                <div class="font-extrabold text-slate-900 font-sans tabular-nums text-xl sm:text-2xl tracking-wider mt-0.5 select-all">
                                    {{ $displayRetailCode }}
                                </div>
                            </div>

                            <button 
                                type="button" 
                                @click="copyText('{{ $displayRetailCode }}', 'va')"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg transition-colors cursor-pointer shrink-0"
                            >
                                <span x-text="copiedVa ? 'Kode Disalin!' : 'Salin Kode Bayar'"></span>
                            </button>
                        </div>
                        @endif

                        <div class="text-xs text-slate-600 space-y-1 pt-1">
                            <p>1. Datangi kasir gerai <strong>{{ strtoupper($payMethod) }}</strong> terdekat.</p>
                            <p>2. Beritahukan kepada kasir untuk melakukan pembayaran tagihan <strong>{{ strtoupper($payMethod) }} Merchant</strong>.</p>
                            <p>3. Tunjukkan Kode Bayar di atas kepada kasir dan selesaikan pembayaran tunai sebesar Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}.</p>
                        </div>
                    </div>

                <!-- F. DEFAULT / PAYLATER / CREDIT CARD -->
                @else
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 sm:p-5 space-y-3">
                        <div class="text-xs font-bold text-slate-800">
                            Instruksi Pembayaran {{ strtoupper(str_replace('_', ' ', $payMethod)) }}
                        </div>

                        <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
                            <div>
                                <div class="text-[11px] text-slate-500 font-semibold">Status Transaksi:</div>
                                <div class="font-bold text-slate-900 font-sans text-base sm:text-lg tracking-wide mt-0.5">
                                    Menunggu Pembayaran {{ strtoupper(str_replace('_', ' ', $payMethod)) }}
                                </div>
                            </div>

                            @if($isUrl)
                            <a 
                                href="{{ $payCode }}" 
                                target="_blank"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-2.5 rounded-lg transition-colors text-center shrink-0"
                            >
                                Bayar Sekarang
                            </a>
                            @endif
                        </div>

                        <div class="text-xs text-slate-600 space-y-1 pt-1">
                            <p>1. Buka aplikasi atau portal <strong>{{ strtoupper(str_replace('_', ' ', $payMethod)) }}</strong> Anda.</p>
                            <p>2. Konfirmasi notifikasi permintaan tagihan sebesar Rp {{ number_format($order['total_amount'] ?? 0, 0, ',', '.') }}.</p>
                            <p>3. Selesaikan transaksi &amp; sistem akan memverifikasi secara instan.</p>
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
                    <span class="font-sans tabular-nums font-bold text-blue-700 text-xs bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-lg">
                        {{ str_replace('/', '-', $order['order_id'] ?? ($order['invoice_number'] ?? '-')) }}
                    </span>
                    <button 
                        type="button" 
                        @click="copyText('{{ str_replace('/', '-', $order['order_id'] ?? ($order['invoice_number'] ?? '')) }}', 'invoice')"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 text-xs font-semibold transition-all shadow-2xs cursor-pointer"
                        title="Salin Nomor Invoice"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="copiedInvoice ? 'Tersalin!' : 'Salin'">Salin</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="space-y-1">
                    <div class="text-slate-400 font-medium">Penerima &amp; Alamat</div>
                    <div class="font-bold text-slate-900">{{ $order['user_name'] ?? ($order['recipient_name'] ?? 'Pelanggan') }}</div>
                    <div class="text-slate-600">{{ $order['user_phone'] ?? ($order['recipient_phone'] ?? '-') }} • {{ $order['user_email'] ?? ($order['customer_email'] ?? '-') }}</div>
                </div>

                <div class="space-y-1">
                    <div class="text-slate-400 font-medium">Ekspedisi Pengiriman</div>
                    <div class="font-bold text-slate-900">{{ $order['courier_name'] ?? ($order['courier'] ?? '-') }}</div>
                    <div class="text-slate-600">Ongkir: Rp {{ number_format($order['shipping_cost'] ?? 0, 0, ',', '.') }}</div>
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

        <!-- 5. ACTION BUTTONS (STANDARD E-COMMERCE UX FLOW) -->
        <div class="pt-4">
            <div class="flex flex-col-reverse sm:flex-row items-center justify-center gap-3">
                <a 
                    href="{{ route('home') }}" 
                    class="w-full sm:w-auto bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 font-semibold text-xs sm:text-sm px-6 py-3 rounded-xl transition-all border border-slate-200 shadow-2xs text-center flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>

                <a 
                    href="{{ route('orders', ['order_id' => $order['order_id'] ?? ($order['invoice_number'] ?? ($order['id'] ?? ''))]) }}" 
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-all shadow-xs text-center flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>Cek Status Pesanan</span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
