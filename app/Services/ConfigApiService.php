<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ConfigApiService extends BaseApiService
{
    protected int $cacheTtl = 3600; // TTL dalam detik (1 jam)

    public function desa(array $filters = [])
    {
        $cacheKey = $this->buildCacheKey('config_desa', $filters);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters) {
            $data = $this->apiRequest('/api/v1/config/desa', $filters);
            if (! $data) {
                return collect([]);
            }

            return collect($data)->map(fn ($item) => (object) $item['attributes']);
        });
    }

    public function kecamatan(array $filters = [])
    {
        $cacheKey = $this->buildCacheKey('config_kecamatan', $filters);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters) {
            $data = $this->apiRequest('/api/v1/config/kecamatan', $filters);
            if (! $data) {
                return collect([]);
            }

            return collect($data)->map(fn ($item) => (object) $item['attributes']);
        });
    }

    public function kabupaten(array $filters = [])
    {
        $cacheKey = $this->buildCacheKey('config_kabupaten', $filters);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters) {
            $data = $this->apiRequest('/api/v1/config/kabupaten', $filters);
            if (! $data) {
                return collect([]);
            }

            return collect($data)->map(fn ($item) => (object) $item['attributes']);
        });
    }

    public function kabupatenByKode(string $kode_kabupaten)
    {
        $cacheKey = "config_kabupaten_kode_{$kode_kabupaten}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($kode_kabupaten) {
            $data = $this->apiRequest("/api/v1/config/kabupaten-by-kode/{$kode_kabupaten}");

            return $data ?: collect([]);
        });
    }

    public function kecamatanByKode(string $kode_kecamatan)
    {
        $cacheKey = "config_kecamatan_kode_{$kode_kecamatan}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($kode_kecamatan) {
            $data = $this->apiRequest("/api/v1/config/kecamatan-by-kode/{$kode_kecamatan}");

            return $data ?: collect([]);
        });
    }
}
