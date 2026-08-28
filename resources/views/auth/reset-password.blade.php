@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8" x-data="resetPasswordForm()">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">

        <!-- Header -->
        <div class="text-center space-y-1.5">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center mx-auto shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight pt-1">
                Atur Ulang Kata Sandi
            </h1>
            <p class="text-xs text-slate-500 leading-relaxed">
                Masukkan kode OTP 6-digit yang dikirim ke <br>
                <strong class="text-blue-700 font-bold">{{ $email }}</strong>
            </p>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 text-xs text-emerald-800 font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-xs text-blue-800 font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 space-y-1">
                @foreach($errors->all() as $error)
                    <p class="text-xs text-red-700 font-medium flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $error }}</span>
                    </p>
                @endforeach
            </div>
        @endif

        <!-- Form Reset Password -->
        <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <!-- 6 Digit OTP Code Input Boxes -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 text-center">
                    Kode Verifikasi OTP (6 Digit) <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center justify-center gap-2 sm:gap-2.5">
                    <template x-for="(digit, index) in otp" :key="index">
                        <input 
                            type="text" 
                            maxlength="1" 
                            pattern="[0-9]*" 
                            inputmode="numeric"
                            x-model="otp[index]"
                            @input="handleInput(index, $event)"
                            @keydown.backspace="handleBackspace(index, $event)"
                            @paste="handlePaste($event)"
                            :id="'otp-' + index"
                            class="w-10 h-12 sm:w-11 sm:h-12 text-center text-lg sm:text-xl font-extrabold text-blue-700 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 transition-all shadow-2xs"
                            required
                        >
                    </template>
                </div>
                <input type="hidden" name="code" :value="otp.join('')">
                @error('code')
                    <p class="text-[11px] text-red-600 font-medium text-center">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password Field -->
            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi Baru <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input 
                        :type="showPass ? 'text' : 'password'" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Minimal 6 karakter"
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition-colors pr-10"
                    >
                    <button 
                        type="button" 
                        @click="showPass = !showPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
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
                @error('password')
                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm New Password Field -->
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700">Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input 
                        :type="showConfirmPass ? 'text' : 'password'" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required 
                        placeholder="Ulangi kata sandi baru"
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-700 focus:ring-1 focus:ring-blue-700 transition-colors pr-10"
                    >
                    <button 
                        type="button" 
                        @click="showConfirmPass = !showConfirmPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                    >
                        <svg x-show="!showConfirmPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showConfirmPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.044 10.044 0 012.122-.363c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.225-4.225L3 3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm py-3 rounded-xl transition-colors shadow-2xs cursor-pointer flex items-center justify-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Kata Sandi Baru
            </button>
        </form>

        <!-- Resend OTP Section -->
        <div class="text-center pt-3 border-t border-slate-100 space-y-2">
            <p class="text-xs text-slate-500">Tidak menerima kode OTP?</p>
            <form action="{{ route('password.resendOtp') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button 
                    type="submit" 
                    :disabled="countdown > 0"
                    :class="countdown > 0 ? 'opacity-50 cursor-not-allowed text-slate-400' : 'text-blue-700 font-bold hover:underline cursor-pointer'"
                    class="text-xs transition-colors"
                >
                    <span x-show="countdown > 0">Kirim Ulang Kode (<span x-text="countdown"></span> detik)</span>
                    <span x-show="countdown <= 0">Kirim Ulang Kode OTP Reset</span>
                </button>
            </form>
        </div>

    </div>
</div>

<script>
function resetPasswordForm() {
    return {
        otp: ['', '', '', '', '', ''],
        showPass: false,
        showConfirmPass: false,
        countdown: 60,
        init() {
            this.startTimer();
            this.$nextTick(() => {
                const el = document.getElementById('otp-0');
                if (el) el.focus();
            });
        },
        startTimer() {
            const timer = setInterval(() => {
                if (this.countdown > 0) {
                    this.countdown--;
                } else {
                    clearInterval(timer);
                }
            }, 1000);
        },
        handleInput(index, event) {
            const val = event.target.value;
            if (val && index < 5) {
                document.getElementById('otp-' + (index + 1)).focus();
            }
        },
        handleBackspace(index, event) {
            if (!this.otp[index] && index > 0) {
                document.getElementById('otp-' + (index - 1)).focus();
            }
        },
        handlePaste(event) {
            event.preventDefault();
            const pasteData = (event.clipboardData || window.clipboardData).getData('text').trim();
            if (/^\d{6}$/.test(pasteData)) {
                for (let i = 0; i < 6; i++) {
                    this.otp[i] = pasteData[i];
                }
                document.getElementById('otp-5').focus();
            }
        }
    }
}
</script>
@endsection
