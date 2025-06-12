<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class KategoriService extends BaseApiService
{

    public function kategori(int $id)
    {
        $cacheKey = "kategori_$id";

        // Ambil dari cache dulu
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($id) {
            $data = $this->apiRequest('/api/v1/kategori', [
                'filter[id]' => $id,
            ]);

            if (is_array($data) && count($data) > 0) {
                    return $data[0];
                }

            return null;
        });
    }
}
