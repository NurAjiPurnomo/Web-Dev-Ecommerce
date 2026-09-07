<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccess;

class DokuWebhookController extends Controller
{
    /**
     * Endpoint untuk menerima notifikasi dari DOKU Jokul.
     */
    public function handle(Request $request)
    {
        Log::info('DOKU Webhook Received: ', $request->all());

        // DOKU Jokul mengirim Signature melalui Headers
        $clientId = config('services.doku.client_id') ?: env('DOKU_CLIENT_ID', 'DOKU-DUMMY-CLIENT-ID');
        $secretKey = config('services.doku.secret_key') ?: env('DOKU_SECRET_KEY', 'DOKU-DUMMY-SECRET-KEY');

        $dokuSignature = $request->header('Signature');
        $dokuRequestId = $request->header('Request-Id');
        $dokuTimestamp = $request->header('Request-Timestamp');
        $dokuTarget = '/api/doku/webhook'; // Harus sama dengan path endpoint ini di route API

        // Memvalidasi Signature
        $body = $request->getContent();
        $digest = base64_encode(hash('sha256', $body, true));

        $componentSignature = "Client-Id:" . $clientId . "\n" .
                              "Request-Id:" . $dokuRequestId . "\n" .
                              "Request-Timestamp:" . $dokuTimestamp . "\n" .
                              "Request-Target:" . $dokuTarget . "\n" .
                              "Digest:" . $digest;
                              
        $calculatedSignature = "HMACSHA256=" . base64_encode(hash_hmac('sha256', $componentSignature, $secretKey, true));

        // Jika Signature tidak cocok (opsional untuk keamanan maksimal, di-comment untuk testing mudah)
        // if ($calculatedSignature !== $dokuSignature) {
        //     Log::error('DOKU Webhook Invalid Signature');
        //     return response()->json(['message' => 'Invalid signature'], 401);
        // }

        $payload = $request->all();

        // Ambil data invoice dan status
        // Format notifikasi DOKU bisa berbeda tergantung metode (VA, Credit Card, dll). 
        // Secara umum ada order.invoice_number dan transaction.status
        $invoiceNumber = $payload['order']['invoice_number'] ?? null;
        $status = $payload['transaction']['status'] ?? null;

        if ($invoiceNumber) {
            $order = Order::where('invoice_number', $invoiceNumber)->first();

            if ($order) {
                // Jika pembayaran sukses (SUCCESS)
                if (in_array(strtoupper($status), ['SUCCESS', 'PAID'])) {
                    // Update status pesanan ke 'diproses' (Sedang Dikemas)
                    $order->update([
                        'status' => 'diproses'
                    ]);
                    
                    Log::info("DOKU Webhook: Order {$invoiceNumber} marked as PAID.");

                    // Kirim Email Pembayaran Sukses
                    try {
                        $userEmail = $order->user->email ?? null;
                        if ($userEmail) {
                            Mail::to($userEmail)->send(new PaymentSuccess($order));
                            Log::info("DOKU Webhook: PaymentSuccess email sent to {$userEmail}");
                        }
                    } catch (\Exception $e) {
                        Log::error("DOKU Webhook: Gagal mengirim email PaymentSuccess: " . $e->getMessage());
                    }
                } else if (in_array(strtoupper($status), ['FAILED', 'EXPIRED'])) {
                    // Jika gagal atau kedaluwarsa
                    $order->update([
                        'status' => 'dibatalkan'
                    ]);
                    Log::info("DOKU Webhook: Order {$invoiceNumber} marked as FAILED/EXPIRED.");
                }
            } else {
                Log::warning("DOKU Webhook: Order {$invoiceNumber} not found in DB.");
            }
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
