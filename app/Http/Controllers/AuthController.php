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
        // Normalisasi email ke lowercase sebelum validasi
        $request->merge(['email' => strtolower(trim($request->email))]);

        $request->validate([
            'email'    => ['required', 'email:rfc,dns', 'regex:/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/'],
            'password' => ['required', 'min:6'],
        ], [
            'email.required'  => 'Alamat email wajib diisi.',
            'email.email'     => 'Format email tidak valid. Contoh: nama@gmail.com',
            'email.regex'     => 'Email harus menggunakan huruf kecil semua (tidak boleh ada huruf kapital).',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min'      => 'Kata sandi minimal 6 karakter.',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->boolean('remember'))) {
            $user = Auth::user();
            
            if ($user->is_admin) {
                Auth::logout();
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['email' => 'Akun Admin tidak bisa masuk dari sini. Silakan masuk melalui halaman Admin.']);
            }

            $request->session()->regenerate();
            session(['user' => $user->toArray()]);

            return redirect()->intended(route('home'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'Email atau kata sandi salah. Periksa kembali dan coba lagi.']);
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

            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', strtolower($googleUser->email))
                ->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }
            } else {
                $user = User::create([
                    'name'      => $googleUser->name ?? 'Pengguna Google',
                    'email'     => strtolower($googleUser->email),
                    'google_id' => $googleUser->id,
                    'password'  => null,
                ]);
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->route('home')
                ->with('success', 'Berhasil masuk dengan akun Google: ' . $user->name);
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Gagal masuk dengan Google: ' . $e->getMessage()]);
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

