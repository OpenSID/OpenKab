<?php

namespace App\Services;

class KesehatanApiService extends BaseApiService
{

    public function data(array $filters = [])
    {
        // Panggil API dan ambil data
        $data = $this->apiRequest('/api/v1/data-kesehatan', $filters);
        if(!$data) {
            return collect([]);
        }
        return collect($data)->map(function ($item) {
            return $item['attributes'];
        });
    }
}
