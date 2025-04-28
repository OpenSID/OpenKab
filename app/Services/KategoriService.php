<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class KategoriService
{

    protected $baseUrl;
    protected $setting;

    public function __construct()
    {
        // URL dasar API yang dituju
        $this->baseUrl = config('app.databaseGabunganUrl'); // Misal simpan di config/services.php
        $this->setting = Setting::where('key', 'database_gabungan_api_key')->first();
    }

    public function kategori(int $id)
    {
        $cacheKey = "kategori_$id";

        // Ambil dari cache dulu
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($id) {
            $url = $this->baseUrl . '/api/v1/kategori';

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . ($this->setting->value ?? ''),
            ])->get($url, [
                'filter[id]' => $id
            ]);

            if ($response->successful()) {
                $data = $response->json('data');

                if (is_array($data) && count($data) > 0) {
                    return $data[0];
                }

                return null;
            }

            throw new \Exception("Gagal mengambil data kategori: " . $response->status());
        });

    }

}