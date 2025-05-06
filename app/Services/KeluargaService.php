<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class KeluargaService
{

    protected $baseUrl;
    protected $setting;

    public function __construct()
    {
        // URL dasar API yang dituju
        $this->baseUrl = config('app.databaseGabunganUrl'); // Misal simpan di config/services.php
        $this->setting = Setting::where('key', 'database_gabungan_api_key')->first();
    }

    public function summary(array $query = [])
    {
        $cacheKey = 'keluarga_summary_' . md5(json_encode($query));
        $ttl = now()->addMinutes(10); // Simpan cache selama 10 menit

        return Cache::remember($cacheKey, $ttl, function () use ($query) {
            $url = $this->baseUrl . '/api/v1/keluarga/summary';

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . ($this->setting->value ?? ''),
            ])->get($url, $query);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception("Gagal mengambil data summary: " . $response->status());
        });
    }

}