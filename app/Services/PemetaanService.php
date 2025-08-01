<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class PemetaanService extends BaseApiService
{
    protected $baseUrl;

    protected $setting;

    public function __construct()
    {
        parent::__construct();
        $this->setting = Setting::where('key', 'database_gabungan_api_key')->first();
    }

    public function getAllPoint(array $query = [])
    {
        return $this->apiRequest('/api/v1/point');
    }

    public function pointLock(array $data, string $id): ?array
    {
        $url = "{$this->baseUrl}/api/v1/point/lock/{$id}";

        $response = Http::withToken($this->setting->value ?? '')
            ->acceptJson()
            ->put($url, $data);

        if ($response->successful()) {
            return $response->json('data') ?? null;
        }

        throw new \Exception('Gagal mengupdate data point: '.$response->status().' - '.$response->body());
    }

    public function pointUpdate(array $data, string $id): ?array
    {
        $url = "{$this->baseUrl}/api/v1/point/update/{$id}";

        $response = Http::withToken($this->setting->value ?? '')
            ->acceptJson()
            ->put($url, $data);

        if ($response->successful()) {
            return $response->json('data') ?? null;
        }

        throw new \Exception('Gagal mengupdate data point: '.$response->status().' - '.$response->body());
    }

    public function pointStore(array $data): ?array
    {
        $url = "{$this->baseUrl}/api/v1/point/store";

        $response = Http::withToken($this->setting->value ?? '')
            ->acceptJson()
            ->post($url, $data);

        if ($response->successful()) {
            return $response->json('data') ?? null;
        }

        throw new \Exception('Gagal store data point: '.$response->status().' - '.$response->body());
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
