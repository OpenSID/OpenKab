<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class PemetaanService
{
    protected $baseUrl;

    protected $setting;

    public function __construct()
    {
        // URL dasar API yang dituju
        $this->baseUrl = config('app.databaseGabunganUrl'); // Misal simpan di config/services.php
        $this->setting = Setting::where('key', 'database_gabungan_api_key')->first();
    }

    public function getAllPoint(array $query = [])
    {
        $url = $this->baseUrl.'/api/v1/point';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.($this->setting->value ?? ''),
        ])->get($url, $query);

        if ($response->successful()) {
            $data = $response->json('data');

            if (is_array($data)) {
                return $data;
            }

            return null;
        }

        throw new \Exception('Gagal mengambil data point: '.$response->status());
    }

    public function getAllPlan(array $query = [])
    {
        $url = $this->baseUrl.'/api/v1/plan';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.($this->setting->value ?? ''),
        ])->get($url, $query);

        if ($response->successful()) {
            $data = $response->json('data');

            if (is_array($data)) {
                return $data;
            }

            return null;
        }

        throw new \Exception('Gagal mengambil data point: '.$response->status());
    }
}
