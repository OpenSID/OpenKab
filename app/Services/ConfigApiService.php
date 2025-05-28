<?php

namespace App\Services;

class ConfigApiService extends BaseApiService
{
    public function desa(array $filters = [])
    {
        // Panggil API dan ambil data
        $data = $this->apiRequest('/api/v1/config/desa', $filters);
        if (! $data) {
            return collect([]);
        }

        return collect($data)->map(function ($item) {
            return (object) $item['attributes'];
        });
    }

    public function desaConfig(array $filters = [])
    {
        // Panggil API dan ambil data
        $data = $this->apiRequest('/api/v1/config/desa', $filters);
        if (! $data) {
            return collect([]);
        }

        return collect($data)->map(function ($item) {
            return (object) [
                'id' => $item['id'],
                ...$item['attributes']
            ];
        });
    }

    public function kecamatan(array $filters = [])
    {
        // Panggil API dan ambil data
        $data = $this->apiRequest('/api/v1/config/kecamatan', $filters);
        if (! $data) {
            return collect([]);
        }

        return collect($data)->map(function ($item) {
            return (object) $item['attributes'];
        });
    }

    public function kabupaten(array $filters = [])
    {
        // Panggil API dan ambil data
        $data = $this->apiRequest('/api/v1/config/kabupaten', $filters);
        if (! $data) {
            return collect([]);
        }

        return collect($data)->map(function ($item) {
            return (object) $item['attributes'];
        });
    }
}
