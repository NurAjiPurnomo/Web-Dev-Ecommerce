@extends('layouts.app')

@section('content')
<div x-data="{ tab: 'aktif' }" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Voucher Saya</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola dan gunakan voucher yang sudah Anda klaim.</p>
        </div>
        <a href="{{ route('promo') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800 bg-blue-50 px-4 py-2 rounded-lg">
            Cari Voucher Lain
        </a>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button 
                @click="tab = 'aktif'"
                :class="tab === 'aktif' ? 'border-blue-700 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm cursor-pointer"
            >
                Voucher Aktif
                <span class="ml-2 bg-blue-100 text-blue-700 py-0.5 px-2.5 rounded-full text-xs">{{ $activeVouchers->count() }}</span>
            </button>
            <button 
                @click="tab = 'tidak-berlaku'"
                :class="tab === 'tidak-berlaku' ? 'border-blue-700 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm cursor-pointer"
            >
                Riwayat / Tidak Berlaku
                <span class="ml-2 bg-slate-100 text-slate-700 py-0.5 px-2.5 rounded-full text-xs">{{ $inactiveVouchers->count() }}</span>
            </button>
        </nav>
    </div>

    <!-- Active Vouchers -->
    <div x-show="tab === 'aktif'" x-transition>
        @if($activeVouchers->isEmpty())
            <div class="text-center py-12 bg-white rounded-xl border border-slate-200">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Belum Ada Voucher Aktif</h3>
                <p class="mt-1 text-sm text-slate-500">Anda belum memiliki voucher yang siap digunakan.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($activeVouchers as $v)
                    <div class="bg-white border-l-4 border-l-blue-600 border-t border-r border-b border-slate-200 rounded-lg p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                                {{ str_replace('_', ' ', $v->type) }}
                            </span>
                            <h3 class="font-bold text-slate-900 mt-2 text-lg">
                                {{ $v->type === 'gratis_ongkir' ? 'Gratis Ongkir ' . $v->formatted_discount : 'Diskon ' . $v->formatted_discount }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Min. belanja {{ $v->formatted_min_spend }}</p>
                            <p class="text-xs text-slate-400 mt-1">Berlaku s/d {{ $v->expires_at ? $v->expires_at->format('d M Y') : 'Selamanya' }}</p>
                        </div>
                        <div class="text-right flex flex-col items-end justify-center">
                            <span class="text-[10px] text-slate-500 font-medium mb-1">Kode Voucher:</span>
                            <div class="bg-slate-100 border border-slate-200 px-3 py-1.5 rounded font-mono font-bold text-slate-700 text-sm">
                                {{ $v->code }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Inactive Vouchers -->
    <div x-show="tab === 'tidak-berlaku'" x-transition style="display: none;">
        @if($inactiveVouchers->isEmpty())
            <div class="text-center py-12 bg-white rounded-xl border border-slate-200">
                <p class="text-sm text-slate-500">Belum ada riwayat voucher terpakai atau kadaluarsa.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 opacity-60 grayscale">
                @foreach($inactiveVouchers as $v)
                    <div class="bg-slate-50 border-l-4 border-l-slate-400 border-t border-r border-b border-slate-200 rounded-lg p-5 flex items-center justify-between relative overflow-hidden">
                        
                        <!-- Status Stamp -->
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -rotate-12 border-2 border-slate-300 text-slate-300 font-extrabold text-xl py-1 px-4 rounded">
                            @if($v->pivot->is_used)
                                TERPAKAI
                            @elseif($v->status !== 'aktif')
                                DITUTUP
                            @else
                                KADALUARSA
                            @endif
                        </div>

                        <div class="relative z-10">
                            <span class="bg-slate-200 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                                {{ str_replace('_', ' ', $v->type) }}
                            </span>
                            <h3 class="font-bold text-slate-900 mt-2 text-lg">
                                {{ $v->type === 'gratis_ongkir' ? 'Gratis Ongkir ' . $v->formatted_discount : 'Diskon ' . $v->formatted_discount }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Min. belanja {{ $v->formatted_min_spend }}</p>
                        </div>
                        <div class="text-right flex flex-col items-end justify-center relative z-10">
                            <span class="text-[10px] text-slate-500 font-medium mb-1">Kode Voucher:</span>
                            <div class="bg-slate-200 border border-slate-300 px-3 py-1.5 rounded font-mono font-bold text-slate-500 text-sm">
                                {{ $v->code }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
