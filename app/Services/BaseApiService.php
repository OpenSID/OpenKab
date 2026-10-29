<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BaseApiService
{
    protected $settings;

    protected $useDatabaseGabungan;

    protected $header;

    protected $baseUrl;

    protected $kodeKecamatan;

    public function __construct()
    {
        $this->settings = Setting::whereIn('key', ['database_gabungan_api_key', ''])->pluck('value', 'key');
        $this->header = [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/json',
            // Catatan: pada PHP, '.' mengikat lebih kuat daripada '??', sehingga
            // 'Bearer '.$x['k'] ?? '' tetap menghitung akses array tanpa
            // perlindungan. Tanda kurung diperlukan.
            'Authorization' => 'Bearer '.($this->settings['database_gabungan_api_key'] ?? ''),
        ];
        $this->baseUrl = config('app.databaseGabunganUrl');
        $this->kodeKecamatan = str_replace('.', '', config('profil.kecamatan_id'));
    }

    protected function buildCacheKey(string $prefix, array $filters = []): string
    {
        if (empty($filters)) {
            return $prefix;
        }

        return $prefix.'_'.md5(json_encode($filters));
    }

    public function clearCache(string $prefix, array $filters = [])
    {
        $cacheKey = $this->buildCacheKey($prefix, $filters);
        if (cache()->has($cacheKey)) {
            cache()->forget($cacheKey);
        }
    }

    /**
     * General API Call Method.
     */
    protected function apiRequest(string $endpoint, array $params = [])
    {
        try {
            // Buat permintaan API dengan Header dan Parameter, tambahkan throw agar error code 403 dkk masuk exception
            $response = Http::withHeaders($this->header)->get($this->baseUrl.$endpoint, $params)->throw();
            session()->forget('error_api');

            // Return JSON hasil
            return $response->json('data') ?? [];
        } catch (\Exception $e) {
            session()->flash('error_api', 'Gagal mendapatkan data'.$e->getMessage());
            Log::error('Failed get data in '.__FILE__.' function '.__METHOD__.' '.$e->getMessage());
        }

        return [];
    }

    /**
     * Panggilan API database gabungan generik (GET/POST/PUT/PATCH/DELETE).
     *
     * Menangani header otentikasi, pengiriman payload, logging, dan
     * melempar exception saat respons gagal agar dapat ditangani pemanggil.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function apiCall(string $method, string $endpoint, array $payload = []): mixed
    {
        $url = str_starts_with($endpoint, 'http') ? $endpoint : $this->baseUrl.$endpoint;
        $headers = array_merge($this->header, ['Accept' => 'application/json']);

        try {
            $pending = Http::withHeaders($headers);

            $response = match (strtoupper($method)) {
                'GET' => $pending->get($url, $payload),
                'POST' => $pending->post($url, $payload),
                'PUT' => $pending->put($url, $payload),
                'PATCH' => $pending->patch($url, $payload),
                'DELETE' => $pending->delete($url, $payload),
                default => throw new \InvalidArgumentException("Metode HTTP '{$method}' tidak didukung."),
            };

            if ($response->failed()) {
                throw new \RuntimeException('Gagal memanggil API ('.$response->status().'): '.$response->body());
            }

            return $response->json('data') ?? $response->json();
        } catch (\Throwable $e) {
            Log::error('Failed '.strtoupper($method).' request to '.$url.' in '.__FILE__.' '.$e->getMessage());

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function apiGet(string $endpoint, array $params = []): mixed
    {
        return $this->apiCall('GET', $endpoint, $params);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function apiPost(string $endpoint, array $data = []): mixed
    {
        return $this->apiCall('POST', $endpoint, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function apiPut(string $endpoint, array $data = []): mixed
    {
        return $this->apiCall('PUT', $endpoint, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function apiDelete(string $endpoint, array $data = []): mixed
    {
        return $this->apiCall('DELETE', $endpoint, $data);
    }
}
