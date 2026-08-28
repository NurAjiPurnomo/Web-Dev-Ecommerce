@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">

        <!-- Brand Header -->
        <div class="text-center space-y-2">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mb-1">
                <div class="w-10 h-10 rounded-xl bg-blue-700 text-white font-extrabold text-xl flex items-center justify-center shadow-2xs">
                    T
                </div>
                <span class="text-xl font-black text-slate-900 tracking-tight">Toko<span class="text-blue-700">Online</span></span>
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Verifikasi Email Anda
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                Kami telah mengirimkan 6 digit kode OTP ke email:<br>
                <span class="font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded text-xs inline-block mt-1">{{ $email }}</span>
            </p>
        </div>

        <!-- Flash & Error Messages -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-xs text-emerald-800 font-medium flex items-start gap-2.5">
                <svg class="w-4 h-4 shrink-0 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-xs text-blue-800 font-medium flex items-start gap-2.5">
                <svg class="w-4 h-4 shrink-0 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-3.5 space-y-1">
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

        <!-- Form OTP 6 Digit -->
        <form action="{{ route('otp.verify.post') }}" method="POST" class="space-y-6" id="otp-form">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 text-center mb-3">
                    Masukkan Kode OTP (6 Digit)
                </label>
                
                <div class="flex items-center justify-between gap-1.5 sm:gap-2" id="otp-inputs">
                    @for($i = 0; $i < 6; $i++)
                        <input 
                            type="text" 
                            name="otp[]" 
                            maxlength="1" 
                            pattern="[0-9]*" 
                            inputmode="numeric"
                            required
                            autocomplete="off"
                            class="otp-box w-10 h-12 sm:w-12 sm:h-14 text-center font-extrabold text-lg sm:text-xl text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 focus:outline-none transition-all"
                        >
                    @endfor
                </div>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm py-3 rounded-xl transition-colors shadow-2xs cursor-pointer flex items-center justify-center gap-2"
            >
                <span>Verifikasi Kode OTP</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <!-- Kirim Ulang OTP -->
        <div class="pt-4 border-t border-slate-100 text-center space-y-2">
            <p class="text-xs text-slate-500">Tidak menerima kode verifikasi?</p>
            
            <form action="{{ route('otp.resend') }}" method="POST" id="resend-form">
                @csrf
                <button 
                    type="submit" 
                    id="resend-btn"
                    class="text-xs font-bold text-blue-700 hover:underline disabled:text-slate-400 disabled:no-underline disabled:cursor-not-allowed cursor-pointer transition-colors"
                >
                    Kirim Ulang Kode OTP <span id="timer-text"></span>
                </button>
            </form>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.otp-box');
    const form = document.getElementById('otp-form');

    // Auto-focus input pertama
    if (inputs.length > 0) {
        inputs[0].focus();
    }

    inputs.forEach((input, index) => {
        // Cuma boleh angka
        input.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            // Auto submit jika 6 digit terisi semua
            let allFilled = true;
            inputs.forEach(inp => { if (!inp.value) allFilled = false; });
            if (allFilled && index === inputs.length - 1) {
                form.submit();
            }
        });

        // Handle Backspace
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        // Handle Paste 6 digit
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            if (pastedData) {
                const digits = pastedData.split('');
                digits.forEach((digit, i) => {
                    if (inputs[i]) {
                        inputs[i].value = digit;
                    }
                });
                if (digits.length >= inputs.length) {
                    inputs[inputs.length - 1].focus();
                    form.submit();
                } else {
                    inputs[digits.length]?.focus();
                }
            }
        });
    });

    // Timer Kirim Ulang (60 detik)
    let secondsLeft = 60;
    const resendBtn = document.getElementById('resend-btn');
    const timerText = document.getElementById('timer-text');

    function updateTimer() {
        if (secondsLeft > 0) {
            resendBtn.disabled = true;
            timerText.textContent = `(${secondsLeft}s)`;
            secondsLeft--;
            setTimeout(updateTimer, 1000);
        } else {
            resendBtn.disabled = false;
            timerText.textContent = '';
        }
    }

    updateTimer();
});
</script>
@endpush
@endsection
