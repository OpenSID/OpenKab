<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ArtikelService extends BaseApiService
{
    protected int $cacheTtl = 3600; // TTL dalam detik (1 jam)

    public function artikel(array $filters = [])
    {
        $cacheKey = $this->buildCacheKey('artikel', $filters);

        // Ambil dari cache dulu
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters) {
            $data = $this->apiRequest('/api/v1/artikel', $filters);
            if (! $data) {
                return collect([]);
            }

            return collect($data)->map(fn ($item) => (object) $item['attributes']);
        });
    }

    public function artikelById(int $id)
    {
        $cacheKey = "artikel_$id";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            $data = $this->apiRequest('/api/v1/artikel/tampil', [
                'id' => $id,
            ]);

            if (is_array($data) && isset($data['data'])) {
                return (object) $data['data'];
            }

            return null;
        });
    }

    public function clearCache(string $prefix = 'artikel', array $filters = [])
    {
        $cacheKey = $this->buildCacheKey($prefix, $filters);
        Cache::forget($cacheKey);
    }
}
