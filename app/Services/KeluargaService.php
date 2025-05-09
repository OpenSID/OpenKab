<?php

namespace App\Services;

class KeluargaService extends BaseApiService
{
    public function store($data)
    {
        $data = $this->apiPost('/api/v1/keluarga/store', $data);
        if(!$data) {
            return collect([]);
        }
        return (object) $data;
    }
}