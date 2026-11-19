<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

        $claimedVouchers = $user->vouchers()
            ->withPivot('is_used', 'used_at', 'created_at')
            ->orderByDesc('user_vouchers.id')
            ->get();

        $usableVouchersCount = $claimedVouchers->filter(function($v) {
            $isUsed = (bool) ($v->pivot->is_used ?? false);
            $isExpired = $v->expires_at && $v->expires_at->isPast();
            $isInactive = ($v->status ?? 'aktif') !== 'aktif';
            return !$isUsed && !$isExpired && !$isInactive;
        })->count();

        return view('pages.profile', [
            'user' => $user,
            'claimedVouchers' => $claimedVouchers,
            'usableVouchersCount' => $usableVouchersCount,
        ]);
    }

    /**
     * Update Biodata & Alamat Pengiriman Pengguna.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $activeTab = $request->input('active_tab', 'biodata');

        // 1. Tangani Upload Avatar Foto Profil (Base64 dari Cropper.js atau File Upload Biasa)
        $avatarPath = $user->avatar;
        if ($request->filled('avatar_base64')) {
            $base64Image = $request->avatar_base64;
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $data = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);

                if ($data !== false) {
                    $extension = in_array($type, ['png', 'jpg', 'jpeg', 'webp']) ? ($type === 'jpeg' ? 'jpg' : $type) : 'png';
                    $fileName = 'avatars/avatar_' . $user->id . '_' . time() . '.' . $extension;
                    Storage::disk('public')->put($fileName, $data);

                    if ($user->avatar && !str_starts_with($user->avatar, 'http') && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }

                    $avatarPath = $fileName;
                }
            }
        } elseif ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = $file->store('avatars', 'public');
            if ($user->avatar && !str_starts_with($user->avatar, 'http') && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $fileName;
        }

        // Jika request khusus AJAX upload foto dari modal Cropper.js
        if (($request->wantsJson() || $request->ajax()) && $request->filled('avatar_base64') && !$request->has('name') && !$request->has('recipient_name')) {
            $user->update(['avatar' => $avatarPath]);
            $user->refresh();
            if (session()->has('user')) {
                session()->put('user', $user->toArray());
            }
            return response()->json([
                'success' => true,
                'message' => 'Foto profil Anda berhasil diperbarui!',
                'avatar_url' => str_starts_with($avatarPath, 'http') ? $avatarPath : asset('storage/' . $avatarPath)
            ]);
        }

        // 2. Pemrosesan Tab Berdasarkan Tab yang Aktif
        if ($activeTab === 'alamat') {
            // ============================================
            // UPDATE ALAMAT PENGIRIMAN (Tab Alamat)
            // ============================================
            if ($request->has('recipient_phone') && !empty($request->recipient_phone)) {
                $request->merge([
                    'recipient_phone' => preg_replace('/[^0-9]/', '', $request->recipient_phone),
                ]);
            }

            $request->validate([
                'recipient_name'   => ['required', 'string', 'max:100'],
                'recipient_phone'  => ['required', 'digits_between:10,15'],
                'address_label'    => ['nullable', 'string', 'max:50'],
                'address'          => ['required', 'string', 'max:500'],
                'city_id'          => ['nullable', 'string', 'max:100'],
                'biteship_area_id' => ['nullable', 'string', 'max:100'],
                'district'         => ['nullable', 'string', 'max:100'],
                'village'          => ['nullable', 'string', 'max:100'],
                'city'             => ['nullable', 'string', 'max:100'],
                'province'         => ['nullable', 'string', 'max:100'],
                'postal_code'      => ['nullable', 'string', 'max:10'],
            ], [
                'recipient_name.required'        => 'Nama penerima paket wajib diisi.',
                'recipient_phone.required'       => 'Nomor HP penerima wajib diisi.',
                'recipient_phone.digits_between' => 'Nomor HP penerima harus 10 sampai 15 digit angka.',
                'address.required'               => 'Alamat lengkap & patokan rumah wajib diisi.',
            ]);

            $biteshipAreaId = $request->input('biteship_area_id', $request->city_id);

            $updateData = [
                'recipient_name'     => trim($request->recipient_name),
                'recipient_phone'    => trim($request->recipient_phone),
                'address_label'      => trim($request->address_label) ?: 'Rumah',
                'is_primary_address' => $request->has('is_primary_address') ? (bool) $request->is_primary_address : true,
                'address'            => trim($request->address),
                'city_id'            => trim($request->city_id ?: $biteshipAreaId),
                'biteship_area_id'   => trim($biteshipAreaId),
                'district'           => trim($request->district ?? ''),
                'village'            => trim($request->village ?? ''),
                'city'               => trim($request->city ?? ''),
                'province'           => trim($request->province ?? ''),
                'postal_code'        => trim($request->postal_code ?? ''),
            ];

            // Jika user belum punya nomor HP akun utama tapi mengisi recipient_phone, sinkronkan otomatis
            if (empty($user->phone) && !empty($updateData['recipient_phone'])) {
                $updateData['phone'] = $updateData['recipient_phone'];
            }

            $user->update($updateData);

        } else {
            // ============================================
            // UPDATE BIODATA DIRI (Tab Biodata)
            // ============================================
            if ($request->has('phone') && !empty($request->phone)) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
                if (str_starts_with($cleanPhone, '628')) {
                    $cleanPhone = '08' . substr($cleanPhone, 3);
                } elseif (str_starts_with($cleanPhone, '8')) {
                    $cleanPhone = '08' . substr($cleanPhone, 1);
                }
                $request->merge(['phone' => $cleanPhone]);
            }

            // Aturan validasi nomor HP:
            // Jika login Google dan kolom HP belum diisi, nomor HP diizinkan nullable.
            // Jika diisi, harus 10-15 digit dengan format yang valid (diawali 08 atau 628).
            $phoneRules = ['nullable', 'digits_between:10,15', 'regex:/^(08|628)[0-9]{7,13}$/'];
            if (!$user->google_id && empty($user->phone)) {
                $phoneRules[0] = 'required';
            }

            $request->validate([
                'name'       => ['required', 'string', 'min:3', 'max:100'],
                'email'      => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone'      => $phoneRules,
                'gender'     => ['nullable', 'in:Laki-laki,Perempuan'],
                'birth_date' => ['nullable', 'date'],
            ], [
                'name.required'        => 'Nama lengkap wajib diisi.',
                'name.min'             => 'Nama minimal 3 karakter.',
                'email.required'       => 'Alamat email wajib diisi.',
                'email.email'          => 'Format alamat email tidak valid.',
                'email.unique'         => 'Alamat email sudah digunakan oleh akun lain.',
                'phone.required'       => 'Nomor WhatsApp / HP wajib diisi.',
                'phone.digits_between' => 'Nomor HP harus 10 sampai 15 digit angka.',
                'phone.regex'          => 'Nomor HP tidak valid. Diawali 08 atau 628.',
            ]);

            $updateData = [
                'name'       => trim($request->name),
                'email'      => trim($request->email),
                'gender'     => $request->gender,
                'birth_date' => $request->birth_date,
                'avatar'     => $avatarPath,
            ];

            if ($request->filled('phone')) {
                $updateData['phone'] = trim($request->phone);
            }

            $user->update($updateData);
        }

        $user->refresh();

        if (session()->has('user')) {
            session()->put('user', $user->toArray());
        }

        $activeTab = $request->input('active_tab', 'biodata');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data profil Anda berhasil diperbarui!',
                'avatar_url' => $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar)) : null
            ]);
        }

        return redirect()->back()
            ->with('success', 'Data profil Anda berhasil diperbarui!')
            ->with('active_tab', $activeTab);
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
            return back()->withErrors(['current_password' => 'Kata sandi saat ini salah.'])->with('active_tab', 'keamanan');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'Kata sandi akun Anda berhasil diperbarui!')
            ->with('active_tab', 'keamanan');
    }
}
