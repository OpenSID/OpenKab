<?php

namespace App\Services;

class GolonganDarahService extends BaseApiService
{
    public function count()
    {
        $data = $this->apiRequestSimple('/api/v1/golongan-darah/count');
        if(!$data) {
            return collect([]);
        }
        return $data;
    }
}