@extends('admin.layout')

@section('title', 'Manajemen Program Afiliasi')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight">Manajemen Program Afiliasi</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pantau perolehan komisi mitra afiliasi, kode referral, dan pencairan dana komisi</p>
        </div>
        <div class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-2xs">
            Total Komisi: <span class="text-emerald-600 font-semibold">Rp {{ number_format($affiliates->sum('commission_earned'), 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Affiliates Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3.5">Mitra Afiliasi</th>
                        <th class="px-5 py-3.5">Kode Referral</th>
                        <th class="px-5 py-3.5">Persentase Komisi</th>
                        <th class="px-5 py-3.5">Total Omzet Sales</th>
                        <th class="px-5 py-3.5">Komisi Diperoleh</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($affiliates as $a)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $a->user->name ?? 'Mitra Afiliasi' }}
                                <div class="text-[11px] font-normal text-slate-500">{{ $a->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-blue-700">
                                {{ $a->referral_code }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-900">
                                {{ $a->commission_rate }}%
                            </td>
                            <td class="px-5 py-4 text-slate-800 font-bold">
                                Rp {{ number_format($a->total_sales, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-emerald-600 font-semibold">
                                Rp {{ number_format($a->commission_earned, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-emerald-200">● {{ $a->status }}</span>
                            </td>
                            <td class="px-5 py-4 text-right space-x-1">
                                <form action="{{ route('admin.affiliates.payout', $a->id) }}" method="POST" class="inline" @submit.prevent="$dispatch('open-confirm', { title: 'Konfirmasi Pencairan', message: 'Proses pencairan dana komisi sebesar Rp {{ number_format($a->commission_earned, 0, ',', '.') }} ke pengguna {{ $a->user->name }}?', confirmText: 'Ya, Cairkan', action: () => $el.submit() })">
                                    @csrf
                                    <button type="submit" {{ $a->commission_earned <= 0 ? 'disabled' : '' }} class="text-xs font-bold {{ $a->commission_earned > 0 ? 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100 cursor-pointer' : 'text-slate-400 bg-slate-100 border-slate-200 opacity-50 cursor-not-allowed' }} px-3 py-1.5 rounded-lg border transition-colors">
                                        Cairkan Komisi
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada mitra afiliasi terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
