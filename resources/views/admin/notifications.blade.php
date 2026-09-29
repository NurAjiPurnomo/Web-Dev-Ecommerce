@extends('admin.layout')

@section('title', 'Notifikasi')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 sm:p-6 rounded-xl border border-slate-200 shadow-2xs">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Notifikasi Pesanan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Riwayat notifikasi otomatis perubahan status pesanan ke pelanggan.</p>
        </div>
        <div>
            <a href="{{ route('admin.orders') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm px-4 py-2.5 rounded-lg transition-all shadow-2xs flex items-center justify-center gap-2 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>Kelola Pesanan</span>
            </a>
        </div>
    </div>

    <!-- Guidance Info Box -->
    <div class="bg-blue-50/80 border border-blue-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-blue-700 text-white flex items-center justify-center font-semibold text-base shrink-0 shadow-2xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="space-y-1 text-xs text-slate-700 flex-1">
            <h4 class="font-semibold text-slate-900 text-sm">Bagaimana Notifikasi Otomatis Ini Bekerja?</h4>
            <p>1. Setiap kali Anda mengubah status pesanan menjadi <span class="font-bold text-slate-900 font-mono">Sedang Dikemas</span>, <span class="font-bold text-slate-900 font-mono">Dikirim (+ Resi)</span>, atau <span class="font-bold text-slate-900 font-mono">Selesai</span> di halaman <a href="{{ route('admin.orders') }}" class="text-blue-700 font-bold underline">Manajemen Penjualan</a>, sistem akan otomatis mencatat dan menerbitkan notifikasi ini.</p>
            <p>2. Pelanggan terkait akan langsung melihat notifikasi ini pada **Lonceng Notifikasi Navbar Utama** di website mereka secara real-time.</p>
        </div>
    </div>

    <!-- Notifications Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3.5">Judul &amp; Pesan Status Pesanan</th>
                        <th class="px-5 py-3.5">Penerima</th>
                        <th class="px-5 py-3.5">Waktu Terbit</th>
                        <th class="px-5 py-3.5">Status Tampil</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($notifications as $n)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $n->title }}
                                @if($n->content)
                                    <div class="text-[11px] font-normal text-slate-500 mt-0.5">{{ $n->content }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 font-bold whitespace-nowrap">
                                @if($n->user)
                                    <span class="bg-blue-50 text-blue-700 text-[11px] font-semibold px-2.5 py-1 rounded border border-blue-200 inline-block truncate max-w-[180px]" title="{{ $n->user->name }} ({{ $n->user->email }})">
                                        👤 {{ $n->user->name }}
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-slate-700 text-[11px] font-semibold px-2.5 py-1 rounded border border-slate-200 inline-block whitespace-nowrap">SEMUA PELANGGAN</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-xs font-mono">
                                {{ $n->created_at ? $n->created_at->format('d M Y, H:i') . ' WIB' : '-' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($n->status === 'ditayangkan')
                                    <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-emerald-200 inline-block whitespace-nowrap">● Terkirim ke Lonceng</span>
                                @else
                                    <span class="bg-slate-100 text-slate-600 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-slate-200 inline-block whitespace-nowrap">● Selesai</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <form action="{{ route('admin.notifications.toggle', $n->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold {{ $n->status === 'ditayangkan' ? 'text-red-600 bg-red-50 border-red-200 hover:bg-red-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }} px-3 py-1.5 rounded-lg border transition-colors cursor-pointer whitespace-nowrap">
                                        {{ $n->status === 'ditayangkan' ? 'Sembunyikan' : 'Tampilkan Lagi' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada riwayat notifikasi status pesanan. Notifikasi akan otomatis tercatat saat Anda memperbarui status pesanan di Manajemen Penjualan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
