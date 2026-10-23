@extends('admin.layout')

@section('title', 'Voucher Diskon')

@section('content')
<div 
    x-data="{ 
        showAddModal: false,
        searchQuery: '',
        activeTab: 'semua',
        categoryFilter: 'all',
        copiedCode: '',
        copyCode(code) {
            navigator.clipboard.writeText(code);
            this.copiedCode = code;
            setTimeout(() => this.copiedCode = '', 2000);
        },
        matchesRow(target, status, category, code, customerName, customerEmail) {
            // Tab filter logic
            if (this.activeTab === 'publik' && target === 'pelanggan') return false;
            if (this.activeTab === 'khusus' && target !== 'pelanggan') return false;
            if (this.activeTab === 'aktif' && status !== 'aktif') return false;
            if (this.activeTab === 'nonaktif' && status !== 'nonaktif') return false;

            // Category filter logic
            if (this.categoryFilter !== 'all' && category !== this.categoryFilter) return false;

            // Search query logic
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase();
                const matchCode = code.toLowerCase().includes(q);
                const matchName = customerName.toLowerCase().includes(q);
                const matchEmail = customerEmail.toLowerCase().includes(q);
                if (!matchCode && !matchName && !matchEmail) return false;
            }

            return true;
        }
    }" 
    class="space-y-6"
