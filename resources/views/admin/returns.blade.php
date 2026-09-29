@extends('admin.layout')
@section('title', 'Retur & Komplain')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Retur &amp; Komplain</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola pengajuan pengembalian barang, komplain, dan bukti dari pembeli.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3">
    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span class="text-sm font-semibold">{{ session('success') }}</span>
</div>
@endif

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-bold text-slate-900 text-base">Daftar Pengajuan Retur / Komplain ({{ $returns->count() }} Transaksi)</h3>
    </div>

    @if($returns->isEmpty())
        <div class="p-12 text-center text-slate-400 space-y-3">
            <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="text-sm font-semibold">Belum ada pengajuan retur atau komplain dari pembeli.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="p-4">Invoice / Pembeli</th>
                        <th class="p-4">Alasan Retur</th>
                        <th class="p-4">Bukti Foto & Video</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi Peninjauan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($returns as $r)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-slate-900">{{ $r->order->invoice_number ?? ('Order #' . $r->order_id) }}</div>
                                <div class="text-xs text-slate-500">{{ $r->user->name ?? 'Pelanggan' }} ({{ $r->created_at->format('d M Y H:i') }})</div>
                            </td>
                            <td class="p-4">
                                <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 uppercase tracking-wide">
                                    {{ str_replace('_', ' ', $r->reason) }}
                                </span>
                                <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $r->description }}</p>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    @if($r->photo_proof)
                                        <a href="{{ asset($r->photo_proof) }}" target="_blank" class="px-2.5 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1">
                                            📷 Foto Bukti
                                        </a>
                                    @endif

                                    @if($r->video_proof)
                                        <a href="{{ asset($r->video_proof) }}" target="_blank" class="px-2.5 py-1.5 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1">
                                            🎥 Video Unboxing
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4">
                                @if($r->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu Tinjauan
                                    </span>
                                @elseif($r->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Retur Disetujui
                                    </span>
                                @elseif($r->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        ✕ Ditolak (Tidak Valid)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                @if($r->status === 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Form Approve -->
                                        <form action="{{ route('admin.returns.process', $r->id) }}" method="POST" onsubmit="return confirm('Setujui pengembalian barang ini?')">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-colors cursor-pointer">
                                                ✓ Setujui
                                            </button>
                                        </form>

                                        <!-- Form Reject -->
                                        <form action="{{ route('admin.returns.process', $r->id) }}" method="POST" onsubmit="return confirm('Tolak pengajuan retur ini?')">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-colors cursor-pointer">
                                                ✕ Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic font-medium">Sudah Diproses</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
