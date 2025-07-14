<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class BantuanService extends BaseApiService
{
    public function bantuan(array $filters = [])
    {
        $cacheKey = $this->buildCacheKey('bantuan', $filters);

        // Ambil dari cache dulu
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($filters) {
            $data = $this->apiRequest('/api/v1/bantuan-kabupaten', $filters);
            if (! $data) {
                return collect([]);
            }

            return collect($data)->map(fn ($item) => (object) $item['attributes']);
        });
    }
}
