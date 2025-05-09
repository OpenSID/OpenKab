<?php

namespace App\Services;

class WilayahApiService extends BaseApiService
{
    public function pluckId(array $filters = [])
    {
        $data = $this->apiRequest('/api/v1/wilayah/id', $filters);
        if (! $data) {
            return collect([]);
        }

        return collect($data)->map(function ($item) {
            return (int) $item['id'];
        });
    }

    public function storeDusun($data)
    {
        $data = $this->apiPost('/api/v1/wilayah/store/dusun', $data);
        if (! $data) {
            return collect([]);
        }

        return $data;
    }
}
