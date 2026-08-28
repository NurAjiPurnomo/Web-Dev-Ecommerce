<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\OtpCode;
use App\Mail\PasswordResetMail;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan Halaman Form Lupa Kata Sandi (Input Email).
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Proses Permintaan Reset Kata Sandi & Kirim Kode OTP.
     */
    public function sendResetOtp(Request $request)
    {
        // Normalisasi email ke lowercase sebelum validasi
        $email = strtolower(trim($request->input('email', '')));
        $request->merge(['email' => $email]);

        $request->validate([
            'email' => [
                'required',
                'email:rfc',
                'regex:/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/',
                'exists:users,email',
            ],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid. Contoh: nama@gmail.com',
            'email.regex'    => 'Email harus menggunakan huruf kecil semua (tidak boleh ada huruf kapital).',
            'email.exists'   => 'Alamat email tidak terdaftar di sistem Toko Online. Periksa kembali atau daftar akun baru.',
        ]);

        // Hapus kode OTP reset lama jika ada
        OtpCode::where('email', $email)->delete();

        // Buat kode OTP 6-digit baru
        $code = sprintf('%06d', mt_rand(100000, 999999));
        OtpCode::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // Simpan email sementara di session
        session(['pending_reset_email' => $email]);

        // Kirim email OTP Reset
        try {
            Mail::to($email)->send(new PasswordResetMail($code));
            session()->flash('success', 'Kode OTP reset kata sandi telah dikirimkan ke email: ' . $email);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email Reset OTP: ' . $e->getMessage());
            session()->flash('info', 'Kode OTP reset dibuat! (Mode Pengujian: Kode OTP kamu adalah ' . $code . '). Masukkan 16-digit Sandi Aplikasi di .env agar terkirim ke email.');
        }

        return redirect()->route('password.reset');
    }

    /**
     * Tampilkan Halaman Masukkan Kode OTP & Kata Sandi Baru.
     */
    public function showResetForm(Request $request)
    {
        $email = session('pending_reset_email') ?? $request->query('email');

        if (!$email) {
            return redirect()->route('password.request')
                ->with('info', 'Silakan masukkan email kamu terlebih dahulu untuk lupa kata sandi.');
        }

        return view('auth.reset-password', [
            'email' => $email,
        ]);
    }

    /**
     * Proses Verifikasi Kode OTP & Simpan Kata Sandi Baru.
     */
    public function resetPassword(Request $request)
    {
        $email = session('pending_reset_email') ?? strtolower(trim($request->input('email', '')));

        if (!$email) {
            return redirect()->route('password.request');
        }

        // Ambil kode OTP (bisa dari input terpisah `otp_1`, `otp_2`, dst, atau string `code`)
        $otpInput = $request->input('code');
        if (is_array($request->input('otp'))) {
            $otpInput = implode('', $request->input('otp'));
        }

        $request->merge([
            'code'  => $otpInput,
            'email' => $email,
        ]);

        $request->validate([
            'email'                 => ['required', 'email', 'exists:users,email'],
            'code'                  => ['required', 'digits:6'],
            'password'              => ['required', 'min:6', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'email.required'                 => 'Email wajib diisi.',
            'email.exists'                   => 'Akun dengan email ini tidak ditemukan.',
            'code.required'                  => 'Kode OTP 6 digit wajib diisi.',
            'code.digits'                    => 'Kode OTP harus terdiri dari 6 angka.',
            'password.required'              => 'Kata sandi baru wajib diisi.',
            'password.min'                   => 'Kata sandi minimal 6 karakter.',
            'password.confirmed'             => 'Konfirmasi kata sandi baru tidak cocok. Pastikan kedua kata sandi sama.',
            'password_confirmation.required' => 'Konfirmasi kata sandi baru wajib diisi.',
        ]);

        // Cek keabsahan kode OTP
        $otpRecord = OtpCode::where('email', $email)
            ->where('code', $otpInput)
            ->where('expires_at', '>=', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return back()->withInput($request->only('email', 'code'))
                ->withErrors(['code' => 'Kode OTP salah atau sudah kadaluarsa. Silakan minta kode baru.']);
        }

        // Update kata sandi user
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Hapus kode OTP yang telah digunakan
        OtpCode::where('email', $email)->delete();
        $request->session()->forget('pending_reset_email');

        return redirect()->route('login')
            ->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan masuk menggunakan kata sandi baru.');
    }

    /**
     * Kirim Ulang Kode OTP Reset.
     */
    public function resendResetOtp(Request $request)
    {
        $email = session('pending_reset_email') ?? strtolower(trim($request->input('email', '')));

        if (!$email) {
            return redirect()->route('password.request');
        }

        // Cek jeda pengiriman minimal 60 detik
        $recentOtp = OtpCode::where('email', $email)
            ->where('created_at', '>=', Carbon::now()->subSeconds(60))
            ->first();

        if ($recentOtp) {
            $remaining = 60 - Carbon::now()->diffInSeconds($recentOtp->created_at);
            return back()->with('info', 'Tunggu ' . $remaining . ' detik sebelum meminta kode OTP reset baru.');
        }

        // Hapus OTP reset lama
        OtpCode::where('email', $email)->delete();

        // Buat OTP reset baru
        $code = sprintf('%06d', mt_rand(100000, 999999));
        OtpCode::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // Kirim email
        try {
            Mail::to($email)->send(new PasswordResetMail($code));
            $msg = 'Kode OTP reset baru berhasil dikirim ke ' . $email;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email Reset OTP: ' . $e->getMessage());
            $msg = 'Mode Pengujian: Kode OTP reset baru kamu adalah ' . $code;
        }

        return back()->with('success', $msg);
    }
}
