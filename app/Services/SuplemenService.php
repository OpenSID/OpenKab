<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SuplemenService extends BaseApiService
{
    protected int $cacheTtl = 3600;

    public function suplemen(array $filters = [])
    {
        $cacheKey = $this->buildCacheKey('suplemen', $filters);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters) {
            $data = $this->apiRequest('/api/v1/suplemen', $filters);
            if (! $data) {
                return collect([]);
            }

            return collect($data)->map(fn ($item) => (object) ($item['attributes'] ?? $item));
        });
    }

    public function suplemenById($id)
    {
        $data = $this->apiRequest('/api/v1/suplemen', ['filter[id]' => $id]);
        if (! $data) {
            return null;
        }

        return (object) ($data[0]['attributes'] ?? $data[0] ?? null);
    }

    public function terdata($sasaran, $suplemenId, array $filters = [])
    {
        $params = array_merge(['page[size]' => 9999], $filters);
        $cacheKey = $this->buildCacheKey("suplemen_terdata_{$sasaran}_{$suplemenId}", $params);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($sasaran, $suplemenId, $params) {
            $data = $this->apiRequest("/api/v1/suplemen/terdata/{$sasaran}/{$suplemenId}", $params);
            if (! $data) {
                return collect([]);
            }

            return collect($data)->map(fn ($item) => (object) ($item['attributes'] ?? $item));
        });
    }

    public function configDesa(array $filters = [])
    {
        $cacheKey = $this->buildCacheKey('suplemen_config_desa', $filters);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters) {
            $data = $this->apiRequest('/api/v1/config/desa', $filters);
            if (! $data) {
                return collect([]);
            }

            return collect($data)->map(fn ($item) => (object) ($item['attributes'] ?? $item));
        });
    }

    public function clearSuplemenCache($id = null)
    {
        if ($id) {
            $this->clearCache('suplemen', ['filter[id]' => $id]);
        } else {
            cache()->forget('suplemen');
        }
    }
}
