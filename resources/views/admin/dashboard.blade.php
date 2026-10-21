@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Dashboard</h1>
            <p class="text-sm text-slate-500 mt-0.5">Ringkasan aktivitas penjualan dan operasional toko.</p>
        </div>
        <div>
            <a 
                href="{{ route('admin.products') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Produk</span>
            </a>
        </div>
    </div>

    <!-- Perlu Diproses (To-Do List Harian) -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-900">Perlu Diproses</h2>
            <a href="{{ route('admin.orders') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                Semua Pesanan &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <a 
                href="{{ route('admin.orders') }}?status=diproses" 
                class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-blue-50/50 hover:border-blue-200 transition-colors text-center"
            >
                <div class="text-2xl font-semibold {{ $tasks['to_ship'] > 0 ? 'text-blue-600' : 'text-slate-800' }}">
                    {{ $tasks['to_ship'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Perlu Dikemas</div>
            </a>

            <a 
                href="{{ route('admin.orders') }}?status=belum_dibayar" 
                class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-amber-50/50 hover:border-amber-200 transition-colors text-center"
            >
                <div class="text-2xl font-semibold {{ $tasks['unpaid'] > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                    {{ $tasks['unpaid'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Belum Bayar</div>
            </a>

            <a 
                href="{{ route('admin.orders') }}?status=dikirim" 
                class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-indigo-50/50 hover:border-indigo-200 transition-colors text-center"
            >
                <div class="text-2xl font-semibold {{ $tasks['shipping'] > 0 ? 'text-indigo-600' : 'text-slate-800' }}">
                    {{ $tasks['shipping'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Sedang Dikirim</div>
            </a>

            <a 
                href="{{ route('admin.returns') }}" 
                class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-rose-50/50 hover:border-rose-200 transition-colors text-center"
            >
                <div class="text-2xl font-semibold {{ $tasks['returns'] > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                    {{ $tasks['returns'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Komplain / Retur</div>
            </a>

            <a 
                href="{{ route('admin.products') }}" 
                class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-orange-50/50 hover:border-orange-200 transition-colors text-center"
            >
                <div class="text-2xl font-semibold {{ $tasks['low_stock'] > 0 ? 'text-orange-600' : 'text-slate-800' }}">
                    {{ $tasks['low_stock'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Stok Menipis</div>
            </a>

            <a 
                href="{{ route('admin.orders') }}?status=selesai" 
                class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-emerald-50/50 hover:border-emerald-200 transition-colors text-center"
            >
                <div class="text-2xl font-semibold {{ $tasks['completed'] > 0 ? 'text-emerald-600' : 'text-slate-800' }}">
                    {{ $tasks['completed'] }}
                </div>
                <div class="text-xs font-medium text-slate-700 mt-1">Pesanan Selesai</div>
            </a>
        </div>
    </div>

    <!-- Ringkasan Penjualan -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Ringkasan Penjualan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Performa transaksi dan omset toko.</p>
            </div>

            <!-- Periode Filter Pills -->
            <div class="inline-flex bg-slate-100 p-0.5 rounded-lg text-xs font-medium">
                <a 
                    href="{{ route('admin.dashboard', ['period' => 'all']) }}" 
                    class="px-3 py-1.5 rounded-md transition-colors {{ $selectedPeriod === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Semua
                </a>
                <a 
                    href="{{ route('admin.dashboard', ['period' => 'today']) }}" 
                    class="px-3 py-1.5 rounded-md transition-colors {{ $selectedPeriod === 'today' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Hari Ini
                </a>
                <a 
                    href="{{ route('admin.dashboard', ['period' => '7days']) }}" 
                    class="px-3 py-1.5 rounded-md transition-colors {{ $selectedPeriod === '7days' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    7 Hari Terakhir
                </a>
                <a 
                    href="{{ route('admin.dashboard', ['period' => 'this_month']) }}" 
                    class="px-3 py-1.5 rounded-md transition-colors {{ $selectedPeriod === 'this_month' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Bulan Ini
                </a>
            </div>
        </div>

        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/40">
                <div class="text-xs font-medium text-slate-500">Total Penjualan</div>
                <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-1">
                    Rp {{ number_format($stats['gross_revenue'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-400 mt-1">
                    @if($stats['gross_revenue'] > 0)
                        Subtotal produk: Rp {{ number_format($stats['subtotal_revenue'] ?? 0, 0, ',', '.') }}
                    @else
                        Belum ada transaksi
                    @endif
                </div>
            </div>

            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/40">
                <div class="text-xs font-medium text-slate-500">Pesanan Berhasil</div>
                <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-1">
                    {{ number_format($stats['total_orders'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-400 mt-1">
                    @if($stats['total_orders'] > 0)
                        Rata-rata: Rp {{ number_format($stats['avg_order_val'], 0, ',', '.') }}
                    @else
                        Belum ada pesanan
                    @endif
                </div>
            </div>

            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/40">
                <div class="text-xs font-medium text-slate-500">Produk Terjual</div>
                <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-1">
                    {{ number_format($stats['items_sold'], 0, ',', '.') }} pcs
                </div>
                <div class="text-xs text-slate-400 mt-1">Unit berhasil diproses</div>
            </div>

            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/40">
                <div class="text-xs font-medium text-slate-500">Pelanggan Terdaftar</div>
                <div class="text-xl sm:text-2xl font-semibold text-slate-900 mt-1">
                    {{ number_format($stats['total_customers'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-400 mt-1">{{ $stats['total_products'] }} produk aktif</div>
            </div>
        </div>
    </div>

    <!-- Grafik Tren & Kategori (2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kategori Produk (7 Kolom) -->
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 mb-1">Performa Kategori</h3>
                <p class="text-xs text-slate-500 mb-4">Rincian pendapatan berdasarkan kategori barang.</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="text-slate-500 font-medium border-b border-slate-100 text-xs">
                            <tr>
                                <th class="pb-2.5">Kategori</th>
                                <th class="pb-2.5 text-center">Terjual</th>
                                <th class="pb-2.5 text-right">Pendapatan</th>
                                <th class="pb-2.5 text-right">Porsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($categoryBreakdown as $catData)
                                <tr>
                                    <td class="py-2.5 font-medium text-slate-900">{{ $catData['category'] }}</td>
                                    <td class="py-2.5 text-center text-slate-600">{{ $catData['quantity'] }} pcs</td>
                                    <td class="py-2.5 text-right font-medium text-slate-900">Rp {{ number_format($catData['gross'], 0, ',', '.') }}</td>
                                    <td class="py-2.5 text-right text-slate-500">{{ $catData['pct'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tren Bulanan (5 Kolom) -->
        @php
            $maxMonthlyGross = max(array_column($monthlyTrend, 'gross') ?: [1]);
            if ($maxMonthlyGross <= 0) $maxMonthlyGross = 1;
        @endphp
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 mb-1">Tren Bulanan ({{ date('Y') }})</h3>
                <p class="text-xs text-slate-500 mb-4">Grafik omset penjualan selama 12 bulan.</p>
            </div>

            <div>
                <div class="h-36 flex items-end justify-between gap-1.5 pb-2 border-b border-slate-100">
                    @foreach($monthlyTrend as $mTrend)
                        @php
                            $heightPct = ($mTrend['gross'] > 0) ? max(12, round(($mTrend['gross'] / $maxMonthlyGross) * 100)) : 6;
                            $shortName = substr($mTrend['month_name'], 0, 3);
                        @endphp
                        <div class="flex-1 h-full flex flex-col justify-end items-center gap-1 group relative">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-7 bg-slate-900 text-white text-[10px] py-1 px-2 rounded pointer-events-none whitespace-nowrap z-20">
                                {{ $mTrend['month_name'] }}: Rp {{ number_format($mTrend['gross'], 0, ',', '.') }}
                            </div>
                            <div class="w-full flex-1 flex items-end justify-center">
                                <div 
                                    style="height: {{ $heightPct }}%" 
                                    class="w-full max-w-[16px] rounded-t-sm transition-all {{ $mTrend['gross'] > 0 ? 'bg-blue-600' : 'bg-slate-200' }}"
                                ></div>
                            </div>
                            <span class="text-[10px] text-slate-400">
                                {{ $shortName }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 mt-2">
                    <span>Januari</span>
                    <span>Desember</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Produk Terlaris & Stok Menipis (2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Produk Terlaris -->
        @php
            $periodLabels = [
                'all'        => 'Semua Waktu',
                'today'      => 'Hari Ini',
                '7days'      => '7 Hari Terakhir',
                '30days'     => '30 Hari Terakhir',
                'this_month' => 'Bulan Ini',
            ];
            $currentPeriodLabel = $periodLabels[$selectedPeriod] ?? 'Periode Ini';
        @endphp
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Produk Terlaris</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $currentPeriodLabel }}</p>
                </div>
                <a href="{{ route('admin.products') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                    Lihat Produk &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($topProducts as $tp)
                    <div class="flex items-center gap-3 py-2 border-b border-slate-100 last:border-b-0">
                        <img src="{{ $tp->image }}" alt="{{ $tp->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs sm:text-sm font-medium text-slate-900 truncate">{{ $tp->name }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $tp->category }} • {{ $tp->formatted_price }}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs font-semibold text-slate-900">{{ $tp->period_sold ?? $tp->sold }} terjual</div>
                            <div class="text-[11px] text-slate-400">Stok: {{ $tp->stock }}</div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        Belum ada data produk terjual pada {{ strtolower($currentPeriodLabel) }}.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Peringatan Stok Menipis -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-900">Peringatan Stok (&le; 5 pcs)</h3>
                <a href="{{ route('admin.products') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                    Kelola Stok &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($lowStockProducts as $lp)
                    <div class="flex items-start gap-3 py-2.5 border-b border-slate-100 last:border-b-0">
                        <img src="{{ $lp->image }}" alt="{{ $lp->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0 mt-0.5">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs sm:text-sm font-medium text-slate-900 truncate">{{ $lp->name }}</div>
                            @if(!empty($lp->low_variants_list))
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($lp->low_variants_list as $lv)
                                        <span class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.5 rounded font-medium {{ $lv['stock'] === 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            <span>{{ $lv['name'] }}:</span>
                                            <span class="font-bold">{{ $lv['stock'] === 0 ? 'Habis' : $lv['stock'] . ' pcs' }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-xs text-slate-400 mt-0.5">{{ $lp->category }} • {{ $lp->formatted_price }}</div>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            @if(empty($lp->low_variants_list))
                                <div class="text-xs font-semibold {{ $lp->stock === 0 ? 'text-rose-600' : 'text-orange-600' }}">
                                    {{ $lp->stock === 0 ? 'Habis' : $lp->stock . ' pcs' }}
                                </div>
                            @endif
                            <a href="{{ route('admin.products') }}" class="text-[11px] text-blue-600 hover:underline block mt-0.5">
                                Edit Stok
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        Semua stok produk dalam kondisi aman.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Pesanan Terbaru -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Pesanan Terbaru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Transaksi belanja pelanggan terbaru.</p>
            </div>
            <a href="{{ route('admin.orders') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200 text-xs">
                    <tr>
                        <th class="px-4 py-3">No. Invoice</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-mono font-medium text-slate-900">{{ $order->invoice_number }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $order->user->name ?? $order->recipient_name }}</div>
                                <div class="text-xs text-slate-400">{{ $order->user->email ?? $order->recipient_phone }}</div>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $order->formatted_total }}</td>
                            <td class="px-4 py-3">
                                @if($order->status === 'dikemas' || $order->status === 'diproses')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                        Dikemas
                                    </span>
                                @elseif($order->status === 'dikirim')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Dikirim
                                    </span>
                                @elseif($order->status === 'selesai')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai
                                    </span>
                                @elseif($order->status === 'batal')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        Batal
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.orders') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">
                                Belum ada pesanan terbaru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
