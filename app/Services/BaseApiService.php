<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BaseApiService
{
    protected $settings;
    protected $header;
    protected $baseUrl;

    public function __construct()
    {
        // URL dasar API yang dituju
        $this->baseUrl = config('app.databaseGabunganUrl');
    }

    /**
     * Mendapatkan header Authorization untuk request API
     */
    protected function getHeader()
    {
        // Hanya ambil value jika header belum ada
        if (!$this->header) {
            $apiKey = Setting::where('key', 'database_gabungan_api_key')->value('value');
            $this->header = [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . ($apiKey ?? ''),
            ];
        }

        return $this->header;
    }

    /**
     * General API Call Method
     */
    protected function apiRequest(string $endpoint, array $params = [])
    {
        try {
            // Buat permintaan API dengan Header dan Parameter
            $response = Http::withHeaders($this->getHeader())->get($this->baseUrl . $endpoint, $params);

            if ($response->status() == 429) {
                $retryAfter = $response->header('Retry-After');
                Log::warning("Rate limited. Retry after {$retryAfter} seconds.");
                return [];
            }

            if ($response->failed()) {
                $message = $response->json()['message'] ?? 'Unknown API error';
                session()->flash('error_api', 'Gagal Insert Data: ' . $message);
                Log::error('API error in ' . __FILE__ . ' function ' . __METHOD__ . ': ' . $response->body());
                return [];
            }

            session()->forget('error_api');
            // Return JSON hasil
            return $response->json('data') ?? [];
        } catch (\Exception $e) {
            session()->flash('error_api', 'Gagal mendapatkan data'. $e->getMessage());
            Log::error('Failed get data in '.__FILE__.' function '.__METHOD__.' '. $e->getMessage());
        }
        return [];
    }

    protected function apiPost(string $endpoint, array $data = [])
    {
        try {
            // Buat permintaan API POST dengan Header dan Body
            $response = Http::withHeaders($this->getHeader())->post($this->baseUrl . $endpoint, $data);

            if ($response->status() == 429) {
                $retryAfter = $response->header('Retry-After');
                Log::warning("Rate limited. Retry after {$retryAfter} seconds.");
                return [];
            }

            if ($response->failed()) {

                $message = $response->json()['message'] ?? 'Unknown API error';
                session()->flash('error_api', 'Gagal Insert Data: ' . $message);
                Log::error('API error in ' . __FILE__ . ' function ' . __METHOD__ . ': ' . $response->body());
                return [];
            }
            
            session()->forget('error_api');
            // Return JSON hasil
            return $response->json();
        } catch (\Exception $e) {
            session()->flash('error_api', 'Gagal Insert Data: ' . $e->getMessage());
            Log::error('Failed to post data in ' . __FILE__ . ' function ' . __METHOD__ . ': ' . $e->getMessage());
        }
        return [];
    }


    /**
     * General API Call Method
     */
    protected function apiRequestSimple(string $endpoint, array $params = [])
    {
        try {
            // Buat permintaan API dengan Header dan Parameter
            $response = Http::withHeaders($this->getHeader())->get($this->baseUrl . $endpoint, $params);
            session()->forget('error_api');
            // Return JSON hasil
            return $response->json() ?? [];
        } catch (\Exception $e) {
            session()->flash('error_api', 'Gagal mendapatkan data'. $e->getMessage());
            Log::error('Failed get data in '.__FILE__.' function '.__METHOD__.' '. $e->getMessage());
        }
        return [];
    }
}