<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show the Login Page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    /**
     * Process Login submission.
     */
    public function login(Request $request)
    {
        $loginInput = trim($request->input('login', $request->input('email', '')));
        $password = $request->input('password');

        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'min:6'],
        ], [
            'login.required'    => 'Alamat email atau nomor HP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min'      => 'Kata sandi minimal 6 karakter.',
        ]);

        // Rate limiting key: login attempt throttle (max 5 attempts per minute)
        $throttleKey = 'login_attempt:' . strtolower($loginInput) . '|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()
                ->withInput($request->only('login', 'email', 'remember'))
                ->withErrors(['login' => "Terlalu banyak percobaan login yang gagal (maksimal 5 kali). Silakan coba lagi dalam {$seconds} detik."]);
        }

        // Tentukan apakah input berupa email atau nomor HP
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $user = null;

        if ($isEmail) {
            $normalizedEmail = strtolower($loginInput);
            $user = User::where('email', $normalizedEmail)->first();
        } else {
            // Pembersihan dan pencarian nomor HP (08xx, 628xx, +628xx)
            $cleanPhone = preg_replace('/[^0-9]/', '', $loginInput);
            $altPhone = '';
            if (str_starts_with($cleanPhone, '08')) {
                $altPhone = '628' . substr($cleanPhone, 2);
            } elseif (str_starts_with($cleanPhone, '628')) {
                $altPhone = '08' . substr($cleanPhone, 3);
            }

            $user = User::where('phone', $cleanPhone)
                ->orWhere('phone', $loginInput)
                ->when($altPhone, fn($q) => $q->orWhere('phone', $altPhone))
                ->first();
        }

        if ($user && Hash::check($password, $user->password)) {
            // 1. Cek Status Aktif / Suspend Akun User
            if ($user->status && in_array(strtolower((string)$user->status), ['suspended', 'inactive', 'blocked', 'nonaktif', '0', 'disabled'])) {
                \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
                return back()
                    ->withInput($request->only('login', 'email', 'remember'))
                    ->withErrors(['login' => 'Akun Anda sedang ditangguhkan atau nonaktif. Silakan hubungi Customer Service.']);
            }

            // 2. Cek apakah pengguna adalah Admin
            if ($user->is_admin) {
                \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
                return back()
                    ->withInput($request->only('login', 'email', 'remember'))
                    ->withErrors(['login' => 'Akun Admin tidak bisa masuk dari sini. Silakan masuk melalui halaman Admin.']);
            }

            // Login Berhasil -> Reset Rate Limiter & Regenerate Session
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

            session(['user' => $user->toArray()]);

            return redirect()->intended(route('home'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        // Login Gagal -> Tambahkan Hit ke Rate Limiter
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        return back()
            ->withInput($request->only('login', 'email', 'remember'))
            ->withErrors(['login' => 'Email / Nomor HP atau kata sandi yang Anda masukkan salah. Periksa kembali dan coba lagi.']);
    }

    /**
     * Show the Register Page.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.register');
    }

    /**
     * Process Register submission.
     */
    public function register(Request $request)
    {
        // Normalisasi email ke lowercase sebelum validasi
        $request->merge(['email' => strtolower(trim($request->email))]);

        $request->validate([
            'name'                  => ['required', 'string', 'min:3', 'max:100'],
            'email'                 => [
                'required',
                'email:rfc',
                'regex:/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/',
                'unique:users,email',
            ],
            'phone'                 => [
                'required',
                'digits_between:10,13',
                'regex:/^(08|628|\+628)[0-9]{7,11}$/',
            ],
            'password'              => ['required', 'min:6', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'name.required'         => 'Nama lengkap wajib diisi.',
            'name.min'              => 'Nama minimal 3 karakter.',
            'name.max'              => 'Nama maksimal 100 karakter.',
            'email.required'        => 'Alamat email wajib diisi.',
            'email.email'           => 'Format email tidak valid. Contoh: nama@gmail.com',
            'email.regex'           => 'Email harus menggunakan huruf kecil semua (tidak boleh ada huruf kapital).',
            'email.unique'          => 'Email ini sudah terdaftar. Gunakan email lain atau langsung masuk.',
            'phone.required'        => 'Nomor WhatsApp/HP wajib diisi.',
            'phone.digits_between'  => 'Nomor HP harus terdiri dari 10 sampai 13 angka (tanpa tanda + atau spasi).',
            'phone.regex'           => 'Nomor HP tidak valid. Harus diawali 08, 628, atau +628. Contoh: 081234567890',
            'password.required'     => 'Kata sandi wajib diisi.',
            'password.min'          => 'Kata sandi minimal 6 karakter.',
            'password.confirmed'    => 'Konfirmasi kata sandi tidak cocok. Pastikan kedua kata sandi sama.',
            'password_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
        ]);

        $user = User::create([
            'name'     => trim($request->name),
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Generate & kirim email OTP
        OtpController::sendOtpForEmail($user->email);

        // Simpan email sementara di session untuk halaman OTP
        session(['pending_otp_email' => $user->email]);

        return redirect()->route('otp.verify');
    }

    /**
     * Redirect ke Google OAuth.
     */
    public function googleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Callback dari Google OAuth.
     */
    public function googleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = strtolower($googleUser->email);

            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $email)
                ->first();

            if ($user) {
                $updates = [];
                if (!$user->google_id) {
                    $updates['google_id'] = $googleUser->id;
                }
                if (!$user->email_verified_at) {
                    $updates['email_verified_at'] = \Carbon\Carbon::now();
                }
                if (!empty($updates)) {
                    $user->update($updates);
                }
            } else {
                $user = User::create([
                    'name'              => $googleUser->name ?? 'Pengguna Google',
                    'email'             => $email,
                    'google_id'         => $googleUser->id,
                    'email_verified_at' => \Carbon\Carbon::now(),
                    'password'          => null,
                ]);
            }

            // 1. Cek Status Aktif / Suspend Akun User
            if ($user->status && in_array(strtolower((string)$user->status), ['suspended', 'inactive', 'blocked', 'nonaktif', '0', 'disabled'])) {
                return redirect()->route('login')
                    ->withErrors(['login' => 'Akun Anda sedang ditangguhkan atau nonaktif. Silakan hubungi Customer Service.']);
            }

            // 2. Cek apakah pengguna adalah Admin
            if ($user->is_admin) {
                return redirect()->route('login')
                    ->withErrors(['login' => 'Akun Admin tidak bisa masuk dari sini. Silakan masuk melalui halaman Admin.']);
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            session(['user' => $user->toArray()]);

            return redirect()->intended(route('home'))
                ->with('success', 'Berhasil masuk dengan akun Google: ' . $user->name);
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['login' => 'Gagal masuk dengan Google: ' . $e->getMessage()]);
        }
    }


    /**
     * Process Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Kamu berhasil keluar dari akun.');
    }
}

