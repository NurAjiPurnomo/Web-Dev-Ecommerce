<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function claim(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);
        
        // Cannot claim inactive or expired voucher
        if ($voucher->status !== 'aktif' || ($voucher->expires_at && $voucher->expires_at->isPast())) {
            return back()->with('error', 'Voucher sudah tidak berlaku atau kadaluarsa.');
        }

        $user = Auth::user();

        // Check if targeted to specific user
        if ($voucher->target === 'pelanggan' && $voucher->user_id && $voucher->user_id !== $user->id) {
            return back()->with('error', 'Voucher ini bersifat khusus dan tidak ditujukan untuk akun Anda.');
        }
        
        // Check if already claimed
        if ($user->vouchers()->where('voucher_id', $id)->exists()) {
            return back()->with('info', 'Anda sudah mengklaim voucher ini.');
        }

        $user->vouchers()->attach($id);

        return back()->with('success', 'Voucher berhasil diklaim dan masuk ke dompet Anda!');
    }

    public function myVouchers()
    {
        return redirect()->to(route('profile') . '#vouchers');
    }
}
