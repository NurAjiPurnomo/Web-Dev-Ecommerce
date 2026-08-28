<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Portal Administrator - Toko Online</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 sm:p-8 space-y-6 border border-slate-200">
        
        <!-- Header Logo & Title -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-xl bg-blue-700 text-white font-bold text-xl flex items-center justify-center mx-auto shadow-lg">
                TO
            </div>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight">Portal Administrator</h1>
            <p class="text-xs sm:text-sm text-slate-500">Masuk untuk mengelola produk, pesanan, dan toko online</p>
        </div>

        <!-- Alert Error -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-3.5 space-y-1">
                @foreach($errors->all() as $error)
                    <p class="text-xs text-red-700 font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $error }}
                    </p>
                @endforeach
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-800 font-medium">
                {{ session('info') }}
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4" x-data="{ showPass: false }">
            @csrf

            <!-- Email Input -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-slate-700">Email Administrator</label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    required 
                    value="{{ old('email', 'admin@toko.com') }}"
                    placeholder="admin@toko.com"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none transition-colors"
                >
            </div>

            <!-- Password Input -->
            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi Admin</label>
                <div class="relative">
                    <input 
                        :type="showPass ? 'text' : 'password'" 
                        name="password" 
                        id="password" 
                        required 
                        value="admin123"
                        placeholder="••••••••"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-700 focus:outline-none transition-colors pr-10"
                    >
                    <button 
                        type="button" 
                        @click="showPass = !showPass" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
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

            <!-- Demo Hint Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-900 space-y-0.5">
                <span class="font-bold block">💡 Kredensial Default Admin:</span>
                <p class="font-mono text-[11px] text-blue-800">Email: <strong>admin@toko.com</strong> | Pass: <strong>admin123</strong></p>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold text-xs sm:text-sm py-3 rounded-xl transition-all shadow-md cursor-pointer flex items-center justify-center gap-2"
            >
                <span>Masuk ke Dashboard Admin</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <div class="text-center">
            <a href="{{ route('home') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">← Kembali ke Front-End Toko</a>
        </div>

    </div>

</body>
</html>
