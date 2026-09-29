@extends('admin.layout')

@section('title', 'Voucher')

@section('content')
<div x-data="{ showAddModal: false }" class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Voucher Diskon</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Buat kupon diskon belanja dan syarat penggunaan voucher.</p>
        </div>
        <button 
            type="button" 
            @click="showAddModal = true"
            class="bg-blue-700 hover:bg-blue-800 text-white font-semibold text-xs sm:text-sm px-5 py-2.5 rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>+ Buat Voucher Baru</span>
        </button>
    </div>

    <!-- Vouchers Grid / Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3.5">Kode Voucher</th>
                        <th class="px-5 py-3.5">Kategori / Tipe</th>
                        <th class="px-5 py-3.5">Besar Potongan</th>
                        <th class="px-5 py-3.5">Min. Belanja</th>
                        <th class="px-5 py-3.5">Tanggal Kedaluwarsa</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-mono font-semibold text-blue-700">
                                {{ $v->code }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="bg-blue-50 text-blue-700 font-bold text-[11px] px-2.5 py-0.5 rounded border border-blue-200">
                                    {{ str_replace('_', ' ', strtoupper($v->type)) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-900">
                                {{ $v->formatted_discount }}
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $v->formatted_min_spend }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-xs">
                                {{ $v->expires_at ? $v->expires_at->format('d M Y') : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($v->status === 'aktif')
                                    <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-emerald-200">● Aktif</span>
                                @else
                                    <span class="bg-red-50 text-red-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-red-200">● Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-1">
                                <form action="{{ route('admin.vouchers.toggle', $v->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold {{ $v->status === 'aktif' ? 'text-red-600 bg-red-50 border-red-200 hover:bg-red-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }} px-3 py-1.5 rounded-lg border transition-colors cursor-pointer">
                                        {{ $v->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada voucher promo diterbitkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL BUAT VOUCHER -->
    <div 
        x-show="showAddModal" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <div @click.outside="showAddModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-semibold text-slate-900 text-base">Buat Voucher Baru</h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>

            <form action="{{ route('admin.vouchers.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kode Voucher / Promo <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required uppercase placeholder="Contoh: MERDEKA50K" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 font-mono font-bold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none uppercase">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori Voucher</label>
                        <select name="type" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                            <option value="gratis_ongkir">Gratis Ongkir</option>
                            <option value="diskon_nominal">Diskon Nominal (Rp)</option>
                            <option value="diskon_persen">Diskon Persen (%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Besar Potongan <span class="text-red-500">*</span></label>
                        <input type="number" name="discount_value" required placeholder="50000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Min. Belanja (Rp)</label>
                        <input type="number" name="min_spend" value="100000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kadaluarsa <span class="text-red-500">*</span></label>
                        <input type="date" name="expires_at" value="2026-08-31" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none">
                    </div>
                </div>

                <div class="pt-3 flex gap-2 justify-end">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold shadow-md cursor-pointer">Terbitkan Voucher</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
