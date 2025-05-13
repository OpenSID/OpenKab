<?php

namespace App\Services;

class ConfigApiService extends BaseApiService
{

    public function kabupaten(array $filters = [])
    {
        // Panggil API dan ambil data
        $data = $this->apiRequest('/api/v1/config/kabupaten', $filters);
        if(!$data) {
            return collect([]);
        }
        return collect($data)->map(function ($item) {
            return (object) $item['attributes'];
        });
    }
}