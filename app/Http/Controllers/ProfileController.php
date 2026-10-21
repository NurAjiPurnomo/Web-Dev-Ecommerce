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

        $claimedVouchers = $user->vouchers()->withPivot('is_used', 'used_at')->get();

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

        if ($request->has('recipient_phone') && !empty($request->recipient_phone)) {
            $request->merge([
                'recipient_phone' => preg_replace('/[^0-9]/', '', $request->recipient_phone),
            ]);
        }

        if (!$request->filled('name')) {
            $request->merge(['name' => $user->name]);
        }
        if (!$request->filled('email')) {
            $request->merge(['email' => $user->email]);
        }
        if (!$request->filled('phone')) {
            $request->merge(['phone' => $user->phone]);
        } else {
            $request->merge([
                'phone' => preg_replace('/[^0-9]/', '', $request->phone),
            ]);
        }

        $request->validate([
            'name'             => ['required', 'string', 'min:3', 'max:100'],
            'email'            => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'            => ['required', 'digits_between:10,13', 'regex:/^(08|628)[0-9]{7,11}$/'],
            'gender'           => ['nullable', 'in:Laki-laki,Perempuan'],
            'birth_date'       => ['nullable', 'date'],
            'recipient_name'   => ['nullable', 'string', 'max:100'],
            'recipient_phone'  => ['nullable', 'string', 'max:20'],
            'address_label'    => ['nullable', 'string', 'max:50'],
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
            'email.required'       => 'Alamat email wajib diisi.',
            'email.email'          => 'Format alamat email tidak valid.',
            'email.unique'         => 'Alamat email sudah digunakan oleh akun lain.',
            'phone.required'       => 'Nomor WhatsApp / HP wajib diisi.',
            'phone.digits_between' => 'Nomor HP harus 10 sampai 13 digit angka.',
            'phone.regex'          => 'Nomor HP tidak valid. Diawali 08 atau 628.',
        ]);

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

        $biteshipAreaId = $request->input('biteship_area_id', $request->city_id);

        $user->update([
            'name'               => trim($request->name),
            'email'              => trim($request->email),
            'phone'              => trim($request->phone),
            'gender'             => $request->gender,
            'birth_date'         => $request->birth_date,
            'avatar'             => $avatarPath,
            'recipient_name'     => trim($request->recipient_name) ?: trim($request->name),
            'recipient_phone'    => trim($request->recipient_phone) ?: trim($request->phone),
            'address_label'      => trim($request->address_label) ?: 'Rumah',
            'is_primary_address' => $request->has('is_primary_address') ? (bool) $request->is_primary_address : true,
            'address'            => trim($request->address),
            'city_id'            => trim($request->city_id ?: $biteshipAreaId),
            'biteship_area_id'   => trim($biteshipAreaId),
            'district'           => trim($request->district ?? ''),
            'village'            => trim($request->village ?? ''),
            'city'               => trim($request->city ?? ''),
            'province'           => trim($request->province ?? ''),
            'postal_code'        => trim($request->postal_code),
        ]);

        $user->refresh();

        if (session()->has('user')) {
            session()->put('user', $user);
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
