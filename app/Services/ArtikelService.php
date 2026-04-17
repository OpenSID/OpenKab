<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use stdClass;

class ArtikelService extends BaseApiService
{
    protected int $cacheTtl = 3600; // TTL dalam detik (1 jam)

    private string $cacheSingleArtikel = 'artikel_';

    /**
     * Mendapatkan daftar artikel dengan filter opsional
     *
     * @param  array<int|string, mixed>  $filters
     */
    public function artikel(array $filters = []): Collection
    {
        $cacheKey = $this->buildCacheKey('artikel', $filters);

        // Ambil dari cache dulu
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters): Collection {
            $data = $this->apiRequest('/api/v1/artikel/list', $filters);

            if (empty($data)) {
                return collect([]);
            }

            return collect($data)->map(function (array $item): stdClass {
                // Return 'attributes' but with 'id' populated
                $attributes = $item['attributes'] ?? [];
                $attributes['id'] = $item['id'] ?? null;

                // Fetch detail to enrich with gambar and isi if missing
                if (isset($attributes['id']) && (! isset($attributes['gambar']) || ! isset($attributes['isi']))) {
                    $detail = $this->artikelById((int) $attributes['id']);
                    if ($detail !== null) {
                        $attributes['gambar'] = $detail->gambar ?? null;
                        $attributes['isi'] = $detail->isi ?? null;
                    }
                }

                return (object) $attributes;
            });
        });
    }

    /**
     * Mendapatkan detail artikel berdasarkan ID
     */
    public function artikelById(int $id): ?stdClass
    {
        $cacheKey = $this->cacheSingleArtikel.$id;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id): ?stdClass {
            $data = $this->apiRequest('/api/v1/artikel/tampil', [
                'id' => $id,
            ]);

            if (is_array($data) && count($data) > 0) {
                return (object) $data;
            }

            return null;
        });
    }

    /**
     * Menghapus cache artikel berdasarkan prefix dan filter
     *
     * @param  array<int|string, mixed>  $filters
     */
    public function clearCache(string $prefix = 'artikel', array $filters = []): void
    {
        $cacheKey = $this->buildCacheKey($prefix, $filters);
        Cache::forget($cacheKey);
    }

    /**
     * Menghapus cache artikel tunggal berdasarkan ID
     */
    public function clearCacheSingle(int $id): void
    {
        $cacheKey = $this->cacheSingleArtikel.$id;
        Cache::forget($cacheKey);
    }
}
