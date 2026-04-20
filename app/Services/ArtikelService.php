<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use stdClass;

class ArtikelService extends BaseApiService
{
    protected int $cacheTtl = 3600; // TTL dalam detik (1 jam)

    private string $cacheSingleArtikel = 'artikel_';

    private string $cacheRegistryKey = 'artikel_cache_registry';

    /**
     * Daftarkan cache key ke registry setiap kali generate
     */
    private function registerCacheKey(string $key): void
    {
        $keys = Cache::get($this->cacheRegistryKey, []);

        // Pastikan selalu array meskipun cache corrupt
        if (! is_array($keys)) {
            $keys = [];
        }

        $keys[$key] = time();

        // Gunakan TTL 7 hari, tidak forever untuk mencegah memory bloat
        Cache::put($this->cacheRegistryKey, $keys, now()->addDays(7));
    }

    /**
     * Mendapatkan daftar artikel dengan filter opsional
     *
     * @param  array<int|string, mixed>  $filters
     */
    public function artikel(array $filters = []): Collection
    {
        $cacheKey = $this->buildCacheKey('artikel', $filters);

        // ✅ Daftarkan key setiap kali generate
        $this->registerCacheKey($cacheKey);

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

        // ✅ Daftarkan key setiap kali generate
        $this->registerCacheKey($cacheKey);

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
     * Menghapus cache artikel tunggal berdasarkan ID
     */
    public function clearCacheSingle(int $id): void
    {
        $cacheKey = $this->cacheSingleArtikel.$id;
        Cache::forget($cacheKey);
    }

    /**
     * ✅ HAPUS SEMUA CACHE ARTIKEL 100% BERFUNGSI DI SEMUA DRIVER!
     * Termasuk semua cache list dengan hash MD5 apapun
     */
    public function clearAllCache(): void
    {
        // Ambil semua key yang pernah terdaftar
        $keys = Cache::get($this->cacheRegistryKey, []);

        // Validasi tipe data, hindari fatal error jika cache corrupt
        if (! is_array($keys)) {
            Cache::forget($this->cacheRegistryKey);

            return;
        }

        $cacheKeys = array_keys($keys);

        if (empty($cacheKeys)) {
            Cache::forget($this->cacheRegistryKey);

            return;
        }

        // Laravel 10+ mendukung deleteMultiple untuk batch operation
        try {
            Cache::deleteMultiple($cacheKeys);
        } catch (\BadMethodCallException $e) {
            // Fallback untuk driver yang tidak mendukung deleteMultiple
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
        }

        // Reset registry
        Cache::forget($this->cacheRegistryKey);
    }
}
