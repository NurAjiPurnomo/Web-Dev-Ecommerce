<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirController extends Controller
{
    /**
     * Display the shipping cost calculator view.
     */
    public function index()
    {
        $apiKey = env('RAJAONGKIR_API_KEY');
        $apiKeySet = !empty($apiKey);
        $provinces = [];
        $errorMessage = null;

        if ($apiKeySet) {
            try {
                $response = Http::withoutVerifying()
                    ->withHeaders([
                        'key' => env('RAJAONGKIR_API_KEY'),
                    ])
                    ->get('https://api.rajaongkir.com/starter/province');

                if ($response->successful()) {
                    $provinces = $response->json('rajaongkir.results') ?? [];
                } else {
                    $errorMessage = $response->json('rajaongkir.status.description') ?? 'Gagal mengambil data provinsi dari RajaOngkir API.';
                }
            } catch (\Exception $e) {
                Log::error('RajaOngkir Index Error: ' . $e->getMessage());
                $errorMessage = 'Terjadi kesalahan koneksi ke RajaOngkir API: ' . $e->getMessage();
            }
        }

        return view('shipping', compact('provinces', 'apiKeySet', 'errorMessage'));
    }

    /**
     * Fetch all province data (GET https://api.rajaongkir.com/starter/province).
     */
    public function getProvinces()
    {
        $apiKey = env('RAJAONGKIR_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'API Key RajaOngkir belum dikonfigurasi di file .env (RAJAONGKIR_API_KEY)'
            ], 400);
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'key' => env('RAJAONGKIR_API_KEY'),
                ])
                ->get('https://api.rajaongkir.com/starter/province');

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'data' => $response->json('rajaongkir.results') ?? []
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => $response->json('rajaongkir.status.description') ?? 'Gagal mengambil data provinsi dari RajaOngkir API.'
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('RajaOngkir getProvinces Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan koneksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch city data based on selected province ID (GET https://api.rajaongkir.com/starter/city?province={province_id}).
     */
    public function getCities($provinceId)
    {
        $apiKey = env('RAJAONGKIR_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'API Key RajaOngkir belum dikonfigurasi di file .env (RAJAONGKIR_API_KEY)'
            ], 400);
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'key' => env('RAJAONGKIR_API_KEY'),
                ])
                ->get('https://api.rajaongkir.com/starter/city', [
                    'province' => $provinceId
                ]);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'data' => $response->json('rajaongkir.results') ?? []
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => $response->json('rajaongkir.status.description') ?? 'Gagal mengambil data kota/kabupaten.'
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('RajaOngkir getCities Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan koneksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate shipping costs (POST https://api.rajaongkir.com/starter/cost).
     */
    public function checkCost(Request $request)
    {
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'weight' => 'required|numeric|min:1',
            'courier' => 'required|string|in:jne,pos,tiki',
        ], [
            'origin.required' => 'Kota asal harus dipilih.',
            'destination.required' => 'Kota tujuan harus dipilih.',
            'weight.required' => 'Berat barang harus diisi.',
            'weight.min' => 'Berat barang minimal 1 gram.',
            'courier.required' => 'Kurir pengiriman harus dipilih.',
            'courier.in' => 'Pilihan kurir tidak valid (pilih: JNE, POS, atau TIKI).',
        ]);

        $apiKey = env('RAJAONGKIR_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'API Key RajaOngkir belum dikonfigurasi di file .env. Silakan isi RAJAONGKIR_API_KEY pada file .env Anda.'
            ], 400);
        }

        try {
            $response = Http::withoutVerifying()->timeout(30)
                ->withHeaders([
                    'key' => env('RAJAONGKIR_API_KEY'),
                ])
                ->asForm()
                ->post('https://api.rajaongkir.com/starter/cost', [
                    'origin' => $request->origin,
                    'destination' => $request->destination,
                    'weight' => $request->weight,
                    'courier' => strtolower($request->courier),
                ]);

            if ($response->successful()) {
                $results = $response->json('rajaongkir.results') ?? [];
                $originDetails = $response->json('rajaongkir.origin_details');
                $destinationDetails = $response->json('rajaongkir.destination_details');

                return response()->json([
                    'status' => 'success',
                    'origin_details' => $originDetails,
                    'destination_details' => $destinationDetails,
                    'data' => $results
                ]);
            }

            $description = $response->json('rajaongkir.status.description') ?? 'Gagal menghitung ongkos kirim dari RajaOngkir API.';
            return response()->json([
                'status' => 'error',
                'message' => $description
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('RajaOngkir checkCost Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan koneksi ke RajaOngkir API: ' . $e->getMessage()
            ], 500);
        }
    }
}
