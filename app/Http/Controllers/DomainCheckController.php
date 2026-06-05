<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomainCheckController extends Controller
{
    /**
     * Tampilkan halaman domain check.
     */
    public function index()
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        return view('pengaturan.domain-check');
    }

    /**
     * Panggil API untuk cek domain.
     */
    public function check(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $apiKey = Setting::where('key', 'database_gabungan_api_key')->value('value');
            $apiUrl = config('app.databaseGabunganUrl');

            if (!$apiKey || !$apiUrl) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Konfigurasi API Database Gabungan belum diatur.',
                ], 500);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->get($apiUrl . '/api/v1/debug/domain-check');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'status' => 'ERROR',
                'message' => 'API mengembalikan error: ' . $response->status(),
                'detail' => $response->json(),
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Domain check failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Gagal menghubungi API: ' . $e->getMessage(),
            ], 500);
        }
    }
}
