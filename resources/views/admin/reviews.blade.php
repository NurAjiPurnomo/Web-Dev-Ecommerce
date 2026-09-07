@extends('admin.layout')

@section('title', 'Penilaian & Ulasan Produk')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight">Penilaian &amp; Ulasan Produk</h1>
            <p class="text-xs sm:text-sm text-slate-500">Kelola ulasan pembeli, statistik rating bintang, dan kepuasan pelanggan toko</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Ulasan</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total_reviews']) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center border border-blue-100 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Rating Toko</p>
                <div class="flex items-center gap-2 mt-1">
                    <h3 class="text-2xl font-bold text-slate-900">{{ number_format($stats['average_rating'], 1) }}</h3>
                    <span class="text-amber-500 text-sm font-semibold">★ / 5.0</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 shadow-2xs">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ulasan 5 Bintang</p>
                <h3 class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($stats['five_star']) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h47M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Perlu Diperhatikan</p>
                <h3 class="text-2xl font-bold text-rose-700 mt-1">{{ number_format($stats['low_rating']) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center border border-rose-100 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs space-y-3">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <form action="{{ route('admin.reviews') }}" method="GET" class="w-full sm:w-80 relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari ulasan, pembeli, produk..." 
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-9 pr-4 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </form>

            <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto text-xs font-semibold">
                <a href="{{ route('admin.reviews', array_merge(request()->query(), ['rating' => 'all'])) }}" class="px-3 py-1.5 rounded-lg border {{ request('rating', 'all') === 'all' ? 'bg-amber-500 text-white border-amber-500' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">Semua Rating</a>
                <a href="{{ route('admin.reviews', array_merge(request()->query(), ['rating' => '5'])) }}" class="px-3 py-1.5 rounded-lg border {{ request('rating') === '5' ? 'bg-amber-500 text-white border-amber-500' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">5 ★★★★★</a>
                <a href="{{ route('admin.reviews', array_merge(request()->query(), ['rating' => '4'])) }}" class="px-3 py-1.5 rounded-lg border {{ request('rating') === '4' ? 'bg-amber-500 text-white border-amber-500' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">4 ★★★★☆</a>
                <a href="{{ route('admin.reviews', array_merge(request()->query(), ['rating' => '3'])) }}" class="px-3 py-1.5 rounded-lg border {{ request('rating') === '3' ? 'bg-amber-500 text-white border-amber-500' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">3 ★★★☆☆</a>
                <a href="{{ route('admin.reviews', array_merge(request()->query(), ['rating' => '1'])) }}" class="px-3 py-1.5 rounded-lg border {{ request('rating') === '1' || request('rating') === '2' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">1-2 ★ (Buruk)</a>
            </div>
        </div>

        <!-- Filter Category Tabs -->
        <div class="pt-2 border-t border-slate-100 flex items-center gap-2 overflow-x-auto text-xs font-semibold">
            <span class="text-slate-400 text-[11px] uppercase tracking-wider shrink-0 mr-1">Kategori:</span>
            <a href="{{ route('admin.reviews', array_merge(request()->query(), ['category' => 'all'])) }}" class="px-3 py-1 rounded-md {{ request('category', 'all') === 'all' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
            <a href="{{ route('admin.reviews', array_merge(request()->query(), ['category' => 'Pakaian'])) }}" class="px-3 py-1 rounded-md {{ request('category') === 'Pakaian' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Pakaian</a>
            <a href="{{ route('admin.reviews', array_merge(request()->query(), ['category' => 'Sepatu'])) }}" class="px-3 py-1 rounded-md {{ request('category') === 'Sepatu' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Sepatu</a>
            <a href="{{ route('admin.reviews', array_merge(request()->query(), ['category' => 'Gadget'])) }}" class="px-3 py-1 rounded-md {{ request('category') === 'Gadget' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Gadget</a>
            <a href="{{ route('admin.reviews', array_merge(request()->query(), ['category' => 'Aksesoris'])) }}" class="px-3 py-1 rounded-md {{ request('category') === 'Aksesoris' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Aksesoris</a>
            <a href="{{ route('admin.reviews', array_merge(request()->query(), ['category' => 'Rumah Tangga'])) }}" class="px-3 py-1 rounded-md {{ request('category') === 'Rumah Tangga' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Rumah Tangga</a>
        </div>
    </div>

    <!-- Product Reviews Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3.5">Pembeli</th>
                        <th class="px-5 py-3.5">Produk Yang Diulas</th>
                        <th class="px-5 py-3.5">Rating Bintang</th>
                        <th class="px-5 py-3.5">Komentar / Ulasan</th>
                        <th class="px-5 py-3.5">Waktu Ulasan</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($reviews as $r)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs shrink-0 border border-blue-200">
                                        {{ $r->is_anonymous ? 'A' : strtoupper(substr($r->user->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900">
                                            {{ $r->is_anonymous ? 'Pengguna Anonim' : ($r->user->name ?? 'Pelanggan') }}
                                        </h4>
                                        <span class="text-[11px] text-slate-400 font-mono">Invoice: {{ $r->order_id }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($r->product)
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $r->product->image }}" alt="{{ $r->product->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                                        <div>
                                            <h5 class="font-bold text-slate-900 line-clamp-1 text-xs">{{ $r->product->name }}</h5>
                                            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded border border-slate-200">{{ $r->product->category }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Produk ID #{{ $r->product_id }}</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <div class="flex text-amber-400 text-xs">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span>{{ $i <= $r->rating ? '★' : '☆' }}</span>
                                        @endfor
                                    </div>
                                    <span class="font-semibold text-slate-900 text-xs ml-1">{{ $r->rating }}.0</span>
                                </div>
                            </td>

                            <td class="px-5 py-4 max-w-xs">
                                <p class="text-xs text-slate-700 leading-relaxed font-normal bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    "{{ $r->comment ?: 'Tidak ada ulasan tertulis.' }}"
                                </p>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500 font-mono">
                                {{ $r->created_at ? $r->created_at->format('d M Y, H:i') : '-' }}
                            </td>

                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <form action="{{ route('admin.reviews.delete', $r->id) }}" method="POST" @submit.prevent="$dispatch('open-confirm', { message: 'Hapus ulasan ini? Ulasan akan disembunyikan dari halaman produk.', action: () => $el.submit() })">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg border border-red-200 transition-colors cursor-pointer">
                                        Hapus Ulasan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center mx-auto text-amber-500">
                                        <svg class="w-6 h-6 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum Ada Ulasan Produk</p>
                                    <p class="text-xs text-slate-400">Belum ada ulasan atau penilaian yang cocok dengan filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
