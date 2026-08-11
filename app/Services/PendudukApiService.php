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

    /**
     * Ringkasan desa (nama, kode, jumlah penduduk, website) dari API gabungan.
     * Sumber base URL instalasi OpenSID (field attributes.website).
     */
    public function desaSummary(array $filters = [])
    {
        $data = $this->apiRequest('/api/v1/wilayah/penduduk', $filters);
        if (! $data) {
            return collect([]);
        }

        return collect($data)->map(fn ($item) => (object) $item['attributes']);
    }
}
