<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SummaryWebsiteService extends BaseApiService
{
    public function getSummaryData()
    {
        $cacheKey = 'summary_website_data';

        // Cek cache terlebih dahulu
        return Cache::remember($cacheKey, now()->addHours(6), function () {
            return $this->apiRequest('/api/v1/data-website');
        });
    }
}
