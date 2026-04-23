<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LaporanDesaAktifService extends BaseApiService
{
    protected string $prefix = 'laporan_desa_aktif';

    protected int $cacheTtl = 3600;

    public function getDesaAktif(array $filters = []): array
    {
        $kodeKabupaten = $filters['kode_kabupaten'] ?? session('kabupaten.kode_kabupaten') ?? '';
        $kodeKecamatan = $filters['kode_kecamatan'] ?? null;
        $kodeDesa = $filters['kode_desa'] ?? null;
        $page = $filters['page'] ?? 1;
        $size = $filters['size'] ?? 20;
        $search = $filters['search'] ?? null;
        $sort = $filters['sort'] ?? '-nama_desa';

        $cacheKey = "{$this->prefix}_".md5(json_encode([
            'kode_kabupaten' => $kodeKabupaten,
            'kode_kecamatan' => $kodeKecamatan,
            'kode_desa' => $kodeDesa,
            'page' => $page,
            'size' => $size,
            'search' => $search,
            'sort' => $sort,
        ]));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($kodeKabupaten, $kodeKecamatan, $kodeDesa, $page, $size, $search, $sort) {
            try {
                $params = [
                    'kode_kabupaten' => $kodeKabupaten,
                    'page[size]' => $size,
                    'page[number]' => $page,
                ];

                if ($kodeKecamatan) {
                    $params['filter[kode_kecamatan]'] = $kodeKecamatan;
                }

                if ($kodeDesa) {
                    $params['filter[kode_desa]'] = $kodeDesa;
                }

                if ($search) {
                    $params['filter[search]'] = $search;
                }

                if ($sort) {
                    $params['sort'] = $sort;
                }

                $response = Http::withHeaders($this->header)
                    ->timeout(30)
                    ->get($this->baseUrl.'/api/v1/desa-aktif', $params);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('API desa-aktif returned non-200 status', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'data' => [],
                    'meta' => [
                        'pagination' => [
                            'total' => 0,
                            'page' => $page,
                            'size' => $size,
                        ],
                    ],
                ];
            } catch (\Exception $e) {
                Log::error('Failed to fetch desa-aktif data', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return [
                    'data' => [],
                    'meta' => [
                        'pagination' => [
                            'total' => 0,
                            'page' => $page,
                            'size' => $size,
                        ],
                    ],
                ];
            }
        });
    }

    public function getKecamatanList(string $kodeKabupaten = ''): array
    {
        if (empty($kodeKabupaten)) {
            $kodeKabupaten = session('kabupaten.kode_kabupaten') ?? '';
        }

        $cacheKey = "{$this->prefix}_kecamatan_{$kodeKabupaten}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($kodeKabupaten) {
            try {
                $response = Http::withHeaders($this->header)
                    ->timeout(30)
                    ->get($this->baseUrl.'/api/v1/statistik-web/get-list-kecamatan', [
                        'kode_kabupaten' => $kodeKabupaten,
                    ]);

                if ($response->successful()) {
                    return $response->json('data') ?? [];
                }

                return [];
            } catch (\Exception $e) {
                Log::error('Failed to fetch kecamatan list', [
                    'error' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }

    public function getDesaList(string $kodeKecamatan = '', string $kodeKabupaten = ''): array
    {
        if (empty($kodeKabupaten)) {
            $kodeKabupaten = session('kabupaten.kode_kabupaten') ?? '';
        }

        $cacheKey = "{$this->prefix}_desa_{$kodeKabupaten}_{$kodeKecamatan}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($kodeKecamatan, $kodeKabupaten) {
            try {
                $params = [
                    'kode_kabupaten' => $kodeKabupaten,
                ];

                if ($kodeKecamatan) {
                    $params['filter[kode_kecamatan]'] = $kodeKecamatan;
                }

                $response = Http::withHeaders($this->header)
                    ->timeout(30)
                    ->get($this->baseUrl.'/api/v1/statistik-web/get-list-desa', $params);

                if ($response->successful()) {
                    return $response->json('data') ?? [];
                }

                return [];
            } catch (\Exception $e) {
                Log::error('Failed to fetch desa list', [
                    'error' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }

    public function clearAllCache(): void
    {
        Cache::flush();
    }

    public function getApiKey(): string
    {
        return $this->settings['database_gabungan_api_key'] ?? '';
    }
}
