@extends('admin.layout')

@section('title', 'Manajemen Pelanggan')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight">Manajemen Pelanggan</h1>
            <p class="text-xs sm:text-sm text-slate-500">Kelola akun pengguna terdaftar, status keanggotaan, dan data alamat</p>
        </div>
        <div class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-2xs">
            Total Pelanggan: <span class="text-blue-700 font-semibold">{{ $users->count() }} Pengguna</span>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs">
        <form action="{{ route('admin.users') }}" method="GET" class="w-full sm:w-96 relative">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Cari nama, email, atau no. HP pelanggan..." 
                class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-9 pr-4 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
            >
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">No. Telepon / WA</th>
                        <th class="px-5 py-3.5">Kota / Alamat</th>
                        <th class="px-5 py-3.5">Tanggal Daftar</th>
                        <th class="px-5 py-3.5">Status Akun</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-700 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900">{{ $u->name }}</h4>
                                        <p class="text-[11px] text-slate-500">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700 font-semibold">
                                {{ $u->phone ?: '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $u->city ?: 'DKI Jakarta' }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $u->address ?: 'Alamat belum diatur' }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-xs">
                                {{ $u->created_at ? $u->created_at->format('d M Y') : '07 Agt 2026' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($u->status === 'active' || empty($u->status))
                                    <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-emerald-200">● Aktif</span>
                                @else
                                    <span class="bg-red-50 text-red-700 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-red-200">● Suspended</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-1">
                                <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold {{ ($u->status === 'active' || empty($u->status)) ? 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }} px-3 py-1.5 rounded-lg border transition-colors cursor-pointer">
                                        {{ ($u->status === 'active' || empty($u->status)) ? 'Suspend Akun' : 'Aktifkan Akun' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada data pelanggan terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
