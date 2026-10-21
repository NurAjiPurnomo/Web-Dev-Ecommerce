@extends('layouts.app')

@section('title', 'Pusat Notifikasi - Toko Online')

@section('content')
<div x-data="notificationCenterApp()" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

    <!-- Page Header & Breadcrumb -->
    <div class="space-y-4">
        <div>
            <!-- Breadcrumb -->
            <nav class="text-xs text-slate-500 flex items-center gap-1.5 mb-1.5">
                <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
                <span>&gt;</span>
                <span class="text-slate-900 font-semibold">Pusat Notifikasi</span>
            </nav>
            
            <!-- Page Title & Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-700 text-white flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <span>Pusat Notifikasi</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Informasi lengkap status pesanan, update pengiriman, pengajuan retur, dan promo terbaru untuk Anda.
                    </p>
                </div>

                @if($counts['unread'] > 0)
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="readAll()"
                            :disabled="loadingAll"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-blue-700 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl shadow-2xs transition-all cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="loadingAll ? 'Memproses...' : 'Tandai Semua Dibaca'"></span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Filter Status Tabs -->
        <div class="bg-white border border-slate-200 rounded-2xl p-1.5 shadow-xs">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-1 text-xs">
                <a 
                    href="{{ route('notifications.index', ['filter' => 'semua']) }}" 
                    class="w-full px-3 py-2.5 rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $filter === 'semua' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span>Semua</span>
                    <span class="ml-0.5 {{ $filter === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }} text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">
                        {{ $counts['semua'] }}
                    </span>
                </a>

                <a 
                    href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                    class="w-full px-3 py-2.5 rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $filter === 'unread' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium' }}"
                >
                    <span class="w-2 h-2 rounded-full {{ $filter === 'unread' ? 'bg-white' : 'bg-blue-600' }}"></span>
                    <span>Belum Dibaca</span>
                    @if($counts['unread'] > 0)
                        <span class="ml-0.5 {{ $filter === 'unread' ? 'bg-white/20 text-white' : 'bg-red-100 text-red-700' }} text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">
                            {{ $counts['unread'] }}
                        </span>
                    @endif
                </a>

                <a 
                    href="{{ route('notifications.index', ['filter' => 'orders']) }}" 
                    class="w-full px-3 py-2.5 rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $filter === 'orders' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Pesanan &amp; Retur</span>
                    <span class="ml-0.5 {{ $filter === 'orders' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }} text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">
                        {{ $counts['orders'] }}
                    </span>
                </a>

                <a 
                    href="{{ route('notifications.index', ['filter' => 'promo']) }}" 
                    class="w-full px-3 py-2.5 rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $filter === 'promo' ? 'bg-blue-700 text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-medium' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>Promo &amp; Voucher</span>
                    <span class="ml-0.5 {{ $filter === 'promo' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }} text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">
                        {{ $counts['promo'] }}
                    </span>
                </a>
            </div>
        </div>
    </div>

    <!-- Notification Cards List -->
    <div class="space-y-3.5">
        @forelse($notifications as $notif)
            @php
                $isUnread = !$notif->is_read;
                $text = $notif->title . ' ' . $notif->content;
                
                // Extract invoice if present
                $invoice = null;
                if (preg_match('/(INV[-\/][A-Za-z0-9\-\/]+)/i', $text, $invMatches)) {
                    $invoice = $invMatches[1];
                }

                // Extract waybill/resi if present
                $waybill = null;
                if (preg_match('/(?:Resi|waybill)[:\s]+([A-Za-z0-9\-]+)/i', $text, $wbMatches)) {
                    $waybill = $wbMatches[1];
                }

                // Determine category icon
                $iconType = 'bell';
                if (str_contains(strtolower($text), 'retur') || str_contains(strtolower($text), 'refund') || str_contains(strtolower($text), 'komplain')) {
                    $iconType = 'return';
                } elseif (str_contains(strtolower($text), 'resi') || str_contains(strtolower($text), 'kirim') || str_contains(strtolower($text), 'kurir')) {
                    $iconType = 'shipping';
                } elseif (str_contains(strtolower($text), 'pesanan') || str_contains(strtolower($text), 'order') || $invoice) {
                    $iconType = 'order';
                } elseif (str_contains(strtolower($text), 'promo') || str_contains(strtolower($text), 'diskon') || str_contains(strtolower($text), 'voucher')) {
                    $iconType = 'promo';
                }

                // Bersihkan emoji sisa di awal judul
                $cleanTitle = trim(preg_replace('/^[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]\s*/u', '', $notif->title));

                // Skema warna badge kategori yang konsisten dan profesional
                $badgeStyle = match($iconType) {
                    'return'   => $isUnread ? 'bg-amber-600 text-white shadow-2xs' : 'bg-amber-50 text-amber-700 border border-amber-200/90',
                    'shipping' => $isUnread ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/90',
                    'order'    => $isUnread ? 'bg-blue-600 text-white shadow-2xs' : 'bg-blue-50 text-blue-700 border border-blue-200/90',
                    'promo'    => $isUnread ? 'bg-purple-600 text-white shadow-2xs' : 'bg-purple-50 text-purple-700 border border-purple-200/90',
                    default    => $isUnread ? 'bg-slate-700 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 border border-slate-200/90',
                };
            @endphp

            <div 
                id="notif-card-{{ $notif->id }}"
                class="bg-white rounded-2xl border transition-all duration-200 overflow-hidden shadow-xs hover:shadow-md {{ $isUnread ? 'border-blue-400 bg-blue-50/30 ring-1 ring-blue-300' : 'border-slate-200 hover:border-slate-300' }}"
            >
                <div class="p-4 sm:p-5">
                    <div class="flex items-start gap-3.5">
                        
                        <!-- Category Badge Icon (Konsisten Sesuai Kategori Notifikasi) -->
                        <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center shadow-2xs {{ $badgeStyle }}">
                            @if($iconType === 'return')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            @elseif($iconType === 'shipping')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                            @elseif($iconType === 'order')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            @elseif($iconType === 'promo')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            @endif
                        </div>

                        <!-- Content Body -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug flex items-center gap-2">
                                    <span>{{ $cleanTitle }}</span>
                                    @if($isUnread)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-600 text-white">
                                            BARU
                                        </span>
                                    @endif
                                </h3>
                                <div class="text-[11px] text-slate-400 font-medium shrink-0">
                                    {{ $notif->created_at->format('d M Y, H:i') }} WIB ({{ $notif->created_at->diffForHumans() }})
                                </div>
                            </div>

                            <!-- COMPLETE FULL MESSAGE TEXT (NO LINE CLAMP) -->
                            <div class="mt-2.5 p-3.5 bg-slate-50/80 rounded-xl border border-slate-100">
                                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line select-text">
                                    {{ $notif->content }}
                                </p>
                            </div>

                            <!-- Action Bar & Quick Links (Clean Human E-Commerce Style) -->
                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                                <div class="flex flex-wrap items-center gap-4">
                                    @if($invoice)
                                        <a 
                                            href="{{ route('orders', ['search' => $invoice]) }}" 
                                            class="font-semibold text-blue-700 hover:text-blue-800 hover:underline inline-flex items-center gap-1 group"
                                        >
                                            <span>{{ $iconType === 'return' ? 'Lihat Detail Retur' : 'Lihat Detail Pesanan' }}</span>
                                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if($waybill)
                                        <a 
                                            href="{{ route('tracking.public', ['waybill' => $waybill]) }}" 
                                            class="font-medium text-slate-600 hover:text-slate-900 hover:underline inline-flex items-center gap-1.5 group"
                                        >
                                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                            </svg>
                                            <span>Lacak Pengiriman</span>
                                            <svg class="w-3 h-3 text-slate-400 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if($iconType === 'promo')
                                        <a 
                                            href="{{ route('promo') }}" 
                                            class="font-semibold text-purple-700 hover:text-purple-800 hover:underline inline-flex items-center gap-1 group"
                                        >
                                            <span>Lihat Promo &amp; Kupon</span>
                                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>

                                <div>
                                    @if($isUnread)
                                        <button 
                                            type="button" 
                                            @click="markRead({{ $notif->id }})"
                                            class="text-[11px] font-semibold text-slate-500 hover:text-blue-700 hover:underline inline-flex items-center gap-1 cursor-pointer transition-colors"
                                        >
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Tandai Dibaca</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Sudah Dibaca</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center space-y-3 shadow-xs">
                <div class="w-16 h-16 mx-auto rounded-full bg-blue-50 text-blue-700 flex items-center justify-center shadow-2xs">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h4 class="font-bold text-slate-900 text-base">Tidak ada notifikasi</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                    @if($filter === 'unread')
                        Hore! Semua notifikasi Anda sudah dibaca.
                    @elseif($filter === 'orders')
                        Belum ada pembaruan terkait pesanan atau pengajuan retur Anda saat ini.
                    @elseif($filter === 'promo')
                        Belum ada info promo khusus saat ini. Nantikan penawaran menarik berikutnya!
                    @else
                        Belum ada notifikasi apapun untuk akun Anda.
                    @endif
                </p>
                <div class="pt-2">
                    <a href="{{ route('catalog') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-2xs transition-colors">
                        Jelajahi Produk Toko
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    @if($notifications->hasPages())
        <div class="pt-4 flex justify-center">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

<script>
function notificationCenterApp() {
    return {
        loadingAll: false,
        async markRead(id) {
            try {
                const res = await fetch(`/notifications/${id}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data && data.success) {
                    const card = document.getElementById('notif-card-' + id);
                    if (card) {
                        card.classList.remove('border-blue-400', 'bg-blue-50/30', 'ring-1', 'ring-blue-300');
                        card.classList.add('border-slate-200');
                    }
                    window.location.reload();
                }
            } catch(e) {
                console.error(e);
            }
        },
        async readAll() {
            this.loadingAll = true;
            try {
                const res = await fetch(`{{ route('notifications.readAll') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data && data.success) {
                    window.location.reload();
                }
            } catch(e) {
                console.error(e);
                window.location.reload();
            } finally {
                this.loadingAll = false;
            }
        }
    };
}
</script>
@endsection
