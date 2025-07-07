<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SummaryWebsiteService extends BaseApiService
{
    public function getSummaryData(array $filters = [])
    {
        $cacheKey = 'summary_website_data';
        if(!empty($filters)) {
            // Tambahkan filter ke cache key untuk menghindari konflik cache
            $cacheKey .= '_' . md5(json_encode($filters));
        }
        // Cek cache terlebih dahulu
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($filters) {
            return $this->apiRequest('/api/v1/data-website', $filters);
        });
    }
}
