@extends('admin.layout')

@section('title', 'Manajemen Penjualan & Transaksi')

@section('content')
@php
    $ordersMap = [];
    foreach ($orders as $o) {
        $ordersMap[$o->id] = [
            'id'               => $o->id,
            'invoice_number'   => $o->invoice_number,
            'status'           => $o->status,
            'tracking_number'  => $o->tracking_number ?: ('JT' . date('Ymd') . rand(10000, 99999)),
            'recipient_name'   => $o->recipient_name ?: ($o->user->name ?? 'Pelanggan'),
            'recipient_phone'  => $o->recipient_phone ?: ($o->user->phone ?? '-'),
            'shipping_address' => $o->shipping_address ?: 'Alamat tidak tersedia',
            'courier'          => $o->courier ?: 'J&T Express',
            'payment_method'   => $o->payment_method ?: 'BCA Virtual Account',
            'formatted_total'  => $o->formatted_total,
            'items'            => $o->items->map(fn($i) => [
                'id'           => $i->id,
                'product_name' => $i->product_name,
                'quantity'     => $i->quantity,
                'price'        => (float)$i->price
            ])->values()->toArray()
        ];
    }

    $countSemua     = $orders->count();
    $countBelumBayar= $orders->filter(fn($o) => in_array($o->status, ['belum_bayar', 'belum_dibayar']))->count();
    $countDikemas   = $orders->filter(fn($o) => in_array($o->status, ['dikemas', 'diproses']))->count();
    $countDikirim   = $orders->where('status', 'dikirim')->count();
    $countSelesai   = $orders->where('status', 'selesai')->count();
@endphp

