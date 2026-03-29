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
            $data = $this->apiRequest('/api/v1/artikel/list', $filters);
            if (!$data) {
                return collect([]);
            }
            return collect($data)->map(function ($item) {
                // Return 'attributes' but with 'id' populated 
                $attributes = $item['attributes'] ?? [];
                $attributes['id'] = $item['id'] ?? null;

                // Fetch detail to enrich with gambar and isi if missing
                if (isset($attributes['id']) && (!isset($attributes['gambar']) || !isset($attributes['isi']))) {
                    $detail = $this->artikelById($attributes['id']);
                    if ($detail) {
                        $attributes['gambar'] = $detail->gambar ?? null;
                        $attributes['isi'] = $detail->isi ?? null;
                    }
                }

                return (object) $attributes;
            });
        });
    }

    public function artikelById(int $id)
    {
        $cacheKey = "artikel_$id";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            $data = $this->apiRequest('/api/v1/artikel/tampil', [
                'id' => $id,
            ]);

            if (is_array($data) && count($data) > 0) {
                return (object) $data;
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
