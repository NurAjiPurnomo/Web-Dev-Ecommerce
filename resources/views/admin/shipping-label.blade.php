<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman - {{ $order->waybill_number ?: $order->tracking_number ?: $order->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .label-card {
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 flex flex-col items-center justify-start text-slate-900 font-sans">

    <!-- Action Bar -->
    <div class="no-print w-full max-w-[400px] mb-4 flex items-center justify-between bg-white p-3 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-semibold text-slate-600">
            Resi: <span class="text-slate-900 font-bold">{{ $order->waybill_number ?: $order->tracking_number }}</span>
        </div>
        <button 
            onclick="window.print()" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2 rounded-lg transition-colors flex items-center gap-2 shadow-sm cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak Resi (A6)</span>
        </button>
    </div>

    <!-- Thermal A6 Label Document (100mm x 150mm aspect ratio) -->
    <div class="label-card w-full max-w-[400px] bg-white border-2 border-slate-900 rounded-none shadow-md p-4 text-xs font-sans leading-tight">
        
        <!-- Header: Expedition & Service -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3 mb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded bg-slate-900 text-white font-black text-sm flex items-center justify-center tracking-tighter">
                    {{ strtoupper(substr($order->courier ?: 'EXP', 0, 3)) }}
                </div>
                <div>
                    <h1 class="font-extrabold text-base uppercase tracking-tight text-slate-900 leading-none">
                        {{ $order->courier ?: 'EKSPEDISI' }}
                    </h1>
                    <span class="text-[10px] font-bold text-slate-600 tracking-wider">CASHLESS / NON-COD</span>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block bg-slate-900 text-white text-xs font-black px-2.5 py-1 rounded uppercase tracking-wider">
                    {{ $order->courier_service ?: 'EZ / REG' }}
                </span>
            </div>
        </div>

        <!-- Barcode & Waybill Section -->
        <div class="text-center border-b-2 border-slate-900 pb-3 mb-3">
            <!-- Simulated Barcode Lines -->
            <div class="flex items-center justify-center gap-[2px] h-12 my-1 px-4 bg-slate-50 py-1 border border-slate-300 rounded">
                <div class="w-[3px] h-full bg-slate-900"></div>
                <div class="w-[1px] h-full bg-slate-900"></div>
                <div class="w-[4px] h-full bg-slate-900"></div>
                <div class="w-[2px] h-full bg-slate-900"></div>
                <div class="w-[1px] h-full bg-slate-900"></div>
                <div class="w-[3px] h-full bg-slate-900"></div>
                <div class="w-[5px] h-full bg-slate-900"></div>
                <div class="w-[2px] h-full bg-slate-900"></div>
                <div class="w-[1px] h-full bg-slate-900"></div>
                <div class="w-[4px] h-full bg-slate-900"></div>
                <div class="w-[2px] h-full bg-slate-900"></div>
                <div class="w-[3px] h-full bg-slate-900"></div>
                <div class="w-[1px] h-full bg-slate-900"></div>
                <div class="w-[4px] h-full bg-slate-900"></div>
                <div class="w-[2px] h-full bg-slate-900"></div>
                <div class="w-[5px] h-full bg-slate-900"></div>
                <div class="w-[1px] h-full bg-slate-900"></div>
                <div class="w-[3px] h-full bg-slate-900"></div>
                <div class="w-[2px] h-full bg-slate-900"></div>
                <div class="w-[4px] h-full bg-slate-900"></div>
            </div>
            <div class="font-mono font-black text-sm tracking-widest text-slate-900">
                {{ $order->waybill_number ?: $order->tracking_number ?: ('JT' . date('Ymd') . rand(1000, 9999)) }}
            </div>
            <div class="text-[10px] text-slate-500 font-semibold mt-0.5">
                No. Pesanan: #{{ $order->invoice_number }}
            </div>
        </div>

        <!-- Addresses Grid -->
        <div class="grid grid-cols-2 gap-3 border-b-2 border-slate-900 pb-3 mb-3">
            
            <!-- Penerima (Recipient) -->
            <div class="pr-2 border-r border-slate-300">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 block mb-1">PENERIMA:</span>
                <h2 class="font-extrabold text-slate-900 text-xs mb-0.5">{{ $order->recipient_name ?: ($order->user->name ?? 'Pelanggan') }}</h2>
                <div class="text-[11px] font-semibold text-slate-800 mb-1">
                    📞 {{ $order->recipient_phone ?: ($order->user->phone ?? '081234567890') }}
                </div>
                <p class="text-[10px] text-slate-700 leading-tight">
                    {{ $order->shipping_address }}
                </p>
            </div>

            <!-- Pengirim (Sender / Store) -->
            <div class="pl-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 block mb-1">PENGIRIM:</span>
                <h2 class="font-extrabold text-slate-900 text-xs mb-0.5">{{ $store->store_name ?? 'Toko Online Official' }}</h2>
                <div class="text-[11px] font-semibold text-slate-800 mb-1">
                    📞 {{ $store->sender_phone ?? '081234567890' }}
                </div>
                <p class="text-[10px] text-slate-700 leading-tight">
                    {{ $store->address_detail ?? 'Pusat Distribusi Utama' }}, {{ $store->district ?? 'Jakarta' }}, {{ $store->city ?? 'Jakarta Pusat' }}
                </p>
            </div>

        </div>

        <!-- Product Items Table -->
        <div class="mb-3">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 block mb-1">RINCIAN BARANG:</span>
            <div class="border border-slate-300 rounded overflow-hidden">
                <table class="w-full text-[10px]">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-300">
                        <tr>
                            <th class="py-1 px-2 text-left">Nama Produk</th>
                            <th class="py-1 px-2 text-center w-10">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($order->items as $item)
                            <tr>
                                <td class="py-1 px-2 font-medium text-slate-800">{{ $item->product->name ?? 'Produk Pesanan' }}</td>
                                <td class="py-1 px-2 text-center font-bold">{{ $item->quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-1 px-2 font-medium text-slate-800">Paket Pesanan Toko</td>
                                <td class="py-1 px-2 text-center font-bold">1</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer / Notes -->
        <div class="bg-slate-50 border border-slate-300 rounded p-2 flex items-center justify-between text-[10px]">
            <div>
                <span class="font-bold text-slate-900 block">Berat: {{ (int)ceil(($order->total_weight ?? 1000)/1000) }} kg</span>
                <span class="text-slate-500">Biteship Automated Label</span>
            </div>
            <div class="text-right">
                <span class="font-bold text-slate-900 block">Status: READY TO SHIP</span>
                <span class="text-slate-500">{{ date('d M Y H:i') }}</span>
            </div>
        </div>

    </div>

</body>
</html>
