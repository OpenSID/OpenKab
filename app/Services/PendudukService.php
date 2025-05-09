<?php

namespace App\Services;

class PendudukService extends BaseApiService
{
    public function store($data)
    {
        $result = $this->apiPost('/api/v1/penduduk/store', $data);

        if(!$result) {
            return collect([]);
        }

        return (object) $result['data'] ?? [];
    }

    public function updatePendudukByKkLevel($data)
    {
        $result = $this->apiPost('/api/v1/penduduk/update-penduduk-by-kk-level', $data);
        if(!$result) {
            return collect([]);
        }
        return $result;
    }

    public function penduduk(array $filters = [])
    {
        $result = $this->apiRequest('/api/v1/penduduk/kepala-keluarga', $filters);
        if(!$result) {
            return collect([]);
        }
        return collect($result)->map(function ($item) {
            return (object) array_merge(['id' => $item['id']], $item['attributes']);
        });
    }
}