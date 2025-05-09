<?php

namespace App\Services;

class ConfigService extends BaseApiService
{
    public function index(array $filters = [])
    {
        $data = $this->apiRequest('/api/v1/config', $filters);
        if (is_array($data)) {
            $data = collect($data); // Mengubah array menjadi koleksi (Collection)
        }

        if ($data->isNotEmpty()) {
            $firstItem = $data->first();

            // Gabungkan id dan attributes
            $mergedData = array_merge(
                ['id' => $firstItem['id']], // Menambahkan id ke dalam array
                $firstItem['attributes'] // Menambahkan attributes
            );

            // Jika kamu ingin hasil dalam bentuk objek
            return (object) $mergedData;
        }

        return null;
    }
}
