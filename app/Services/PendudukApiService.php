<?php

namespace App\Services;

class PendudukApiService extends BaseApiService
{
    public function penduduk(array $filters = [])
    {
        $data = $this->apiRequest('/api/v1/penduduk', $filters);
        if (! $data) {
            return collect([]);
        }

        return collect($data)->map(fn ($item) => (object) $item['attributes']);
    }
}
