<?php

namespace App\Services;

use App\Models\CMS\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
     * Mendapatkan daftar artikel dari CMS lokal OpenKab
     *
     * @param  array<int|string, mixed>  $filters
     */
    public function getLocalArticles(array $filters = []): Collection
    {
        $search = $filters['search'] ?? $filters['filter[search]'] ?? null;
        $categoryId = $filters['kategori'] ?? $filters['category_id'] ?? $filters['filter[id_kategori]'] ?? null;

        $query = Article::with('category')
            ->where('state', Article::PUBLISH)
            ->where('published_at', '<=', now());

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if (! empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        $articles = $query->orderBy('published_at', 'desc')->get();

        return $articles->map(function (Article $article): stdClass {
            return (object) [
                'id' => $article->id,
                'slug' => $article->slug,
                'judul' => $article->title,
                'isi' => $article->content,
                'gambar' => $article->thumbnail ? Storage::url($article->thumbnail) : null,
                'id_kategori' => $article->category_id,
                'kategori_nama' => $article->category?->name ?? 'Berita OpenKab',
                'tgl_upload' => $article->published_at ? $article->published_at->format('Y-m-d H:i:s') : ($article->created_at ? $article->created_at->format('Y-m-d H:i:s') : ''),
                'enabled' => 1,
                'source' => 'openkab',
                'detail_url' => route('article', ['aSlug' => $article->slug]),
            ];
        });
    }

    /**
     * Mendapatkan daftar artikel dari API upstream OpenSID dengan filter opsional
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
            try {
                $data = $this->apiRequest('/api/v1/artikel/list', $filters);

                if (empty($data)) {
                    return collect([]);
                }

                return collect($data)->map(function (array $item): stdClass {
                    // Return 'attributes' but with 'id' populated
                    $attributes = $item['attributes'] ?? [];
                    $attributes['id'] = $item['id'] ?? null;
                    $attributes['source'] = 'opensid';
                    $attributes['detail_url'] = isset($attributes['id']) ? route('web.artikel.show', $attributes['id']) : '#';

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
            } catch (\Throwable $e) {
                return collect([]);
            }
        });
    }

    /**
     * Mendapatkan gabungan artikel OpenKab lokal dan artikel OpenSID API, terurut berdasarkan tanggal terbaru
     *
     * @param  array<int|string, mixed>  $filters
     */
    public function getCombinedArticles(array $filters = []): Collection
    {
        $cacheKey = $this->buildCacheKey('artikel_combined', $filters);
        $this->registerCacheKey($cacheKey);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters): Collection {
            $localArticles = $this->getLocalArticles($filters);
            $apiArticles = $this->artikel($filters);

            // Filter out disabled articles just in case
            $apiArticles = $apiArticles->filter(function ($item) {
                return isset($item->enabled) && $item->enabled == 1;
            });

            return $localArticles->concat($apiArticles)->sortByDesc(function ($item) {
                return $item->tgl_upload ?? '1970-01-01 00:00:00';
            })->values();
        });
    }

    /**
     * Mendapatkan daftar artikel terbaru gabungan untuk widget beranda
     */
    public function getArtikelTerbaru(int $limit = 6): Collection
    {
        return $this->getCombinedArticles(['page[size]' => $limit])->take($limit)->values();
    }

    /**
     * Mendapatkan detail artikel berdasarkan ID (dari API)
     */
    public function artikelById(int $id): ?stdClass
    {
        $cacheKey = $this->cacheSingleArtikel.$id;

        // ✅ Daftarkan key setiap kali generate
        $this->registerCacheKey($cacheKey);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id): ?stdClass {
            try {
                $data = $this->apiRequest('/api/v1/artikel/tampil', [
                    'id' => $id,
                ]);

                if (is_array($data) && count($data) > 0) {
                    return (object) $data;
                }
            } catch (\Throwable $e) {
                return null;
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

