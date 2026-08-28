@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'biodata' }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

    <!-- Page Header & Breadcrumb -->
    <div class="mb-6 space-y-1">
        <nav class="flex text-xs text-slate-500 gap-2 items-center">
            <a href="{{ route('home') }}" class="hover:text-blue-700">Beranda</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Profil Saya</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
            Pengaturan Akun &amp; Biodata
        </h1>
    </div>

    <!-- Flash Alert Messages -->
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-xs sm:text-sm text-emerald-800 font-medium flex items-center gap-2.5">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 space-y-1">
            @foreach($errors->all() as $error)
                <p class="text-xs sm:text-sm text-red-700 font-medium flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $error }}
                </p>
            @endforeach
        </div>
    @endif

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- LEFT SIDEBAR (~30% / 4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- User Avatar & Card Header -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs text-center space-y-3">
                <div class="relative w-20 h-20 rounded-full bg-blue-700 text-white font-extrabold text-3xl flex items-center justify-center mx-auto shadow-md border-2 border-white">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    <div class="absolute bottom-0 right-0 w-6 h-6 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center text-white" title="Email Terverifikasi">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <h2 class="font-extrabold text-slate-900 text-base sm:text-lg">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    <span class="inline-block bg-blue-50 text-blue-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full mt-1 border border-blue-200">
                        Member Toko Online
                    </span>
                </div>
            </div>

            <!-- Navigation Tabs Sidebar -->
            <div class="bg-white border border-slate-200 rounded-2xl p-2 shadow-xs space-y-1">
                <button 
                    type="button" 
                    @click="activeTab = 'biodata'"
                    :class="activeTab === 'biodata' ? 'bg-blue-50 text-blue-700 font-bold border-l-4 border-blue-700' : 'text-slate-700 hover:bg-slate-50 font-medium'"
                    class="w-full text-left px-4 py-3 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-3 cursor-pointer"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Biodata Diri</span>
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'alamat'"
                    :class="activeTab === 'alamat' ? 'bg-blue-50 text-blue-700 font-bold border-l-4 border-blue-700' : 'text-slate-700 hover:bg-slate-50 font-medium'"
                    class="w-full text-left px-4 py-3 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-3 cursor-pointer"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Alamat Pengiriman Utama</span>
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'keamanan'"
                    :class="activeTab === 'keamanan' ? 'bg-blue-50 text-blue-700 font-bold border-l-4 border-blue-700' : 'text-slate-700 hover:bg-slate-50 font-medium'"
                    class="w-full text-left px-4 py-3 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-3 cursor-pointer"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Keamanan &amp; Kata Sandi</span>
                </button>
            </div>

        </div>

        <!-- RIGHT CONTENT AREA (~70% / 8 cols) -->
        <div class="lg:col-span-8">

            <!-- TAB 1: BIODATA DIRI -->
            <div x-show="activeTab === 'biodata'" class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Ubah Biodata Diri</h2>
                    <p class="text-xs text-slate-500">Kelola informasi profil akun Toko Online Anda</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            value="{{ old('name', $user->name) }}" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Alamat Email (Terverifikasi)
                            </label>
                            <input 
                                type="email" 
                                value="{{ $user->email }}" 
                                disabled 
                                class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-500 cursor-not-allowed"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Nomor WhatsApp / HP <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="phone" 
                                value="{{ old('phone', $user->phone) }}" 
                                required 
                                placeholder="081234567890"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                            <select 
                                name="gender" 
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none cursor-pointer"
                            >
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('gender', $user->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('gender', $user->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                            <input 
                                type="date" 
                                name="birth_date" 
                                value="{{ old('birth_date', $user->birth_date) }}" 
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                            >
                        </div>
                    </div>

                    <!-- Hidden Keep Address -->
                    <input type="hidden" name="address" value="{{ $user->address }}">
                    <input type="hidden" name="city" value="{{ $user->city }}">
                    <input type="hidden" name="province" value="{{ $user->province }}">
                    <input type="hidden" name="postal_code" value="{{ $user->postal_code }}">

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-colors shadow-2xs cursor-pointer"
                        >
                            Simpan Perubahan Biodata
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: ALAMAT PENGIRIMAN UTAMA -->
            <div x-show="activeTab === 'alamat'" class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Alamat Pengiriman Utama</h2>
                    <p class="text-xs text-slate-500">Alamat ini akan otomatis terpasang saat Anda melakukan pembelian / checkout</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Hidden Keep Biodata -->
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="phone" value="{{ $user->phone }}">
                    <input type="hidden" name="gender" value="{{ $user->gender }}">
                    <input type="hidden" name="birth_date" value="{{ $user->birth_date }}">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Alamat Lengkap (Jalan, RT/RW, No. Rumah, Kelurahan) <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            name="address" 
                            rows="3" 
                            required
                            placeholder="Alamat lengkap tujuan pengiriman"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >{{ old('address', $user->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten</label>
                            <input 
                                type="text" 
                                name="city" 
                                value="{{ old('city', $user->city) }}" 
                                placeholder="Jakarta Selatan"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi</label>
                            <input 
                                type="text" 
                                name="province" 
                                value="{{ old('province', $user->province) }}" 
                                placeholder="DKI Jakarta"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Pos</label>
                            <input 
                                type="text" 
                                name="postal_code" 
                                value="{{ old('postal_code', $user->postal_code) }}" 
                                placeholder="12190"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-colors shadow-2xs cursor-pointer"
                        >
                            Simpan Alamat Pengiriman Utama
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 3: KEAMANAN & KATA SANDI -->
            <div x-show="activeTab === 'keamanan'" class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Keamanan &amp; Ubah Kata Sandi</h2>
                    <p class="text-xs text-slate-500">Perbarui kata sandi akun Anda secara berkala untuk menjaga keamanan</p>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Kata Sandi Saat Ini <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="current_password" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Kata Sandi Baru (Min. 6 Karakter) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none"
                        >
                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-colors shadow-2xs cursor-pointer"
                        >
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
