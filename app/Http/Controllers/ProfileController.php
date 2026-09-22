<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Tampilkan Halaman Profil Saya & Pengaturan Alamat.
     */
    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.profile', [
            'user' => $user,
        ]);
    }

    /**
     * Update Biodata & Alamat Pengiriman Pengguna.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->merge([
            'phone' => preg_replace('/[^0-9]/', '', $request->phone),
        ]);

        $request->validate([
            'name'        => ['required', 'string', 'min:3', 'max:100'],
            'phone'       => ['required', 'digits_between:10,13', 'regex:/^(08|628)[0-9]{7,11}$/'],
            'gender'      => ['nullable', 'in:Laki-laki,Perempuan'],
            'birth_date'  => ['nullable', 'date'],
            'address'          => ['nullable', 'string', 'max:500'],
            'city_id'          => ['nullable', 'string', 'max:100'],
            'biteship_area_id' => ['nullable', 'string', 'max:100'],
            'district'         => ['nullable', 'string', 'max:100'],
            'village'          => ['nullable', 'string', 'max:100'],
            'city'             => ['nullable', 'string', 'max:100'],
            'province'         => ['nullable', 'string', 'max:100'],
            'postal_code'      => ['nullable', 'string', 'max:10'],
        ], [
            'name.required'        => 'Nama lengkap wajib diisi.',
            'name.min'             => 'Nama minimal 3 karakter.',
            'phone.required'       => 'Nomor WhatsApp / HP wajib diisi.',
            'phone.digits_between' => 'Nomor HP harus 10 sampai 13 digit angka.',
            'phone.regex'          => 'Nomor HP tidak valid. Diawali 08 atau 628.',
        ]);

        $biteshipAreaId = $request->input('biteship_area_id', $request->city_id);

        $user->update([
            'name'             => trim($request->name),
            'phone'            => trim($request->phone),
            'gender'           => $request->gender,
            'birth_date'       => $request->birth_date,
            'address'          => trim($request->address),
            'city_id'          => trim($request->city_id ?: $biteshipAreaId),
            'biteship_area_id' => trim($biteshipAreaId),
            'district'         => trim($request->district ?? ''),
            'village'          => trim($request->village ?? ''),
            'city'             => trim($request->city ?? ''),
            'province'         => trim($request->province ?? ''),
            'postal_code'      => trim($request->postal_code),
        ]);

        return redirect()->back()
            ->with('success', 'Biodata & Alamat Pengiriman berhasil diperbarui!');
    }

    /**
     * Update Kata Sandi Akun.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password'      => ['required'],
            'password'              => ['required', 'min:6', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'current_password.required'      => 'Kata sandi saat ini wajib diisi.',
            'password.required'              => 'Kata sandi baru wajib diisi.',
            'password.min'                   => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed'             => 'Konfirmasi kata sandi tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi kata sandi baru wajib diisi.',
        ]);

        if ($user->password && !Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'Kata sandi akun Anda berhasil diperbarui!');
    }
}
