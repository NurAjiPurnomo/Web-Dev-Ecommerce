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
                Lupa Kata Sandi?
            </h1>
            <p class="text-xs text-slate-500 leading-relaxed">
                Masukkan alamat email yang terdaftar pada akun Toko Online Anda. Kami akan mengirimkan <strong>kode OTP 6-digit</strong> untuk mengatur ulang kata sandi.
            </p>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 text-xs text-emerald-800 font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-xs text-blue-800 font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('info') }}
            </div>
        @endif

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

        <!-- Form Request OTP -->
        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            
            <!-- Email Field -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-slate-700">Alamat Email Terdaftar <span class="text-red-500">*</span></label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    autocomplete="email"
                    value="{{ old('email') }}"
                    placeholder="Alamat Email Terdaftar"
                    style="text-transform: lowercase;"
                    oninput="this.value = this.value.toLowerCase()"
                    class="w-full px-3.5 py-2.5 bg-white border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition-colors"
                >
                @error('email')
                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm py-2.5 rounded-lg transition-colors shadow-2xs cursor-pointer flex items-center justify-center gap-2 mt-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Kirim Kode OTP Reset
            </button>
        </form>

        <!-- Footer Link -->
        <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-100">
            Ingat kata sandi Anda? 
            <a href="{{ route('login') }}" class="text-blue-700 font-bold hover:underline">Kembali ke Halaman Masuk</a>
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
.vld-tip.vld-ok { background: #064e3b; }
.vld-tip.vld-ok::before { border-bottom-color: #064e3b; }
.vld-tip.vld-show { opacity: 1; transform: translateY(0); }
</style>
@endpush

@push('scripts')
<script>
(function(){
    function tip(field, msg, ok) {
        var wrap = field.parentElement;
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
        var el = field.parentElement.querySelector('.vld-tip');
        if (el) el.classList.remove('vld-show');
    }

    document.addEventListener('DOMContentLoaded', function(){
        var fEmail = document.getElementById('email');
        if (fEmail) {
            fEmail.parentElement.style.position = 'relative';
            fEmail.addEventListener('input', function(){
                var v = this.value.trim();
                if (!v) return tip(this, 'Alamat email wajib diisi.', false);
                if (v !== v.toLowerCase()) return tip(this, 'Email tidak boleh ada huruf kapital.', false);
                if (!v.includes('@')) return tip(this, 'Email harus ada tanda @. Contoh: nama@gmail.com', false);
                var parts = v.split('@');
                if (!parts[0]) return tip(this, 'Nama pengguna sebelum @ tidak boleh kosong.', false);
                if (!parts[1] || !parts[1].includes('.')) return tip(this, 'Domain tidak valid. Contoh: @gmail.com', false);

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
                if (tld.length < 2) return tip(this, 'Ekstensi domain tidak valid. Contoh: .com / .id', false);
                tip(this, '✓ Format email valid.', true);
                var t=this; setTimeout(function(){ hide(t); }, 1400);
            });
            fEmail.addEventListener('blur', function(){ var t=this; setTimeout(function(){ hide(t); }, 250); });
        }
    });
})();
</script>
@endpush

@endsection
