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
        $loginInput = trim($request->input('login', $request->input('email', '')));

        $request->validate([
            'login' => ['required', 'string'],
        ], [
            'login.required' => 'Alamat email atau nomor HP wajib diisi.',
        ]);

        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $user = null;

        if ($isEmail) {
            $user = User::where('email', strtolower($loginInput))->first();
        } else {
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

        if (!$user) {
            return back()
                ->withInput($request->only('login', 'email'))
                ->withErrors(['login' => 'Akun dengan Email atau Nomor HP tersebut tidak terdaftar di sistem Toko Online.']);
        }

        $email = $user->email;

        // Hapus kode OTP reset lama milik email ini (Auto-Cleanup)
        OtpCode::where('email', $email)->delete();

        // Buat kode OTP 6-digit baru (Sesuai Spesifikasi Backlog)
        $code = sprintf('%06d', mt_rand(100000, 999999));
        OtpCode::create([
            'user_id'     => $user->id,
            'email'       => $email,
            'code'        => $code,
            'action_type' => 'password_reset',
            'is_used'     => false,
            'expires_at'  => Carbon::now()->addMinutes(5),
        ]);

        // Simpan email sementara di session
        session(['pending_reset_email' => $email]);

        // Kirim email OTP Reset
        try {
            Mail::to($email)->send(new PasswordResetMail($code));
            session()->flash('success', 'Kode OTP reset kata sandi telah dikirimkan ke email terhubung (' . $email . ').');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email Reset OTP: ' . $e->getMessage());
            session()->flash('info', 'Kode OTP reset dibuat! (Mode Pengujian: Kode OTP Anda adalah ' . $code . '). Masukkan 16-digit Sandi Aplikasi di .env agar terkirim ke email.');
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
                ->with('info', 'Silakan masukkan email atau nomor HP Anda terlebih dahulu untuk memulihkan kata sandi.');
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

        // Ambil kode OTP
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

        // Cek keabsahan kode OTP (Sesuai Backlog)
        $otpRecord = OtpCode::where('email', $email)
            ->where('code', $otpInput)
            ->where('is_used', false)
            ->where('expires_at', '>=', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return back()->withInput($request->only('email', 'code'))
                ->withErrors(['code' => 'Kode OTP salah, telah digunakan, atau sudah kadaluarsa. Silakan minta kode baru.']);
        }

        // Update kata sandi user
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Tandai OTP telah digunakan & bersihkan OTP lama untuk email ini (Invalidation token/OTP)
        $otpRecord->update(['is_used' => true]);
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

        // Hapus OTP reset lama milik email ini
        OtpCode::where('email', $email)->delete();

        $user = User::where('email', $email)->first();

        // Buat OTP reset baru
        $code = sprintf('%06d', mt_rand(100000, 999999));
        OtpCode::create([
            'user_id'     => $user?->id,
            'email'       => $email,
            'code'        => $code,
            'action_type' => 'password_reset',
            'is_used'     => false,
            'expires_at'  => Carbon::now()->addMinutes(5),
        ]);

        // Kirim email
        try {
            Mail::to($email)->send(new PasswordResetMail($code));
            $msg = 'Kode OTP reset baru berhasil dikirim ke ' . $email;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email Reset OTP: ' . $e->getMessage());
            $msg = 'Mode Pengujian: Kode OTP reset baru Anda adalah ' . $code;
        }

        return back()->with('success', $msg);
    }
}