<div x-data="{ ordersMap: @js($ordersMap), selectedOrder: null, showDetailModal: false, showAwbPrintModal: false }" class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">Manajemen Penjualan</span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Monitoring Transaksi &amp; Shipping AWB</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-1">Pusat Manajemen Penjualan &amp; Transaksi</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Monitoring transaksi pelanggan, status pengiriman ekspedisi, dan pencetakan label Airway Bill (AWB)</p>
        </div>
    </div>

    <!-- 5 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-5 gap-3.5">
        <!-- Total Transaksi Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Transaksi</span>
                <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $countSemua }} <span class="text-xs font-semibold text-slate-400">Pesanan</span></div>
                <p class="text-[11px] text-blue-700 font-semibold">Keseluruhan</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
        </div>

        <!-- Belum Bayar Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Belum Bayar</span>
                <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $countBelumBayar }} <span class="text-xs font-semibold text-slate-400">Pesanan</span></div>
                <p class="text-[11px] text-amber-700 font-semibold">Menunggu Pembayaran</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Perlu Dikemas Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Sedang Dikemas</span>
                <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $countDikemas }} <span class="text-xs font-semibold text-slate-400">Pesanan</span></div>
                <p class="text-[11px] text-blue-700 font-semibold">Siap Diserahkan</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>

        <!-- Dalam Pengiriman Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Dalam Pengiriman</span>
                <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $countDikirim }} <span class="text-xs font-semibold text-slate-400">Resi Aktif</span></div>
                <p class="text-[11px] text-purple-700 font-semibold">Ekspedisi Kurir</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-700 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>

        <!-- Transaksi Selesai Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pesanan Selesai</span>
                <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $countSelesai }} <span class="text-xs font-semibold text-slate-400">Selesai</span></div>
                <p class="text-[11px] text-emerald-700 font-semibold">Telah Diterima</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white border border-slate-200 rounded-2xl p-2 shadow-2xs">
        <div class="flex items-center overflow-x-auto gap-1 text-xs font-semibold">
            <a href="{{ route('admin.orders', ['status' => 'semua']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $status === 'semua' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">Semua Status</a>
            <a href="{{ route('admin.orders', ['status' => 'belum_bayar']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ in_array($status, ['belum_bayar', 'belum_dibayar']) ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">Belum Bayar</a>
            <a href="{{ route('admin.orders', ['status' => 'dikemas']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ in_array($status, ['dikemas', 'diproses']) ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">Sedang Dikemas</a>
            <a href="{{ route('admin.orders', ['status' => 'dikirim']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $status === 'dikirim' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">Dalam Pengiriman</a>
            <a href="{{ route('admin.orders', ['status' => 'selesai']) }}" class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $status === 'selesai' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">Pesanan Selesai</a>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3.5">No. Invoice &amp; Waktu</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Pengiriman &amp; Pembayaran</th>
                        <th class="px-5 py-3.5">Resi Cashless (AWB)</th>
                        <th class="px-5 py-3.5">Status Pesanan</th>
                        <th class="px-5 py-3.5 text-right">Aksi &amp; Label</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($orders as $o)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-mono font-semibold text-slate-900">{{ $o->invoice_number }}</div>
                                <div class="text-[11px] text-slate-400">{{ $o->created_at->format('d M Y, H:i') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $o->user->name ?? $o->recipient_name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $o->recipient_phone ?: ($o->user->phone ?? '-') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-800">{{ $o->courier ?: 'J&T Express' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $o->payment_method ?: 'BCA Virtual Account' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $resi = $o->tracking_number ?: ('JT' . date('Ymd') . rand(10000, 99999));
                                @endphp
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-slate-100 text-slate-800 font-mono font-semibold text-xs px-2.5 py-1 rounded border border-slate-200">{{ $resi }}</span>
                                    <button type="button" @click="navigator.clipboard.writeText('{{ $resi }}'); alert('Nomor Resi Cashless {{ $resi }} berhasil disalin!')" class="text-slate-400 hover:text-slate-700 transition-colors cursor-pointer" title="Salin Resi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="text-[10px] text-emerald-700 font-bold mt-0.5">Auto Booking Active</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($o->status === 'dikemas' || $o->status === 'diproses')
                                    <span class="bg-blue-50 text-blue-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-blue-200">SEDANG DIKEMAS</span>
                                @elseif($o->status === 'dikirim')
                                    <span class="bg-purple-50 text-purple-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-purple-200">DALAM PENGIRIMAN</span>
                                @elseif($o->status === 'selesai')
                                    <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-emerald-200">SELESAI</span>
                                @elseif($o->status === 'batal')
                                    <span class="bg-red-50 text-red-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-red-200">DIBATALKAN</span>
                                @else
                                    <span class="bg-amber-50 text-amber-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-amber-200">BELUM BAYAR</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Button Cetak Label Pengiriman AWB -->
                                    <button 
                                        type="button" 
                                        @click="selectedOrder = ordersMap[{{ $o->id }}]; showAwbPrintModal = true"
                                        class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded-lg transition-colors cursor-pointer flex items-center gap-1.5"
                                        title="Cetak Shipping Label AWB Cashless"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                        <span>Label AWB</span>
                                    </button>

                                    @if($o->status === 'dikemas' || $o->status === 'diproses')
                                        <form action="{{ route('admin.orders.update', $o->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="dikirim">
                                            <button type="submit" class="text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-lg transition-colors cursor-pointer flex items-center gap-1.5" title="Konfirmasi Penyerahan Paket ke Kurir">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                                <span>Serahkan ke Kurir</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Button Detail Monitoring -->
                                    <button 
                                        type="button" 
                                        @click="selectedOrder = ordersMap[{{ $o->id }}]; showDetailModal = true"
                                        class="text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg border border-slate-300 transition-colors cursor-pointer flex items-center gap-1.5"
                                        title="Rincian Pesanan & Lacak"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Detail</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">Tidak ada transaksi ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DETAIL TRANSAKSI READ-ONLY MONITORING -->
    <div 
        x-show="showDetailModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <template x-if="selectedOrder">
            <div @click.outside="showDetailModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-semibold text-slate-900 text-base">Monitoring Rincian Transaksi</h3>
                        <p class="text-xs font-mono text-slate-500" x-text="selectedOrder.invoice_number"></p>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Resi Auto Cashless Card -->
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-800 block text-xs uppercase tracking-wider">Nomor Resi Cashless (Otomatis System):</span>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">AKTIF</span>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <span class="font-mono font-bold text-base text-blue-700" x-text="selectedOrder.tracking_number"></span>
                            <button type="button" @click="showDetailModal = false; showAwbPrintModal = true" class="text-xs font-bold text-blue-700 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                <span>Cetak Label AWB →</span>
                            </button>
                        </div>
                    </div>

                    <!-- Customer & Delivery Info -->
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                        <span class="font-bold text-slate-800 block text-xs uppercase tracking-wider">Alamat Tujuan Pengiriman:</span>
                        <p class="text-slate-900 font-bold" x-text="(selectedOrder.recipient_name || 'Pelanggan') + ' (' + (selectedOrder.recipient_phone || '-') + ')'"></p>
                        <p class="text-slate-600 leading-relaxed text-[11px]" x-text="selectedOrder.shipping_address"></p>
                    </div>

                    <!-- Items Summary -->
                    <div class="space-y-2">
                        <span class="font-bold text-slate-800 block text-xs uppercase tracking-wider">Rincian Barang Dipesan:</span>
                        <div class="space-y-1.5 max-h-36 overflow-y-auto">
                            <template x-for="item in selectedOrder.items" :key="item.id">
                                <div class="flex items-center justify-between p-2.5 bg-white border border-slate-200 rounded-xl">
                                    <span class="font-semibold text-slate-900 text-xs" x-text="item.quantity + 'x ' + item.product_name"></span>
                                    <span class="font-semibold text-slate-900 text-xs" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.price)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex justify-between items-center border-t border-slate-100">
                    <button type="button" @click="showDetailModal = false; showAwbPrintModal = true" class="px-4 py-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold hover:bg-blue-100 cursor-pointer flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>Cetak Shipping Label</span>
                    </button>
                    <button type="button" @click="showDetailModal = false" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold cursor-pointer">
                        Tutup Jendela
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- MODAL CETAK SHIPPING LABEL AIRWAY BILL (AWB CASHLESS SHOPEE-STYLE) -->
    <div 
        x-show="showAwbPrintModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
        style="display: none;"
    >
        <template x-if="selectedOrder">
            <div @click.outside="showAwbPrintModal = false" class="bg-white border border-slate-300 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl space-y-0 text-slate-900 font-sans">
                <!-- Label Header / Header Resi Ekspedisi -->
                <div class="bg-slate-900 text-white p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="bg-blue-600 text-white font-semibold text-xs px-2.5 py-1 rounded">J&T EXPRESS</span>
                        <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">CASHLESS AWB</span>
                    </div>
                    <button type="button" @click="showAwbPrintModal = false" class="text-slate-400 hover:text-white font-bold">✕</button>
                </div>

                <!-- Label Content Body (Persis Shopee Airway Bill) -->
                <div class="p-5 space-y-4 text-xs border-b border-slate-200">
                    <!-- Barcode Resi -->
                    <div class="text-center py-3 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-widest">NOMOR RESI BOOKING (AUTOMATIC)</span>
                        <h2 class="text-xl font-bold font-mono tracking-wider text-slate-900" x-text="selectedOrder.tracking_number"></h2>
                        <!-- Simulated Barcode Lines -->
                        <div class="flex justify-center items-center gap-0.5 h-10 py-1">
                            <div class="w-1 h-full bg-slate-900"></div><div class="w-0.5 h-full bg-slate-900"></div>
                            <div class="w-1.5 h-full bg-slate-900"></div><div class="w-0.5 h-full bg-slate-900"></div>
                            <div class="w-2 h-full bg-slate-900"></div><div class="w-1 h-full bg-slate-900"></div>
                            <div class="w-0.5 h-full bg-slate-900"></div><div class="w-1.5 h-full bg-slate-900"></div>
                            <div class="w-2 h-full bg-slate-900"></div><div class="w-1 h-full bg-slate-900"></div>
                        </div>
                    </div>

                    <!-- Grid Data Pengirim & Penerima -->
                    <div class="grid grid-cols-2 gap-3 text-[11px] border border-slate-200 rounded-xl p-3 bg-slate-50/50">
                        <div class="space-y-0.5">
                            <span class="font-bold text-slate-500 uppercase block text-[9px]">PENGIRIM (TOKO):</span>
                            <p class="font-semibold text-slate-900">Official Shop Store</p>
                            <p class="text-slate-600">0812-9988-7766</p>
                            <p class="text-slate-500 text-[10px]">Kota Surakarta, Jawa Tengah</p>
                        </div>
                        <div class="space-y-0.5 border-l border-slate-200 pl-3">
                            <span class="font-bold text-slate-500 uppercase block text-[9px]">PENERIMA (PELANGGAN):</span>
                            <p class="font-semibold text-slate-900" x-text="selectedOrder.recipient_name"></p>
                            <p class="text-slate-600" x-text="selectedOrder.recipient_phone"></p>
                            <p class="text-slate-600 text-[10px] leading-tight" x-text="selectedOrder.shipping_address"></p>
                        </div>
                    </div>

                    <!-- Items Summary List -->
                    <div class="space-y-1 text-[11px]">
                        <span class="font-bold text-slate-500 block uppercase text-[10px]">ISI PAKET:</span>
                        <div class="bg-white border border-slate-200 rounded-xl p-2.5 space-y-1">
                            <template x-for="item in selectedOrder.items" :key="item.id">
                                <div class="flex justify-between font-medium text-slate-800 text-[11px]">
                                    <span x-text="item.quantity + 'x ' + item.product_name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="p-4 bg-slate-50 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200">Cashless Paid - No COD Fee</span>
                    <div class="flex gap-2">
                        <button type="button" @click="showAwbPrintModal = false" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100">Tutup</button>
                        <button type="button" @click="window.print()" class="px-4 py-1.5 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold shadow-xs cursor-pointer flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 00-2 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Cetak Label</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

</div>
@endsection
