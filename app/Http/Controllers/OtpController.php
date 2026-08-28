<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\OtpCode;
use App\Mail\OtpMail;
use Carbon\Carbon;

class OtpController extends Controller
{
    /**
     * Tampilkan halaman verifikasi OTP.
     */
    public function showVerifyForm()
    {
        $email = session('pending_otp_email');

        if (!$email) {
            return redirect()->route('login')
                ->with('info', 'Silakan masuk atau daftar akun terlebih dahulu.');
        }

        return view('auth.verify-otp', [
            'email' => $email,
        ]);
    }

    /**
     * Proses verifikasi kode OTP 6 digit.
     */
    public function verify(Request $request)
    {
        $email = session('pending_otp_email');

        if (!$email) {
            return redirect()->route('login');
        }

        // Ambil kode 6 digit (bisa dari input terpisah `otp_1`, `otp_2`, dst, atau string `code`)
        $otpInput = $request->input('code');
        if (is_array($request->input('otp'))) {
            $otpInput = implode('', $request->input('otp'));
        }

        $request->merge(['code' => $otpInput]);

        $request->validate([
            'code' => 'required|digits:6',
        ], [
            'code.required' => 'Kode OTP 6 digit wajib diisi.',
            'code.digits'   => 'Kode OTP harus terdiri dari 6 angka.',
        ]);

        $otpRecord = OtpCode::where('email', $email)
            ->where('code', $otpInput)
            ->where('expires_at', '>=', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return back()->withErrors([
                'code' => 'Kode OTP salah atau sudah kadaluarsa. Silakan minta kode baru.',
            ]);
        }

        // Hapus kode OTP yang telah digunakan
        OtpCode::where('email', $email)->delete();

        // Cari user dan verifikasi emailnya
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->email_verified_at = Carbon::now();
            $user->save();

            Auth::login($user, true);
            $request->session()->forget('pending_otp_email');
            $request->session()->regenerate();

            return redirect()->route('home')
                ->with('success', 'Email berhasil diverifikasi! Selamat berbelanja, ' . $user->name . '!');
        }

        return redirect()->route('login')
            ->with('info', 'Akun tidak ditemukan. Silakan daftar ulang.');
    }

    /**
     * Kirim ulang kode OTP.
     */
    public function resend(Request $request)
    {
        $email = session('pending_otp_email');

        if (!$email) {
            return redirect()->route('login');
        }

        // Cek jeda pengiriman minimal 60 detik
        $recentOtp = OtpCode::where('email', $email)
            ->where('created_at', '>=', Carbon::now()->subSeconds(60))
            ->first();

        if ($recentOtp) {
            $remaining = 60 - Carbon::now()->diffInSeconds($recentOtp->created_at);
            return back()->with('info', 'Tunggu ' . $remaining . ' detik sebelum meminta kode OTP baru.');
        }

        // Hapus OTP lama
        OtpCode::where('email', $email)->delete();

        // Buat OTP baru
        $code = sprintf('%06d', mt_rand(100000, 999999));
        OtpCode::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // Kirim email
        try {
            Mail::to($email)->send(new OtpMail($code));
            $msg = 'Kode OTP baru berhasil dikirim ke ' . $email;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email OTP: ' . $e->getMessage());
            $msg = 'SMTP Gmail belum terhubung (Sandi Aplikasi 16-digit belum diisi). Kode OTP Pengujian kamu adalah: ' . $code;
        }

        return back()->with('success', $msg);
    }

    /**
     * Helper method static untuk mengirimkan OTP dari AuthController.
     */
    public static function sendOtpForEmail(string $email): string
    {
        OtpCode::where('email', $email)->delete();

        $code = sprintf('%06d', mt_rand(100000, 999999));
        OtpCode::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        try {
            Mail::to($email)->send(new OtpMail($code));
            session()->flash('success', 'Akun berhasil dibuat! Kode OTP telah dikirimkan ke email ' . $email);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email OTP: ' . $e->getMessage());
            session()->flash('info', 'Akun berhasil dibuat! (Mode Pengujian: Kode OTP kamu adalah ' . $code . '). Masukkan 16-digit Sandi Aplikasi di .env agar terkirim ke email.');
        }

        return $code;
    }
}
