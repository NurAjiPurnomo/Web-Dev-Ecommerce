@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">

        <!-- Brand Header -->
        <div class="text-center space-y-1.5">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mb-2">
                <div class="w-9 h-9 rounded-lg bg-blue-700 text-white font-extrabold text-xl flex items-center justify-center shadow-2xs">
                    T
                </div>
                <span class="text-xl font-black text-slate-900 tracking-tight">Toko<span class="text-blue-700">Online</span></span>
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Daftar Akun Baru
            </h1>
            <p class="text-xs text-slate-500">
                Buat akun Toko Online untuk menikmati promo dan kemudahan berbelanja
            </p>
        </div>

        <!-- Google SSO Button -->
        <div>
            <a 
                href="{{ route('auth.google') }}" 
                class="w-full bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-bold text-xs sm:text-sm py-2.5 px-4 rounded-lg flex items-center justify-center gap-3 transition-colors shadow-2xs cursor-pointer"
            >
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Daftar dengan Akun Google</span>
            </a>
        </div>

        <!-- Divider -->
        <div class="relative flex items-center justify-center">
            <div class="border-t border-slate-200 w-full"></div>
            <span class="bg-white px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider shrink-0">
                atau daftar dengan email
            </span>
            <div class="border-t border-slate-200 w-full"></div>
        </div>

        <!-- Form Register -->
        <form action="{{ route('register') }}" method="POST" class="space-y-4" x-data="{ showPass: false, showPassConfirm: false }">
            @csrf

            {{-- Error Summary --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="text-xs text-red-700 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            <!-- Full Name Field -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required 
                    minlength="3"
                    maxlength="100"
                    value="{{ old('name') }}"
                    placeholder="Nama Lengkap"
                    class="w-full px-3.5 py-2.5 bg-white border {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition-colors"
                >
                @error('name')
                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-slate-700">Alamat Email <span class="text-red-500">*</span></label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    autocomplete="email"
                    value="{{ old('email') }}"
                    placeholder="Alamat Email"
                    style="text-transform: lowercase;"
                    oninput="this.value = this.value.toLowerCase()"
                    class="w-full px-3.5 py-2.5 bg-white border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition-colors"
                >
                @error('email')
                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone Field -->
            <div class="space-y-1.5">
                <label for="phone" class="block text-xs font-bold text-slate-700">Nomor WhatsApp / HP <span class="text-red-500">*</span></label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    required 
                    minlength="10"
                    maxlength="13"
                    pattern="(08|628)[0-9]{7,11}"
                    value="{{ old('phone') }}"
                    placeholder="Nomor WhatsApp / HP"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="w-full px-3.5 py-2.5 bg-white border {{ $errors->has('phone') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition-colors"
                >
                @error('phone')
                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi</label>
                <div class="relative">
                    <input 
                        :type="showPass ? 'text' : 'password'" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Kata Sandi (Minimal 6 karakter)"
                        class="w-full px-3.5 py-2.5 bg-white border {{ $errors->has('password') ? 'border-red-400' : 'border-slate-300' }} rounded-lg text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition-colors pr-10"
                    >
                    <button 
                        type="button" 
                        @click="showPass = !showPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none"
                    >
                        <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.044 10.044 0 012.122-.363c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.225-4.225L3 3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Password Confirmation Field -->
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700">Konfirmasi Kata Sandi</label>
                <div class="relative">
                    <input 
                        :type="showPassConfirm ? 'text' : 'password'" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required 
                        placeholder="Konfirmasi Kata Sandi"
                        class="w-full px-3.5 py-2.5 bg-white border {{ $errors->has('password_confirmation') ? 'border-red-400' : 'border-slate-300' }} rounded-lg text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition-colors pr-10"
                    >
                    <button 
                        type="button" 
                        @click="showPassConfirm = !showPassConfirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none"
                    >
                        <svg x-show="!showPassConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.044 10.044 0 012.122-.363c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.225-4.225L3 3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Terms Checkbox -->
            <div class="flex items-start gap-2 pt-1">
                <input 
                    type="checkbox" 
                    id="terms" 
                    name="terms" 
                    required 
                    checked
                    class="w-4 h-4 text-blue-700 border-slate-300 rounded focus:ring-blue-700 mt-0.5"
                >
                <label for="terms" class="text-xs text-slate-600 font-medium leading-relaxed">
                    Saya menyetujui <a href="#" class="text-blue-700 font-bold hover:underline">Syarat & Ketentuan</a> serta <a href="#" class="text-blue-700 font-bold hover:underline">Kebijakan Privasi</a> Toko Online
                </label>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm py-2.5 rounded-lg transition-colors shadow-2xs cursor-pointer mt-2"
            >
                Buat Akun Sekarang
            </button>
        </form>

        <!-- Footer Link -->
        <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-100">
            Sudah memiliki akun Toko Online? 
            <a href="{{ route('login') }}" class="text-blue-700 font-bold hover:underline">Masuk ke Akun</a>
        </div>

    </div>
</div>

@push('styles')
<style>
.vld-tip {
    position: absolute;
    left: 0;
    top: calc(100% + 7px);
    z-index: 9999;
    font-size: 11.5px;
    font-weight: 500;
    line-height: 1.5;
    padding: 8px 12px 8px 10px;
    border-radius: 8px;
    max-width: 300px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.15);
    pointer-events: none;
    opacity: 0;
    transform: translateY(-4px);
    transition: opacity .15s ease, transform .15s ease;
    background: #1e293b;
    color: #f1f5f9;
    display: flex;
    align-items: flex-start;
    gap: 6px;
}
.vld-tip::before {
    content: '';
    position: absolute;
    top: -5px;
    left: 12px;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-bottom: 5px solid #1e293b;
}
.vld-tip.vld-ok {
    background: #064e3b;
}
.vld-tip.vld-ok::before { border-bottom-color: #064e3b; }
.vld-tip.vld-show {
    opacity: 1;
    transform: translateY(0);
}
</style>
@endpush

@push('scripts')
<script>
(function(){
    function tip(field, msg, ok) {
        var wrap = field.closest('.vld-wrap') || field.parentElement;
        var el = wrap.querySelector('.vld-tip');
        if (!el) {
            el = document.createElement('div');
            el.className = 'vld-tip';
            wrap.appendChild(el);
        }
        el.innerHTML = (ok ? '<span>&#10003;</span>' : '<span>&#9888;</span>') + '<span>' + msg + '</span>';
        el.className = 'vld-tip' + (ok ? ' vld-ok' : '') + ' vld-show';
    }
    function hide(field) {
        var wrap = field.closest('.vld-wrap') || field.parentElement;
        var el = wrap.querySelector('.vld-tip');
        if (el) { el.classList.remove('vld-show'); }
    }
    function wrap(field) { field.parentElement.style.position = 'relative'; }

    document.addEventListener('DOMContentLoaded', function(){

        /* ---- NAMA ---- */
        var fName = document.getElementById('name');
        if (fName) {
            wrap(fName);
            fName.addEventListener('input', function(){
                var v = this.value.trim();
                if (!v) return tip(this, 'Nama lengkap wajib diisi.', false);
                if (v.length < 3) return tip(this, 'Nama terlalu pendek, minimal 3 karakter. (sekarang: ' + v.length + ')', false);
                tip(this, 'Nama valid.', true);
                var t = this; setTimeout(function(){ hide(t); }, 1400);
            });
            fName.addEventListener('blur', function(){ var t=this; setTimeout(function(){ hide(t); },250); });
        }

        /* ---- EMAIL ---- */
        var fEmail = document.getElementById('email');
        if (fEmail) {
            wrap(fEmail);
            fEmail.addEventListener('input', function(){
                var v = this.value.trim();
                if (!v) return tip(this, 'Alamat email wajib diisi.', false);
                if (v !== v.toLowerCase()) return tip(this, 'Email tidak boleh ada huruf kapital.', false);
                if (!v.includes('@')) return tip(this, 'Email harus menyertakan tanda @.', false);
                var parts = v.split('@');
                if (!parts[0]) return tip(this, 'Nama pengguna sebelum @ tidak boleh kosong.', false);
                if (!parts[1] || !parts[1].length) return tip(this, 'Domain setelah @ tidak boleh kosong.', false);
                if (!parts[1].includes('.')) return tip(this, 'Format domain email belum lengkap.', false);

                // Whitelist TLD umum + deteksi typo
                var domainParts = parts[1].split('.');
                var tld = domainParts[domainParts.length - 1].toLowerCase();
                var knownTlds = ['com','net','org','id','co','io','info','biz','edu','gov','my','sg','au','uk','us','de','fr','jp','cn','in','app','dev','store','shop','online','site','web','tech','me','tv','cc','xyz','ac','go','or','sch','mil','int','name','pro'];
                var typoMap = {'comn':'com','coom':'com','ocm':'com','con':'com','comm':'com','cmo':'com','xom':'com','dom':'com','vom':'com','cam':'com','cpm':'com','col':'com','nett':'net','ney':'net','nte':'net','orgg':'org','prg':'org','ogr':'org','iid':'id','di':'id'};

                if (typoMap[tld]) {
                    return tip(this, '⚠️ Sepertinya ada typo — maksud kamu .' + typoMap[tld] + '? (bukan .' + tld + ')', false);
                }
                if (knownTlds.indexOf(tld) === -1 && tld.length > 5) {
                    return tip(this, '⚠️ Ekstensi ".' + tld + '" tidak dikenal. Periksa kembali.', false);
                }
                if (tld.length < 2) return tip(this, 'Ekstensi domain tidak valid.', false);
                if (!/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/.test(v)) return tip(this, 'Format email tidak valid.', false);
                tip(this, '✓ Format email valid.', true);
                var t=this; setTimeout(function(){ hide(t); }, 1400);
            });
            fEmail.addEventListener('blur', function(){ var t=this; setTimeout(function(){ hide(t); },250); });
        }

        /* ---- NO HP ---- */
        var fPhone = document.getElementById('phone');
        if (fPhone) {
            wrap(fPhone);
            fPhone.addEventListener('input', function(){
                var v = this.value.trim();
                if (!v) return tip(this, 'Nomor WhatsApp / HP wajib diisi.', false);
                if (!/^\d+$/.test(v)) return tip(this, 'Hanya boleh angka — tanpa spasi, +, atau strip.', false);
                if (!v.startsWith('08') && !v.startsWith('628')) return tip(this, 'Harus diawali 08.', false);
                if (v.length < 10) return tip(this, 'Nomor terlalu pendek, minimal 10 digit. (sekarang: ' + v.length + ')', false);
                if (v.length > 13) return tip(this, 'Nomor terlalu panjang, maksimal 13 digit.', false);
                tip(this, 'Nomor HP valid (' + v.length + ' digit).', true);
                var t=this; setTimeout(function(){ hide(t); }, 1400);
            });
            fPhone.addEventListener('blur', function(){ var t=this; setTimeout(function(){ hide(t); },250); });
        }

        /* ---- PASSWORD ---- */
        var fPass = document.getElementById('password');
        var fConf = document.getElementById('password_confirmation');
        if (fPass) {
            wrap(fPass);
            fPass.addEventListener('input', function(){
                var v = this.value;
                if (!v) return tip(this, 'Kata sandi wajib diisi.', false);
                if (v.length < 6) return tip(this, 'Kata sandi minimal 6 karakter. (sekarang: ' + v.length + ')', false);
                tip(this, 'Kata sandi cukup.', true);
                var t=this; setTimeout(function(){ hide(t); }, 1400);
                if (fConf && fConf.value) fConf.dispatchEvent(new Event('input'));
            });
            fPass.addEventListener('blur', function(){ var t=this; setTimeout(function(){ hide(t); },250); });
        }

        /* ---- KONFIRMASI PASSWORD ---- */
        if (fConf) {
            wrap(fConf);
            fConf.addEventListener('input', function(){
                var v = this.value;
                var p = fPass ? fPass.value : '';
                if (!v) return tip(this, 'Konfirmasi kata sandi wajib diisi.', false);
                if (v !== p) return tip(this, 'Kata sandi tidak cocok — periksa kembali.', false);
                tip(this, 'Kata sandi cocok.', true);
                var t=this; setTimeout(function(){ hide(t); }, 1400);
            });
            fConf.addEventListener('blur', function(){ var t=this; setTimeout(function(){ hide(t); },250); });
        }

    });
})();
</script>
@endpush

@endsection