>

    <!-- Header Section (Konsisten dengan Pesanan, Pelanggan & Dashboard) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight">Voucher Diskon</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola kupon belanja publik dan voucher eksklusif khusus akun pelanggan.</p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                type="button" 
                @click="showAddModal = true"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs sm:text-sm px-4 py-2.5 rounded-xl transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer shrink-0"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Voucher Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Sukses / Error -->
    @if(session('success'))
        <div class="p-3.5 sm:p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium flex items-center justify-between gap-3 animate-in fade-in duration-200">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs p-1 cursor-pointer">✕</button>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 sm:p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium flex items-center justify-between gap-3 animate-in fade-in duration-200">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold text-xs p-1 cursor-pointer">✕</button>
        </div>
    @endif

    <!-- Container Utama: Status Tabs, Pencarian & Tabel (Standar Seller Centre) -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        
        <!-- Status & Target Tabs (Shopee / Tokopedia Seller Centre Pattern) -->
        <div class="border-b border-slate-200 px-4 sm:px-6 pt-2 bg-white">
            <nav class="flex items-center gap-4 sm:gap-6 overflow-x-auto no-scrollbar -mb-px text-xs sm:text-sm font-medium">
                <!-- Tab: Semua -->
                <button 
                    type="button"
                    @click="activeTab = 'semua'"
                    :class="activeTab === 'semua' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="py-3 px-1 border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors cursor-pointer"
                >
                    <span>Semua Voucher</span>
                    <span 
                        :class="activeTab === 'semua' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'"
                        class="px-2 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ $vouchers->count() }}
                    </span>
                </button>

                <!-- Tab: Publik -->
                <button 
                    type="button"
                    @click="activeTab = 'publik'"
                    :class="activeTab === 'publik' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="py-3 px-1 border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors cursor-pointer"
                >
                    <span>Voucher Publik</span>
                    <span 
                        :class="activeTab === 'publik' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'"
                        class="px-2 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ $vouchers->where('target', '!=', 'pelanggan')->count() }}
                    </span>
                </button>

                <!-- Tab: Khusus Pelanggan -->
                <button 
                    type="button"
                    @click="activeTab = 'khusus'"
                    :class="activeTab === 'khusus' ? 'border-purple-600 text-purple-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="py-3 px-1 border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors cursor-pointer"
                >
                    <span>Khusus Pelanggan</span>
                    <span 
                        :class="activeTab === 'khusus' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600'"
                        class="px-2 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ $vouchers->where('target', 'pelanggan')->count() }}
                    </span>
                </button>

                <!-- Tab: Aktif -->
                <button 
                    type="button"
                    @click="activeTab = 'aktif'"
                    :class="activeTab === 'aktif' ? 'border-emerald-600 text-emerald-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="py-3 px-1 border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors cursor-pointer"
                >
                    <span>Aktif</span>
                    <span 
                        :class="activeTab === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                        class="px-2 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ $vouchers->where('status', 'aktif')->count() }}
                    </span>
                </button>

                <!-- Tab: Nonaktif -->
                <button 
                    type="button"
                    @click="activeTab = 'nonaktif'"
                    :class="activeTab === 'nonaktif' ? 'border-slate-600 text-slate-800 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="py-3 px-1 border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors cursor-pointer"
                >
                    <span>Nonaktif</span>
                    <span 
                        :class="activeTab === 'nonaktif' ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-600'"
                        class="px-2 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ $vouchers->where('status', 'nonaktif')->count() }}
                    </span>
                </button>
            </nav>
        </div>

        <!-- Toolbar Pencarian & Filter Kategori (Terintegrasi Rapi) -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-50/40">
            <!-- Search Bar -->
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Cari kode voucher atau nama/email pelanggan..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-600 focus:ring-1 focus:ring-blue-600 focus:outline-none transition-colors"
                >
            </div>

            <!-- Filter Kategori Voucher -->
            <div class="flex items-center gap-2 shrink-0">
                <select 
                    x-model="categoryFilter"
                    class="bg-white border border-slate-300 text-slate-700 text-xs font-medium rounded-xl px-3.5 py-2 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 focus:outline-none transition-colors cursor-pointer"
                >
                    <option value="all">Semua Tipe Diskon</option>
                    <option value="gratis_ongkir">Gratis Ongkir</option>
                    <option value="diskon_nominal">Diskon Nominal (Rp)</option>
                    <option value="diskon_persen">Diskon Persen (%)</option>
                </select>
            </div>
        </div>

        <!-- Tabel Voucher -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap">Kode Voucher</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Kategori</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Besar Potongan</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Min. Belanja</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Target Penerima</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Kedaluwarsa</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Status</th>
                        <th class="px-5 py-3.5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($vouchers as $v)
                        @php
                            $targetVal = $v->target === 'pelanggan' ? 'pelanggan' : 'semua';
                            $userName = strtolower($v->user->name ?? '');
                            $userEmail = strtolower($v->user->email ?? '');
                            $codeLower = strtolower($v->code);
                        @endphp
                        <tr 
                            x-show="matchesRow('{{ $targetVal }}', '{{ $v->status }}', '{{ $v->type }}', '{{ $codeLower }}', '{{ $userName }}', '{{ $userEmail }}')"
                            class="hover:bg-slate-50/80 transition-colors"
                        >
                            <!-- Kode Voucher -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <span class="font-mono font-bold text-slate-900 tracking-wider">
                                        {{ $v->code }}
                                    </span>
                                    <button 
                                        type="button" 
                                        @click="copyCode('{{ $v->code }}')" 
                                        class="text-slate-400 hover:text-slate-700 p-1 rounded-md transition-colors cursor-pointer"
                                        title="Salin Kode Voucher"
                                    >
                                        <svg x-show="copiedCode !== '{{ $v->code }}'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        <svg x-show="copiedCode === '{{ $v->code }}'" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            <!-- Kategori -->
                            <td class="px-5 py-4 whitespace-nowrap text-slate-700 text-xs">
                                @if($v->type === 'gratis_ongkir')
                                    Gratis Ongkir
                                @elseif($v->type === 'diskon_nominal')
                                    Diskon Nominal
                                @else
                                    Diskon Persen
                                @endif
                            </td>

                            <!-- Besar Potongan -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-900">{{ $v->formatted_discount }}</span>
                                @if($v->type === 'diskon_persen' && $v->max_discount)
                                    <span class="block text-[11px] text-slate-400 font-normal">Maks. Rp {{ number_format($v->max_discount, 0, ',', '.') }}</span>
                                @endif
                            </td>

                            <!-- Min. Belanja -->
                            <td class="px-5 py-4 whitespace-nowrap text-slate-600 font-normal">
                                {{ $v->formatted_min_spend }}
                            </td>

                            <!-- Target Penerima (Teks bersih & natural, tanpa background tombol / pill) -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($v->target === 'pelanggan' && $v->user)
                                    <div>
                                        <span class="font-medium text-slate-900 block">{{ $v->user->name }}</span>
                                        <span class="text-[11px] text-purple-600 font-medium block">Khusus Pelanggan</span>
                                    </div>
                                @elseif($v->target === 'pelanggan' && !$v->user)
                                    <div>
                                        <span class="font-medium text-slate-800 block">Pelanggan #{{ $v->user_id }}</span>
                                        <span class="text-[11px] text-slate-400 block">Akun terhapus</span>
                                    </div>
                                @else
                                    <span class="text-slate-600 text-xs">Semua Pelanggan</span>
                                @endif
                            </td>

                            <!-- Kedaluwarsa -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($v->expires_at)
                                    <span class="text-xs text-slate-700">{{ $v->expires_at->format('d M Y') }}</span>
                                    @if($v->expires_at->isPast())
                                        <span class="ml-1.5 inline-block text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Kedaluwarsa</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-xs">Selamanya</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($v->status === 'aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi (Rapi & Seimbang) -->
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Toggle Status Button -->
                                    <form action="{{ route('admin.vouchers.toggle', $v->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border transition-colors cursor-pointer {{ $v->status === 'aktif' ? 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }}"
                                            title="{{ $v->status === 'aktif' ? 'Klik untuk menonaktifkan voucher' : 'Klik untuk mengaktifkan voucher' }}"
                                        >
                                            {{ $v->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <!-- Delete Voucher Button -->
                                    <form 
                                        action="{{ route('admin.vouchers.delete', $v->id) }}" 
                                        method="POST" 
                                        class="inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus voucher {{ $v->code }}? Aksi ini tidak dapat dibatalkan.')"
                                    >
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors cursor-pointer"
                                            title="Hapus voucher"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <p class="text-sm font-semibold text-slate-600">Belum ada voucher diskon</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "Buat Voucher Baru" untuk menerbitkan kupon pertama.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL BUAT VOUCHER BARU -->
    <div 
        x-show="showAddModal" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
        style="display: none;"
    >
        <div 
            @click.outside="showAddModal = false" 
            x-data="{
                targetType: 'semua',
                voucherType: 'gratis_ongkir',
                codeVal: '',
                generateRandomCode() {
                    const prefix = this.voucherType === 'gratis_ongkir' ? 'ONGKIR' : (this.targetType === 'pelanggan' ? 'VIP' : 'DISKON');
                    const rand = Math.random().toString(36).substring(2, 6).toUpperCase();
                    this.codeVal = prefix + rand;
                }
            }"
            class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl p-6 sm:p-7 space-y-5 my-8"
        >
            <!-- Header Modal -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Buat Voucher Baru</h3>
                        <p class="text-xs text-slate-500">Tentukan penerima dan syarat kupon diskon.</p>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="showAddModal = false" 
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.vouchers.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <!-- TARGET PENERIMA VOUCHER -->
                <div>
                    <label class="block font-bold text-slate-700 mb-2">Target Penerima Voucher <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Option 1: Semua Pelanggan (Publik) -->
                        <label 
                            class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                            :class="targetType === 'semua' ? 'border-blue-600 bg-blue-50/50 shadow-2xs ring-1 ring-blue-600' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'"
                        >
                            <input 
                                type="radio" 
                                name="target" 
                                value="semua" 
                                x-model="targetType" 
                                class="sr-only"
                            >
                            <div class="flex items-center gap-2 font-bold text-slate-900">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Semua Pelanggan</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Kupon terbuka untuk seluruh pembeli dan tampil di halaman promo toko.
                            </p>
                        </label>

                        <!-- Option 2: Pelanggan Tertentu (Eksklusif) -->
                        <label 
                            class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                            :class="targetType === 'pelanggan' ? 'border-purple-600 bg-purple-50/50 shadow-2xs ring-1 ring-purple-600' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'"
                        >
                            <input 
                                type="radio" 
                                name="target" 
                                value="pelanggan" 
                                x-model="targetType" 
                                class="sr-only"
                            >
                            <div class="flex items-center gap-2 font-bold text-purple-900">
                                <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Pelanggan Tertentu</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Voucher hadiah / kompensasi hanya untuk 1 akun pelanggan tertentu.
                            </p>
                        </label>
                    </div>
                </div>

                <!-- SELECT USER DROPDOWN (HANYA MUNCUL KETIKA TARGET = PELANGGAN TERTENTU) -->
                <div x-show="targetType === 'pelanggan'" x-transition class="space-y-1.5 p-3.5 bg-purple-50/70 border border-purple-200 rounded-xl">
                    <label class="block font-bold text-purple-950">
                        Pilih Akun Pelanggan <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="user_id" 
                        class="w-full bg-white border border-purple-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600"
                        :required="targetType === 'pelanggan'"
                    >
                        <option value="">-- Pilih Akun Pelanggan --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->name }} &mdash; {{ $c->email }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-purple-700 flex items-center gap-1.5 pt-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Voucher akan langsung masuk ke dompet pelanggan tersebut dan notifikasi otomatis dikirimkan.</span>
                    </p>
                </div>

                <!-- KODE VOUCHER -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700">Kode Voucher / Kupon <span class="text-rose-500">*</span></label>
                        <button 
                            type="button" 
                            @click="generateRandomCode()" 
                            class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Buat Kode Acak</span>
                        </button>
                    </div>
                    <input 
                        type="text" 
                        name="code" 
                        x-model="codeVal"
                        required 
                        placeholder="Contoh: MERDEKA50K atau VIPDISKON" 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 font-mono font-bold text-slate-900 focus:bg-white focus:border-blue-600 focus:outline-none uppercase"
                    >
                </div>

                <!-- KATEGORI & BESAR POTONGAN -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori Voucher</label>
                        <select 
                            name="type" 
                            x-model="voucherType" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-semibold text-slate-900 focus:bg-white focus:border-blue-600 focus:outline-none"
                        >
                            <option value="gratis_ongkir">Gratis Ongkir</option>
                            <option value="diskon_nominal">Diskon Nominal (Rp)</option>
                            <option value="diskon_persen">Diskon Persen (%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            <span x-text="voucherType === 'diskon_persen' ? 'Besar Potongan (%)' : 'Besar Potongan (Rp)'"></span> 
                            <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="discount_value" 
                            required 
                            :placeholder="voucherType === 'diskon_persen' ? 'Contoh: 15' : 'Contoh: 50000'" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-semibold text-slate-900 focus:bg-white focus:border-blue-600 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- MAKSIMAL DISKON (HANYA UNTUK DISKON PERSEN) -->
                <div x-show="voucherType === 'diskon_persen'" x-transition>
                    <label class="block font-bold text-slate-700 mb-1">Maksimal Diskon (Rp) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input 
                        type="number" 
                        name="max_discount" 
                        placeholder="Contoh: 50000 (Kosongkan jika tanpa batas potongan)" 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-semibold text-slate-900 focus:bg-white focus:border-blue-600 focus:outline-none"
                    >
                </div>

                <!-- MINIMAL BELANJA & TANGGAL KEDALUWARSA -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Min. Belanja (Rp)</label>
                        <input 
                            type="number" 
                            name="min_spend" 
                            value="50000" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-semibold text-slate-900 focus:bg-white focus:border-blue-600 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kedaluwarsa <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            name="expires_at" 
                            value="{{ date('Y-m-d', strtotime('+30 days')) }}" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-semibold text-slate-900 focus:bg-white focus:border-blue-600 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Tombol Aksi Modal -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        @click="showAddModal = false" 
                        class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer transition-colors"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs cursor-pointer transition-colors flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Terbitkan Voucher</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
