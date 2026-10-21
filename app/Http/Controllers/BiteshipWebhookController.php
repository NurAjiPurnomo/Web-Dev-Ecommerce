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

        // Cari pesanan forward atau pengajuan retur berdasarkan biteship_order_id atau waybill_number
        $order = null;
        $orderReturn = null;
        if ($biteshipOrderId) {
            $order = Order::where('biteship_order_id', $biteshipOrderId)->first();
            if (!$order) {
                $orderReturn = \App\Models\OrderReturn::where('biteship_order_id', $biteshipOrderId)->first();
            }
        }
        if (!$order && !$orderReturn && $waybillNumber) {
            $order = Order::where('waybill_number', $waybillNumber)->first();
            if (!$order) {
                $orderReturn = \App\Models\OrderReturn::where('return_tracking_number', $waybillNumber)->first();
            }
        }

        // Tangani webhook khusus paket retur
        if ($orderReturn) {
            Log::info("Biteship Webhook for Return #{$orderReturn->id}: {$status} - {$note}");
            if (in_array($status, ['delivered', 'completed', 'done', 'selesai'])) {
                if ($orderReturn->order && $orderReturn->order->user_id) {
                    Announcement::create([
                        'user_id' => $orderReturn->order->user_id,
                        'title'   => '📦 Paket Retur #' . ($orderReturn->order->invoice_number ?? $orderReturn->order_id) . ' Telah Tiba di Gudang Toko',
                        'content' => 'Paket retur fisik Anda telah sukses diterima di lokasi gudang toko penjual. Tim toko sedang memeriksa kelengkapan barang sebelum menyelesaikan pengembalian dana / penggantian barang.',
                        'type'    => 'notifikasi',
                        'target'  => 'pelanggan',
                        'status'  => 'ditayangkan'
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Biteship Return Webhook processed successfully.'
            ]);
        }

        if ($order) {
            // 1. STATUS SELESAI / TERKIRIM (DELIVERED)
            if (in_array($status, ['delivered', 'completed', 'done', 'selesai'])) {
                if ($order->status !== 'selesai') {
                    $order->update([
                        'status' => 'dikirim',
                    ]);
                }

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
            // 2. STATUS DALAM PENGIRIMAN / TRANSIT / ON HOLD
            elseif (in_array($status, ['dropping_off', 'in_transit', 'picked_up', 'allocated', 'picking_up', 'confirmed', 'on_hold', 'ditahan'])) {
                if ($order->status !== 'dikirim') {
                    $order->update([
                        'status' => 'dikirim',
                    ]);
                    Log::info("Biteship Webhook: Order #{$order->invoice_number} updated to DIKIRIM ({$status}).");
                }
            } 
            // 3. STATUS KENDALA / DITOLAK / PAKET HILANG / PAKET RUSAK / HANCURKAN PAKET
            elseif (in_array($status, ['rejected', 'undelivered', 'courier_not_found_address', 'failed', 'cancelled', 'lost', 'paket_hilang', 'damaged', 'paket_rusak', 'disposed', 'hancurkan_paket'])) {
                $order->update([
                    'status' => 'batal',
                ]);

                // Notifikasi Pelanggan & Log Kendala
                if ($order->user_id) {
                    Announcement::create([
                        'user_id' => $order->user_id,
                        'title'   => '⚠️ Kendala Pengiriman / Paket Dibatalkan #' . $order->invoice_number,
                        'content' => 'Pengiriman paket Anda mengalami kendala status (' . str_replace('_', ' ', $status) . ': ' . $note . ').',
                        'type'    => 'notifikasi',
                        'target'  => 'pelanggan',
                        'status'  => 'ditayangkan'
                    ]);
                }

                Log::warning("Biteship Webhook: Order #{$order->invoice_number} KENDALA: {$status} - {$note}");
            }
            // 4. STATUS RETUR / DIKEMBALIKAN KE PENJUAL (RETURN PROCESS / RETURNING / RETURNED)
            elseif (in_array($status, ['returning', 'return_in_transit', 'returned', 'return_process', 'return process'])) {
                $order->update([
                    'status' => 'retur',
                ]);

                if ($order->user_id) {
                    Announcement::create([
                        'user_id' => $order->user_id,
                        'title'   => '🔄 Paket Dalam Proses Retur ke Toko Penjual #' . $order->invoice_number,
                        'content' => 'Paket pesanan Anda sedang dikembalikan ke lokasi toko pengirim. Alasan: ' . $note,
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
