<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiteshipWebhookController extends Controller
{
    /**
     * Handle Webhook Notifikasi Tracking / Status Pengiriman & Return dari Biteship.
     */
    public function handle(Request $request)
    {
        Log::info('Biteship Webhook Received: ', $request->all());

        $biteshipOrderId = $request->input('order_id') ?: $request->input('id');
        $waybillNumber = $request->input('courier_waybill_id') ?: $request->input('waybill_id');
        $status = strtolower($request->input('status', ''));
        $note = $request->input('note') ?: ($request->input('description') ?: 'Pembaruan status dari kurir');

        // Cari pesanan berdasarkan biteship_order_id atau waybill_number
        $order = null;
        if ($biteshipOrderId) {
            $order = Order::where('biteship_order_id', $biteshipOrderId)->first();
        }
        if (!$order && $waybillNumber) {
            $order = Order::where('waybill_number', $waybillNumber)->first();
        }

        if ($order) {
            // 1. STATUS SELESAI / TERKIRIM (DELIVERED)
            if (in_array($status, ['delivered', 'completed', 'done'])) {
                $order->update([
                    'status' => 'selesai',
                ]);

                // Notifikasi Pelanggan
                if ($order->user_id) {
                    Announcement::create([
                        'user_id' => $order->user_id,
                        'title'   => '🎉 Pesanan #' . $order->invoice_number . ' Telah Diterima',
                        'content' => 'Paket pesanan Anda telah sukses diserahterimakan oleh kurir. Terima kasih telah berbelanja!',
                        'type'    => 'notifikasi',
                        'target'  => 'pelanggan',
                        'status'  => 'ditayangkan'
                    ]);
                }

                Log::info("Biteship Webhook: Order #{$order->invoice_number} marked as SELESAI (Delivered).");
            } 
            // 2. STATUS DALAM PENGIRIMAN / TRANSIT
            elseif (in_array($status, ['dropping_off', 'in_transit', 'picked_up', 'allocated', 'picking_up'])) {
                if ($order->status !== 'dikirim') {
                    $order->update([
                        'status' => 'dikirim',
                    ]);
                    Log::info("Biteship Webhook: Order #{$order->invoice_number} updated to DIKIRIM ({$status}).");
                }
            } 
            // 3. STATUS KENDALA / DITOLAK / GAGAL COD (REJECTED & UNDELIVERED)
            elseif (in_array($status, ['rejected', 'undelivered', 'courier_not_found_address', 'failed', 'cancelled'])) {
                $order->update([
                    'status' => 'batal',
                ]);

                // Notifikasi Pelanggan & Log Kendala
                if ($order->user_id) {
                    Announcement::create([
                        'user_id' => $order->user_id,
                        'title'   => '⚠️ Kendala Pengiriman / Gagal Terkirim #' . $order->invoice_number,
                        'content' => 'Pengiriman paket Anda mengalami kendala (' . $note . '). Pesanan dibatalkan/dikembalikan ke penjual.',
                        'type'    => 'notifikasi',
                        'target'  => 'pelanggan',
                        'status'  => 'ditayangkan'
                    ]);
                }

                Log::warning("Biteship Webhook: Order #{$order->invoice_number} GAGAL/DITOLAK: {$status} - {$note}");
            }
            // 4. STATUS DIKEMBALIKAN KE PENJUAL (RETURN TO SHIPPER / RETURNING / RETURNED)
            elseif (in_array($status, ['returning', 'return_in_transit', 'returned'])) {
                $order->update([
                    'status' => 'batal',
                ]);

                if ($order->user_id) {
                    Announcement::create([
                        'user_id' => $order->user_id,
                        'title'   => '🔄 Paket Dikembalikan ke Toko Penjual #' . $order->invoice_number,
                        'content' => 'Paket pesanan Anda sedang/telah dikembalikan ke lokasi toko pengirim. Alasan: ' . $note,
                        'type'    => 'notifikasi',
                        'target'  => 'pelanggan',
                        'status'  => 'ditayangkan'
                    ]);
                }

                Log::warning("Biteship Webhook: Order #{$order->invoice_number} RETURN TO SHIPPER: {$status} - {$note}");
            }
        } else {
            Log::warning("Biteship Webhook: Order not found for Order ID: {$biteshipOrderId} / Waybill: {$waybillNumber}");
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Biteship Webhook processed successfully.'
        ]);
    }
}
